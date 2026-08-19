<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class BRCMP_Divi5_Module extends BRCMP_Divi5_Module_Renderer implements DependencyInterface {
    private $module_dir;
    private $module_class;
    private $style_attrs;

    public function __construct( $config ) {
        parent::__construct( $config );
        $this->module_dir   = $config['module_dir'];
        $this->module_class = $config['module_class'];
        $this->style_attrs  = $config['style_attrs'];
    }

    public function load() {
        add_action( 'init', array( $this, 'register_module' ) );
    }

    public function register_module() {
        ModuleRegistration::register_module(
            dirname( __DIR__ ) . '/visual-builder/src/modules/' . $this->module_dir,
            array(
                'render_callback' => array( $this, 'render_callback' ),
            )
        );
    }

    public function module_styles( $args ) {
        $elements = $args['elements'];
        $styles   = array(
            $elements->style(
                array(
                    'attrName'   => 'module',
                    'styleProps' => array(
                        'disabledOn' => array(
                            'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
                        ),
                    ),
                )
            ),
        );

        foreach( $this->style_attrs as $attr_name ) {
            $styles[] = $elements->style( array( 'attrName' => $attr_name ) );
        }

        Style::add(
            array(
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => $styles,
            )
        );
    }

    public function module_script_data( $args ) {
        $args['elements']->script_data( array( 'attrName' => 'module' ) );
    }

    public function module_classnames( $args ) {
        $args['classnamesInstance']->add(
            ElementClassnames::classnames(
                array(
                    'attrs' => $args['attrs']['module']['decoration'] ?? array(),
                )
            )
        );
        $args['classnamesInstance']->add( $this->module_class );
    }

    public function render_callback( $attrs, $content, $block, $elements ) {
        $inner_html = HTMLUtility::render(
            array(
                'tag'               => 'div',
                'attributes'        => array( 'class' => 'et_pb_module_inner' ),
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => $this->render_module( $attrs ),
            )
        );

        $style_components = $elements->style_components( array( 'attrName' => 'module' ) );
        foreach( $this->style_attrs as $attr_name ) {
            $style_components .= $elements->style_components( array( 'attrName' => $attr_name ) );
        }

        return Module::render(
            array(
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'moduleClassName'     => $this->module_class,
                'name'                => $block->block_type->name,
                'classnamesFunction'  => array( $this, 'module_classnames' ),
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => array( $this, 'module_styles' ),
                'scriptDataComponent' => array( $this, 'module_script_data' ),
                'children'            => $style_components . $inner_html,
            )
        );
    }
}
