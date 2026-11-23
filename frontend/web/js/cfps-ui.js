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

/* ย้าย Bootstrap Modal ทุกตัวไปเป็นลูกตรงของ <body> อัตโนมัติ เพื่อไม่ให้ติด Stacking Context ของ .content-wrapper หรือ .table-responsive */
(function ($) {
    if (!$) return;
    $(document).on('show.bs.modal', '.modal', function () {
        var $modal = $(this);
        if (!$modal.parent().is('body')) {
            $modal.appendTo('body');
        }
    });
})(window.jQuery);

