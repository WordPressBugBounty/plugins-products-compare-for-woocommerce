<div class="berocket_compare_widget_start<?php if ( ! empty($toolbar) ) echo ' berocket_compare_widget_toolbar_block'; ?>">
<?php
$BeRocket_Compare_Products = BeRocket_Compare_Products::getInstance();
$options_global = $BeRocket_Compare_Products->get_option();
$products = $BeRocket_Compare_Products->get_all_compare_products();
$options = $options_global['general_settings'];
$text = $options_global['text_settings'];
$type = BeRocket_Compare_Products_Widget::sanitize_type( isset( $type ) ? $type : '' );
if ( ! empty($toolbar) ) {
    echo '<a class="berocket_show_compare_toolbar" href="#toolbar"';
    if ( ! isset( $products ) || ! is_array( $products ) || count( $products ) == 0 ) {
        echo ' style="display:none;"';
    }
    echo '>'.esc_html( empty($text['toolbar']) ? __( 'Products For Compare', 'products-compare-for-woocommerce' ) : $text['toolbar'] ).'<i class="fa fa-angle-up"></i></a>
    <div class="berocket_compare_widget_toolbar">
    <a class="berocket_hide_compare_toolbar" href="#close-toolbar"><i class="fa fa-angle-down"></i></a>';
}
?>
<?php if ( ! empty($title) ) {
    echo '<h3>'.wp_kses_post( $title ).'</h3>';
} ?>
<div class="berocket_compare_widget berocket_compare_widget_<?php echo esc_attr( $type ); ?>" data-type="<?php echo esc_attr( $type ); ?>" data-fast_compare="<?php echo esc_attr( empty( $fast_compare ) ? '' : '1' ); ?>">
    <ul>
    <?php
    if ( isset( $products ) && is_array( $products ) && count( $products ) > 0 ) {
        foreach ( $products as $product ) {
            $term = array();
            $current_language= apply_filters( 'wpml_current_language', NULL );
            $default_language= apply_filters( 'wpml_default_language', NULL );
            $product = apply_filters( 'wpml_object_id', $product, 'product', true, $current_language );
            $default_product = apply_filters( 'wpml_object_id', $product, 'product', true, $default_language );
            if ( ! $BeRocket_Compare_Products->is_public_compare_product( $product ) ) {
                continue;
            }
            $post_get = wc_get_product($product);
            if( empty($post_get) ) continue;
            $title = $post_get->get_title();
            $image = $post_get->get_image();
            $link = $post_get->get_permalink();
            echo '<li class="br_widget_compare_product_'.absint( $default_product ).'">';
            echo '<a href="#remove" class="br_remove_compare_product" data-id="'.absint( $default_product ).'"><i class="fa fa-times"></i></a>';
            echo '<a href="'.esc_url( $link ).'">';
            if ( $type != 'text' ) {
                echo wp_kses_post( $image );
            }
            echo '<span>'.esc_html( $title ).'</span>';
            echo '</a></li>';
        }
    }
    ?>
    </ul>
    <?php 
        $page_compare = $options['compare_page'];
    if ( isset( $products ) && is_array( $products ) && count( $products ) > 0 ) { ?>
    <a class="berocket_open_compare<?php if( ! empty($fast_compare) ) echo ' berocket_open_smart_compare'; ?>" href="<?php echo esc_url( get_page_link( absint( $page_compare ) ) ); ?>"><?php echo esc_html( empty($text['compare']) ? __( 'Compare', 'products-compare-for-woocommerce' ) : $text['compare'] ); ?></a>
    <?php } ?>
</div>
</div>
<?php
if ( ! empty($toolbar) ) {
    echo '</div>';
}
?>
