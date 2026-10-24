(function (wp) {
    'use strict';
    if (!window.brCompareTableBlock) { return; }
    var el = wp.element.createElement, __ = wp.i18n.__, domain = 'products-compare-for-woocommerce';
    var editor = wp.blockEditor || wp.editor;
    wp.blocks.registerBlockType('berocket/compare-table', {
        apiVersion: window.brCompareTableBlock.apiVersion,
        title: __('BeRocket Compare Table', domain), icon: 'editor-table', category: 'widgets',
        description: __('Display each visitor’s selected products in the existing comparison table.', domain),
        keywords: ['BeRocket', __('compare', domain), __('products', domain)],
        supports: { html: false, customClassName: false },
        edit: function (props) {
            var blockProps = editor.useBlockProps ? editor.useBlockProps() : { className: props.className };
            return el('div', blockProps, el(wp.components.Placeholder, {
                className: 'br-compare-table-editor', icon: 'editor-table', label: __('BeRocket Compare Table', domain)
            },
                el('p', null, __('No products selected — editor preview only.', domain)),
                el('p', null, __('On the site, this block shows each visitor’s own comparison list. Add products using the Compare buttons in the shop.', domain)),
                el('p', null, __('Table fields, styles and buttons use your Products Compare settings.', domain))
            ));
        },
        save: function () { return null; }
    });
})(window.wp);
