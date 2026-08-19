<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function brcmp_divi5_module_configs() {
    return array(
        'brcmp/compare-widget' => array(
            'module_dir'   => 'compare-widget',
            'module_class' => 'et_pb_brcmp_compare_widget',
            'type'         => 'widget',
            'defaults'     => array(
                'title'        => '',
                'fast_compare' => 'off',
                'type'         => 'image',
                'toolbar'      => 'off',
            ),
            'style_attrs'  => array( 'compareButton', 'productLabel' ),
        ),
        'brcmp/compare-button' => array(
            'module_dir'   => 'compare-button',
            'module_class' => 'et_pb_br_compare_button',
            'type'         => 'button',
            'defaults'     => array(
                'content.advanced.product' => 'dynamic',
                'fast_compare'             => 'on',
                'add_compare'              => '',
                'added_compare'            => '',
            ),
            'style_attrs'  => array( 'button', 'checkMark' ),
        ),
        'brcmp/compare-table' => array(
            'module_dir'   => 'compare-table',
            'module_class' => 'et_pb_brcmp_compare_table',
            'type'         => 'table',
            'defaults'     => array(),
            'style_attrs'  => array( 'productTitle', 'productPrice', 'attributeName', 'attributeValue' ),
        ),
    );
}
