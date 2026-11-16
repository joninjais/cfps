(function ($) {
    'use strict';

    // พับและกางกล่องรายงาน (.box)
    $(document).on('click', '[data-widget="collapse"]', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $box = $btn.closest('.box');
        var $icon = $btn.find('i.fa, span.glyphicon').first();

        if ($box.hasClass('collapsed-box')) {
            $box.removeClass('collapsed-box');
            $box.children('.box-body, .box-footer').slideDown(180);
            $icon.removeClass('fa-plus glyphicon-plus').addClass('fa-minus glyphicon-minus');
        } else {
            $box.children('.box-body, .box-footer').slideUp(180, function () {
                $box.addClass('collapsed-box');
            });
            $icon.removeClass('fa-minus glyphicon-minus').addClass('fa-plus glyphicon-plus');
        }
    });

    // ปิดกล่องรายงาน (.box)
    $(document).on('click', '[data-widget="remove"]', function (e) {
        e.preventDefault();
        $(this).closest('.box').fadeOut(180, function () {
            $(this).remove();
        });
    });
})(jQuery);
