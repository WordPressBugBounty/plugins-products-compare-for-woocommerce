<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ModuleRenderer.php';
require_once __DIR__ . '/Module.php';

function brcmp_divi5_register_modules( $dependency_tree ) {
    foreach( brcmp_divi5_module_configs() as $module ) {
        $dependency_tree->add_dependency( new BRCMP_Divi5_Module( $module ) );
    }
}
