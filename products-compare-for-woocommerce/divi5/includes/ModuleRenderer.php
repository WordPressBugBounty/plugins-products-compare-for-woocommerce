<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BRCMP_Divi5_Module_Renderer {
    protected $module_type;
    protected $defaults;

    public function __construct( $config ) {
        $this->module_type = $config['type'];
        $this->defaults    = $config['defaults'];
    }

    public function render_module( $attrs ) {
        $atts = $this->attrs_to_atts( $attrs );

        if( 'widget' === $this->module_type ) {
            return $this->render_widget( $atts );
        }

        if( 'button' === $this->module_type ) {
            return $this->render_button( $atts );
        }

        return do_shortcode( '[br_compare_table]' );
    }

    public function attrs_to_atts( $attrs ) {
        $atts = $this->defaults;

        foreach( array_keys( $this->defaults ) as $key ) {
            $value = $this->get_attr_value( $attrs, $key );
            if( null !== $value ) {
                $atts[$key] = $value;
            }
        }

        return $atts;
    }

    protected function render_widget( $atts ) {
        $widget_atts = array(
            'title'        => sanitize_text_field( $atts['title'] ),
            'fast_compare' => $this->is_on( $atts['fast_compare'] ),
            'type'         => BeRocket_Compare_Products_Widget::sanitize_type( $atts['type'] ),
            'toolbar'      => $this->is_on( $atts['toolbar'] ),
        );

        ob_start();
        the_widget( 'berocket_compare_products_widget', $widget_atts );
        return ob_get_clean();
    }

    protected function render_button( $atts ) {
        $product = $atts['content.advanced.product'];
        if( is_array( $product ) ) {
            $product = '';
        }
        $product = sanitize_text_field( (string) $product );

        if( 'latest' === $product ) {
            $product = $this->get_latest_product_id();
        } elseif( in_array( $product, array( '', 'current', 'dynamic' ), true ) ) {
            $product = '';
        } else {
            $product = absint( $product );
        }

        $button_atts = array(
            'product'       => $product,
            'fast_compare'  => $this->is_on( $atts['fast_compare'] ),
            'add_compare'   => sanitize_text_field( $atts['add_compare'] ),
            'added_compare' => sanitize_text_field( $atts['added_compare'] ),
        );

        ob_start();
        do_action( 'br_compare_button_options', $button_atts );
        return ob_get_clean();
    }

    protected function get_latest_product_id() {
        $products = get_posts(
            array(
                'post_type'        => 'product',
                'post_status'      => 'publish',
                'posts_per_page'   => 20,
                'orderby'          => 'ID',
                'order'            => 'DESC',
                'fields'           => 'ids',
                'suppress_filters' => false,
            )
        );

        foreach( $products as $product_id ) {
            if( BeRocket_Compare_Products::is_public_compare_product( $product_id ) ) {
                return absint( $product_id );
            }
        }

        return 0;
    }

    protected function get_attr_value( $attrs, $path ) {
        $value = $attrs;
        foreach( explode( '.', $path ) as $part ) {
            if( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
                return null;
            }
            $value = $value[$part];
        }

        if( isset( $value['innerContent']['desktop']['value'] ) ) {
            return $this->normalize_attr_value( $value['innerContent']['desktop']['value'] );
        }
        if( isset( $value['desktop']['value'] ) ) {
            return $this->normalize_attr_value( $value['desktop']['value'] );
        }
        if( ! is_array( $value ) ) {
            return $value;
        }

        return null;
    }

    protected function normalize_attr_value( $value ) {
        if( is_array( $value ) ) {
            foreach( array( 'value', 'number', 'amount', 'id' ) as $value_key ) {
                if( isset( $value[$value_key] ) && '' !== $value[$value_key] && ! is_array( $value[$value_key] ) ) {
                    return $value[$value_key];
                }
            }
            return '';
        }

        return $value;
    }

    protected function is_on( $value ) {
        return true === $value || 1 === $value || '1' === $value || 'on' === $value;
    }
}
