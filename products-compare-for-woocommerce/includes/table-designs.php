<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Visual presets only: product selection and comparison still use the native renderer. */
class BeRocket_Compare_Table_Designs {
    public static function choices() {
        return array(
            'clean' => __( 'Clean', 'products-compare-for-woocommerce' ),
            'striped' => __( 'Soft Stripes', 'products-compare-for-woocommerce' ),
            'outline' => __( 'Outline', 'products-compare-for-woocommerce' ),
            'editorial' => __( 'Editorial', 'products-compare-for-woocommerce' ),
            'dark' => __( 'Dark', 'products-compare-for-woocommerce' ),
            'ocean' => __( 'Ocean', 'products-compare-for-woocommerce' ),
            'forest' => __( 'Forest', 'products-compare-for-woocommerce' ),
            'compact' => __( 'Compact', 'products-compare-for-woocommerce' ),
        );
    }

    public static function normalize( $value ) {
        return is_string( $value ) && array_key_exists( $value, self::choices() ) ? $value : 'manual';
    }

    public static function classes( $value ) {
        $value = self::normalize( $value );
        return 'manual' === $value ? '' : ' br_compare_design br_compare_design--' . $value;
    }

    public static function columns( $count ) {
        return '<colgroup><col style="width:var(--br-cmp-label-width,200px)">'
            . str_repeat( '<col style="width:var(--br-cmp-column-width,200px)">', max( 0, min( 100, (int) $count ) ) ) . '</colgroup>';
    }

    public static function preserve_manual_settings( $settings, $submitted ) {
        if ( ! is_array( $submitted ) || ! array_key_exists( 'table_design', $submitted )
            || ! isset( $settings['style_settings'] ) || ! is_array( $settings['style_settings'] ) ) { return $settings; }
        $saved = get_option( 'br-compare-products-options', array() );
        if ( ! is_array( $saved ) || empty( $saved['style_settings'] ) || ! is_array( $saved['style_settings'] ) ) { return $settings; }
        // The legacy form omits some fields (notably fullscreen). The framework
        // fills omitted keys with empty strings; changing a preset must not erase them.
        foreach ( $saved['style_settings'] as $section => $values ) {
            if ( ! is_array( $values ) ) { continue; }
            $posted = isset( $submitted['style_settings'][$section] ) && is_array( $submitted['style_settings'][$section] ) ? $submitted['style_settings'][$section] : array();
            foreach ( $values as $key => $value ) {
                if ( ! array_key_exists( $key, $posted ) ) { $settings['style_settings'][$section][$key] = $value; }
            }
        }
        return $settings;
    }

    public static function enqueue( $admin = false ) {
        wp_enqueue_style( 'br-compare-table-designs', plugins_url( '../css/table-designs.css', __FILE__ ), array( 'berocket_compare_products_style' ), BeRocket_Compare_Products_version );
        if ( $admin ) {
            wp_enqueue_script( 'br-compare-table-designs-admin', plugins_url( '../js/table-designs-admin.js', __FILE__ ), array( 'jquery' ), BeRocket_Compare_Products_version, true );
        }
    }

    public static function field( $options ) {
        $design = self::normalize( isset( $options['table_design'] ) ? $options['table_design'] : 'manual' );
        ob_start(); ?>
        <th scope="row"><label for="br-compare-table-design"><?php esc_html_e( 'Table design', 'products-compare-for-woocommerce' ); ?></label></th>
        <td><div class="br_field br-compare-design-field">
            <select id="br-compare-table-design" name="br-compare-products-options[table_design]" aria-describedby="br-compare-design-help">
                <?php foreach ( self::choices() as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $design, $value ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
                <optgroup label="<?php esc_attr_e( 'Existing appearance', 'products-compare-for-woocommerce' ); ?>">
                    <option value="manual" <?php selected( $design, 'manual' ); ?>><?php esc_html_e( 'Manual/Default', 'products-compare-for-woocommerce' ); ?></option>
                </optgroup>
            </select>
            <p class="description" id="br-compare-design-help"><?php esc_html_e( 'Choose Manual/Default to use your custom colours and borders. Column and image sizes apply to all designs.', 'products-compare-for-woocommerce' ); ?></p>
            <div id="br-compare-design-preview" class="br-compare-design-preview" <?php echo 'manual' === $design ? 'hidden' : ''; ?>>
                <p class="description"><?php esc_html_e( 'Preview — example products', 'products-compare-for-woocommerce' ); ?></p>
                <?php self::preview( $design ); ?>
            </div>
        </div></td>
        <?php return ob_get_clean();
    }

    private static function preview( $design ) {
        // Same structural classes and stylesheet as the storefront; no shopper data/API calls.
        $headers = '<table>' . self::columns( 2 ) . '<tbody><tr><td>' . esc_html__( 'Product', 'products-compare-for-woocommerce' ) . '</td>';
        foreach ( array( __( 'Everyday Backpack', 'products-compare-for-woocommerce' ), __( 'Travel Backpack', 'products-compare-for-woocommerce' ) ) as $name ) {
            $headers .= '<th><div><h3><a>' . esc_html( $name ) . '</a></h3></div></th>';
        }
        $headers .= '</tr><tr><td></td>';
        foreach ( array( '$49.00', '$69.00' ) as $price ) {
            $headers .= '<th><p class="br_compare_price price">' . esc_html( $price ) . '</p><button class="button" type="button" tabindex="-1">' . esc_html__( 'Add to cart', 'woocommerce' ) . '</button></th>';
        }
        $headers .= '</tr></tbody></table>';
        ?>
        <div class="br_new_compare_block<?php echo esc_attr( self::classes( $design ) ); ?>" style="--br-cmp-products-width:400px;--br-cmp-column-width:200px;" aria-hidden="true">
            <div class="br-compare-table-tools"><button class="br_show_compare_dif" type="button" tabindex="-1"><?php esc_html_e( 'Hide same values', 'products-compare-for-woocommerce' ); ?></button></div>
            <div class="br_top_table"><div class="br_main_top"><?php echo $headers; ?></div><div class="br_opacity_top"><?php echo $headers; ?></div></div>
            <div class="br_new_compare"><div class="br_right_table"><table><?php echo self::columns( 2 ); ?><tbody>
                <tr><th><?php esc_html_e( 'Availability', 'products-compare-for-woocommerce' ); ?></th><td><p class="stock in-stock"><?php esc_html_e( 'In stock', 'woocommerce' ); ?></p></td><td><p class="stock in-stock"><?php esc_html_e( 'In stock', 'woocommerce' ); ?></p></td></tr>
                <tr><th><?php esc_html_e( 'Capacity', 'products-compare-for-woocommerce' ); ?></th><td>18 L</td><td>28 L</td></tr>
                <tr class="br_same_attr"><th><?php esc_html_e( 'Material', 'products-compare-for-woocommerce' ); ?></th><td><?php esc_html_e( 'Recycled fabric', 'products-compare-for-woocommerce' ); ?></td><td><?php esc_html_e( 'Recycled fabric', 'products-compare-for-woocommerce' ); ?></td></tr>
            </tbody></table></div></div>
        </div>
        <?php
    }
}
