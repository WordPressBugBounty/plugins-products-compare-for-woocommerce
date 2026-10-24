(function ($) {
    'use strict';
    $(function () {
        var select = document.getElementById('br-compare-table-design');
        var preview = document.getElementById('br-compare-design-preview');
        if (!select || !preview) { return; }
        function update() {
            var table = preview.querySelector('.br_new_compare_block');
            var value = select.value;
            preview.hidden = value === 'manual';
            table.className = 'br_new_compare_block' + (value === 'manual' ? '' : ' br_compare_design br_compare_design--' + value);
        }
        $(select).on('change', update);
        update();
    });
})(jQuery);
