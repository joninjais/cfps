(function ($) {
    'use strict';

    var $form = $('#card-search-form');
    var $input = $('#cid-input');
    var $results = $('#card-search-results');
    var $loading = $('#card-search-loading');
    var $btnSubmit = $('#btn-submit-cid');
    var currentXhr = null;

    function runAjaxSearch(cidValue) {
        if (currentXhr && currentXhr.readyState !== 4) {
            currentXhr.abort();
        }

        var actionUrl = $form.attr('action');
        $loading.show();
        $btnSubmit.prop('disabled', true);

        // อัปเดต URL บนเบราว์เซอร์เพื่อให้คัดลอกลิงก์หรือกด Refresh ได้ตรงกับค่าล่าสุด
        if (window.history && window.history.replaceState) {
            var newUrl = cidValue
                ? actionUrl + (actionUrl.indexOf('?') === -1 ? '?' : '&') + 'cid=' + encodeURIComponent(cidValue)
                : actionUrl;
            window.history.replaceState({}, '', newUrl);
        }

        currentXhr = $.ajax({
            url: actionUrl,
            type: 'GET',
            data: { cid: cidValue, ajax: 1 },
            success: function (html) {
                $results.html(html);
            },
            error: function (xhr, status) {
                if (status !== 'abort') {
                    $results.html(
                        '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> เกิดข้อผิดพลาดในการเชื่อมต่อระบบ กรุณาลองใหม่อีกครั้ง</div>'
                    );
                }
            },
            complete: function () {
                $loading.hide();
                $btnSubmit.prop('disabled', false);
                $input.focus();
            }
        });
    }

    $form.on('submit', function (e) {
        e.preventDefault();
        var val = $.trim($input.val());
        runAjaxSearch(val);
    });

    $('#btn-clear-cid').on('click', function () {
        $input.val('').focus();
        runAjaxSearch('');
    });

    // รองรับการคลิกปุ่มคัดลอกเลข CID
    $(document).on('click', '.js-copy-cid', function () {
        var cid = $(this).data('cid') || '';
        var $btn = $(this);
        if (navigator.clipboard && cid) {
            navigator.clipboard.writeText(String(cid)).then(function () {
                var oldHtml = $btn.html();
                $btn.html('<i class="fa fa-check"></i> คัดลอกแล้ว');
                setTimeout(function () {
                    $btn.html(oldHtml);
                }, 1500);
            });
        }
    });
})(jQuery);
/* ย้าย Bootstrap Modal ออกไปต่อท้าย <body> อัตโนมัติ พร้อมล้างตัวเก่าที่มี ID ซ้ำจากการโหลด AJAX */
(function ($) {
    if (!$) return;
    $(document).on('show.bs.modal', '.modal', function () {
        var $modal = $(this);
        var modalId = $modal.attr('id');
        if (modalId) {
            $('body > .modal#' + $.escapeSelector(modalId)).not($modal).remove();
        }
        if (!$modal.parent().is('body')) {
            $modal.appendTo('body');
        }
    });
})(window.jQuery);
