<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Explicit block placement of the existing visitor-specific comparison table. */
class BeRocket_Compare_Products_Block {
    const NAME = 'berocket/compare-table';
    private $rendering = false;

    public function __construct() {
        add_action( 'init', array( $this, 'register' ), 20 );
        add_action( 'template_redirect', array( $this, 'protect_page_cache' ), 0 );
        add_filter( 'template_include', array( $this, 'protect_template_cache' ), 99 );
    }

    public function register() {
        if ( ! function_exists( 'register_block_type' ) || ! shortcode_exists( 'br_compare_table' ) ) { return; }
        $version = get_bloginfo( 'version' );
        $api = version_compare( $version, '6.3', '>=' ) ? 3 : ( version_compare( $version, '5.6', '>=' ) ? 2 : 1 );
        $editor = wp_script_is( 'wp-block-editor', 'registered' ) ? 'wp-block-editor' : 'wp-editor';
        wp_register_script( 'br-compare-table-block-editor', plugins_url( '../js/compare-table-block.js', __FILE__ ),
            array( 'wp-blocks', 'wp-element', 'wp-components', $editor, 'wp-i18n' ), BeRocket_Compare_Products_version, true );
        wp_register_style( 'br-compare-table-block-editor', plugins_url( '../css/compare-table-block-editor.css', __FILE__ ), array(), BeRocket_Compare_Products_version );
        wp_localize_script( 'br-compare-table-block-editor', 'brCompareTableBlock', array( 'apiVersion' => $api ) );
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'br-compare-table-block-editor', 'products-compare-for-woocommerce', dirname( __DIR__ ) . '/languages' );
        }
        register_block_type( self::NAME, array(
            'api_version' => $api, 'editor_script' => 'br-compare-table-block-editor', 'editor_style' => 'br-compare-table-block-editor',
            'supports' => array( 'html' => false, 'customClassName' => false ), 'render_callback' => array( $this, 'render' ),
        ) );
    }

    /** Include synced/registered patterns without following circular references. */
    public static function contains_table( $content, $seen = array() ) {
        if ( ! is_string( $content ) || ! function_exists( 'parse_blocks' ) ) { return false; }
        if ( has_block( self::NAME, $content ) ) { return true; }
        return self::contains_blocks( parse_blocks( $content ), $seen );
    }

    private static function contains_blocks( $blocks, $seen ) {
        foreach ( $blocks as $block ) {
            if ( self::NAME === $block['blockName'] ) { return true; }
            if ( 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) && is_numeric( $block['attrs']['ref'] ) ) {
                $ref = absint( $block['attrs']['ref'] );
                if ( ! isset( $seen[$ref] ) ) {
                    $seen[$ref] = true;
                    $post = get_post( $ref );
                    if ( $post && self::contains_table( $post->post_content, $seen ) ) { return true; }
                }
            }
            if ( 'core/pattern' === $block['blockName'] && ! empty( $block['attrs']['slug'] ) && is_string( $block['attrs']['slug'] ) && class_exists( 'WP_Block_Patterns_Registry' ) ) {
                $slug = $block['attrs']['slug'];
                if ( ! isset( $seen[$slug] ) ) {
                    $seen[$slug] = true;
                    $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( $slug );
                    if ( $pattern && self::contains_table( $pattern['content'], $seen ) ) { return true; }
                }
            }
            if ( 'core/template-part' === $block['blockName'] && ! empty( $block['attrs']['slug'] ) && is_string( $block['attrs']['slug'] ) && function_exists( 'get_block_template' ) ) {
                $theme = isset( $block['attrs']['theme'] ) && is_string( $block['attrs']['theme'] ) ? $block['attrs']['theme'] : get_stylesheet();
                $id = $theme . '//' . $block['attrs']['slug'];
                if ( ! isset( $seen[$id] ) ) {
                    $seen[$id] = true;
                    $part = get_block_template( $id, 'wp_template_part' );
                    if ( $part && self::contains_table( $part->content, $seen ) ) { return true; }
                }
            }
            if ( self::contains_blocks( $block['innerBlocks'], $seen ) ) { return true; }
        }
        return false;
    }

    private function no_cache() {
        if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
        if ( ! headers_sent() ) {
            nocache_headers();
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private' );
        }
    }

    public function protect_page_cache() {
        $post = get_queried_object();
        // Empty lists need the same protection. Do not disable unrelated shop pages.
        if ( is_singular() && $post instanceof WP_Post && self::contains_table( $post->post_content ) ) { $this->no_cache(); }
    }

    public function protect_template_cache( $template ) {
        global $_wp_current_template_content;
        if ( self::contains_table( $_wp_current_template_content ) ) { $this->no_cache(); }
        return $template;
    }

    public function render( $attributes = array() ) {
        // The editor must never preview the editor user's real comparison cookie.
        if ( $this->rendering || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed()
            || ! class_exists( 'BeRocket_Compare_Products' ) || ! shortcode_exists( 'br_compare_table' ) ) { return ''; }
        $plugin = BeRocket_Compare_Products::getInstance();
        if ( ! $plugin->init_validation() ) { return ''; }
        $this->no_cache();
        $wrapper = function_exists( 'get_block_wrapper_attributes' ) ? get_block_wrapper_attributes( array( 'class' => 'br-compare-table-block' ) )
            : 'class="wp-block-berocket-compare-table br-compare-table-block"';
        $empty = '<p class="br-compare-table-empty">' . esc_html__( 'No products selected for comparison. Add products using the Compare buttons in the shop.', 'products-compare-for-woocommerce' ) . '</p>';
        // The native renderer uses global postdata and template query variables.
        $keys = array( 'post', 'product', 'woocommerce_loop', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' );
        $saved = array();
        foreach ( $keys as $key ) { $saved[$key] = array( array_key_exists( $key, $GLOBALS ), isset( $GLOBALS[$key] ) ? $GLOBALS[$key] : null ); }
        global $wp_query;
        $query_keys = array( 'berocket_element_i', 'br_compare_apply_filters', 'is_full_screen' );
        $query = array();
        foreach ( $query_keys as $key ) { $query[$key] = array( array_key_exists( $key, $wp_query->query_vars ), get_query_var( $key ) ); }
        $level = ob_get_level();
        $this->rendering = true;
        try {
            // A previous popup/shortcode must not make this table inherit its ID/state.
            foreach ( $query_keys as $key ) { unset( $wp_query->query_vars[$key] ); }
            global $wp;
            $url = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( $wp->request );
            $html = $plugin->render_compare_table( array( 'addthis' => '0' ), $url );
            return '<div ' . $wrapper . '>' . ( false === strpos( $html, 'br_new_compare_block_wrap' ) ? $empty : $html ) . '</div>';
        } finally {
            while ( ob_get_level() > $level ) { ob_end_clean(); }
            foreach ( $saved as $key => $value ) { if ( $value[0] ) { $GLOBALS[$key] = $value[1]; } else { unset( $GLOBALS[$key] ); } }
            foreach ( $query as $key => $value ) { if ( $value[0] ) { $wp_query->query_vars[$key] = $value[1]; } else { unset( $wp_query->query_vars[$key] ); } }
            $this->rendering = false;
        }
    }
}
new BeRocket_Compare_Products_Block();
