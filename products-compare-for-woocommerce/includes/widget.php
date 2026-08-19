<?php
/**
 * Compare Products widget
 */
class BeRocket_Compare_Products_Widget extends WP_Widget 
{
    public static $defaults = array(
        'fast_compare'  => '',
        'title'         => '',
        'type'          => 'image',
        'toolbar'       => '',
    );
	public function __construct() {
        parent::__construct("berocket_compare_products_widget", "WooCommerce Products Compare",
            array("description" => "WooCommerce Products Compare List"));
    }
    /**
     * WordPress widget for display Compare Products
     */
    public function widget($args, $instance)
    {
        $BeRocket_Compare_Products = BeRocket_Compare_Products::getInstance();
        $instance = array_merge(self::$defaults, $instance);
        $instance['title'] = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance );
        $settings = array_merge( BeRocket_Compare_Products_Widget::$defaults, $instance );
        set_query_var( 'title', apply_filters( 'compare_products_widget_title', $settings['title'] ) );
        set_query_var( 'type', self::sanitize_type( apply_filters( 'compare_products_widget_type', $settings['type'] ) ) );
        set_query_var( 'toolbar', apply_filters( 'compare_products_widget_toolbar', $settings['toolbar'] ) );
        set_query_var( 'fast_compare', apply_filters( 'compare_products_widget_fast_compare', $settings['fast_compare'] ) );
        echo $args['before_widget'];
        $BeRocket_Compare_Products->br_get_template_part('selected_products');
        echo $args['after_widget'];
	}
    /**
     * Update widget settings
     */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( wp_unslash( $new_instance['title'] ) ) : '';
        $instance['type'] = self::sanitize_type( isset( $new_instance['type'] ) ? $new_instance['type'] : '' );
        $instance['toolbar'] = ! empty($new_instance['toolbar']);
        $instance['fast_compare'] = ! empty($new_instance['fast_compare']);
		return $instance;
	}

    public static function sanitize_type( $type ) {
        if ( ! is_scalar( $type ) ) {
            return 'image';
        }
        $type = sanitize_key( (string) $type );
        return in_array( $type, array( 'image', 'text' ), true ) ? $type : 'image';
    }
    /**
     * Widget settings form
     */
	public function form($instance)
	{
        if( ! is_array($instance) ) {
            $instance = array();
        }
        $instance = array_merge(self::$defaults, $instance);
		$title = strip_tags($instance['title']);
        $instance['type'] = self::sanitize_type( $instance['type'] );
		?>
        <p>
            <label>
                <input id="<?php echo esc_attr( $this->get_field_id('fast_compare') ); ?>" name="<?php echo esc_attr( $this->get_field_name('fast_compare') ); ?>" value="1" type="checkbox"<?php checked( ! empty( $instance['fast_compare'] ) ); ?>>
                <?php _e( 'Fast compare to load compare table via AJAX', 'products-compare-for-woocommerce' ) ?>
            </label>
        </p>
		<p>
            <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <select id="<?php echo esc_attr( $this->get_field_id('type') ); ?>" name="<?php echo esc_attr( $this->get_field_name('type') ); ?>">
                <option value="image" <?php selected( $instance['type'], 'image' ); ?>><?php esc_html_e( 'Image', 'products-compare-for-woocommerce' ) ?></option>
                <option value="text" <?php selected( $instance['type'], 'text' ); ?>><?php esc_html_e( 'Text', 'products-compare-for-woocommerce' ) ?></option>
            </select>
        </p>
        <p>
            <input id="<?php echo esc_attr( $this->get_field_id('toolbar') ); ?>" name="<?php echo esc_attr( $this->get_field_name('toolbar') ); ?>" value="1" type="checkbox"<?php checked( ! empty( $instance['toolbar'] ) ); ?>>
            <label><?php esc_html_e( 'Is ToolBar', 'products-compare-for-woocommerce' ) ?></label>
        </p>
		<?php
	}
}
?>
