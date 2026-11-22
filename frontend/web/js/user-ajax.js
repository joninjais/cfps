(function ($) {
    'use strict';

    var CARD_SELECTOR = '#user-table-card';
    var MODAL_SELECTOR = '#cfps-user-modal';
    var currentTableStateUrl = window.location.href;
    var activeFetchXhr = null;
    var filterDebounceTimer = null;

    /* 1. ระบบแจ้งเตือน Toast มุมขวาบน */
    function showToast(message, type) {
        if (!message) {
            return;
        }
        var $c = $('.cfps-toast-container');
        if (!$c.length) {
            $c = $('<div class="cfps-toast-container" aria-live="polite"></div>').appendTo('body');
        }
        var kind = (type === 'danger') ? 'error' : (type || 'success');
        var $toast = $(
            '<div class="cfps-toast cfps-toast--' + kind + '">' +
                '<span></span>' +
                '<button type="button" class="cfps-toast__close" aria-label="ปิด">&times;</button>' +
            '</div>'
        );
        $toast.find('span').text(message);
        $c.append($toast);

        var timer = setTimeout(function () {
            $toast.fadeOut(200, function () { $(this).remove(); });
        }, 4200);

        $toast.on('click', '.cfps-toast__close', function () {
            clearTimeout(timer);
            $toast.remove();
        });
    }

    function showFlashes(flashes, fallbackMsg) {
        var shown = false;
        if (flashes && typeof flashes === 'object') {
            $.each(flashes, function (type, msg) {
                if (Array.isArray(msg)) {
                    $.each(msg, function (_, m) { showToast(m, type); shown = true; });
                } else if (msg) {
                    showToast(msg, type);
                    shown = true;
                }
            });
        }
        if (!shown && fallbackMsg) {
            showToast(fallbackMsg, 'success');
        }
    }

    /* 2. โหลดข้อมูลตารางใหม่ผ่าน Pure AJAX (รองรับโหมดรักษาโฟกัสช่องค้นหา) */
    function loadTableByUrl(url, preserveFilterInputs, pushBrowserUrl, silent) {
        var $card = $(CARD_SELECTOR);
        if (!$card.length) {
            return;
        }
        if (activeFetchXhr) {
            activeFetchXhr.abort();
        }

        if (!silent) {
            $card.addClass('is-loading');
        }
        activeFetchXhr = $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-CFPS-Ajax': '1',
                'X-CFPS-Table': '1'
            }
        }).done(function (html) {
            currentTableStateUrl = url;
            if (pushBrowserUrl !== false && window.history && window.history.replaceState) {
                window.history.replaceState(null, '', url);
            }

            var $parsed = $('<div></div>').html(html);
            var $payload = $parsed.find('#user-ajax-payload');
            if (!$payload.length) {
                $payload = $parsed;
            }

            if (preserveFilterInputs) {
                // อัปเดตเฉพาะยอดรวม แถวข้อมูล และ Pagination โดยไม่ทับช่อง <input> ที่กำลังพิมพ์
                $card.find('#user-table-topbar').replaceWith($payload.find('#user-table-topbar'));
                $card.find('#user-table-tbody').replaceWith($payload.find('#user-table-tbody'));
                $card.find('#user-table-footer').replaceWith($payload.find('#user-table-footer'));
                $card.find('.cfps-user-thead-titles').replaceWith($payload.find('.cfps-user-thead-titles'));
            } else {
                $card.html($payload);
            }
        }).fail(function (xhr, statusText) {
            if (statusText !== 'abort' && !silent) {
                showToast('ไม่สามารถโหลดข้อมูลตารางได้ กรุณาลองใหม่อีกครั้ง', 'error');
            }
        }).always(function () {
            $card.removeClass('is-loading');
            activeFetchXhr = null;
        });
    }

    /* 3. สร้าง URL ค้นหาจากค่าในช่อง Filter ปัจจุบัน */
    function buildFilterUrl() {
        var baseUrl = $('.cfps-user-ajax-scope').attr('data-index-url') || window.location.pathname;
        var currentObj = new URL(currentTableStateUrl, window.location.origin);
        var targetObj = new URL(baseUrl, window.location.origin);

        // คงค่า sort และ per-page เดิมไว้ แต่รีเซ็ตหน้ากลับไปหน้า 1 เมื่อพิมพ์กรองใหม่
        if (currentObj.searchParams.get('sort')) {
            targetObj.searchParams.set('sort', currentObj.searchParams.get('sort'));
        }
        if (currentObj.searchParams.get('per-page')) {
            targetObj.searchParams.set('per-page', currentObj.searchParams.get('per-page'));
        }

        $(CARD_SELECTOR).find('.js-user-filter-input').each(function () {
            var name = $(this).attr('name');
            var val = $.trim($(this).val() || '');
            if (name && val !== '') {
                targetObj.searchParams.set(name, val);
            }
        });

        return targetObj.pathname + targetObj.search;
    }

    /* 4. อัปเดตแถบสถานะ "เปิดรับลงทะเบียน เหลือเวลา..." */
    function refreshRegistrationBanner() {
        $('.registration-status[data-status-url]').each(function () {
            var $root = $(this);
            var statusUrl = $root.attr('data-status-url');
            if (!statusUrl) {
                return;
            }
            $.getJSON(statusUrl).done(function (res) {
                if (res && res.open && res.remaining > 0) {
                    $root.attr('data-remaining', res.remaining);
                    $root.find('.registration-status-closed').attr('hidden', true);
                    $root.find('.registration-status-open').removeAttr('hidden');
                    if (res.url) {
                        $root.find('.registration-status-link').attr('href', res.url);
                    }
                } else {
                    $root.attr('data-remaining', 0);
                    $root.find('.registration-status-open').attr('hidden', true);
                    $root.find('.registration-status-closed').removeAttr('hidden');
                    $root.find('.registration-status-link').removeAttr('href');
                }
            });
        });
    }

    /* 5. เปิดแบบฟอร์มใน Modal */
    function openUserModal(url, title) {
        var $modal = $(MODAL_SELECTOR);
        if (!$modal.length) {
            window.location.href = url;
            return;
        }
        $modal.find('.modal-title').text(title || 'จัดการข้อมูลผู้ใช้งาน');
        $modal.find('.modal-body').html(
            '<div class="cfps-modal-loading"><i class="fa fa-spinner fa-spin fa-lg"></i> กำลังโหลดแบบฟอร์ม...</div>'
        );
        $modal.data('currentUrl', url);
        $modal.modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-CFPS-Ajax': '1',
                'X-CFPS-Modal': '1'
            }
        }).done(function (res) {
            if (res && typeof res === 'object' && res.flashes) {
                $modal.modal('hide');
                showFlashes(res.flashes);
                loadTableByUrl(currentTableStateUrl, true, false);
                refreshRegistrationBanner();
                return;
            }
            $modal.find('.modal-body').html(res);
        }).fail(function (xhr) {
            $modal.modal('hide');
            showToast(xhr.status === 403 ? 'คุณไม่มีสิทธิ์ใช้งานส่วนนี้' : 'ไม่สามารถเปิดแบบฟอร์มได้', 'error');
        });
    }

    /* 6. ส่งออกข้อมูลตารางปัจจุบันเป็น CSV (UTF-8 BOM เปิดภาษาไทยใน Excel ได้ทันที) */
    function exportCurrentTableToCsv() {
        var rows = [];
        var headers = ['#', 'ชื่อผู้ใช้', 'ชื่อ-นามสกุล', 'รหัสสถานพยาบาล', 'ชื่อหน่วยบริการ', 'เบอร์โทรศัพท์', 'อีเมล', 'บทบาท/สิทธิ์', 'สิทธิ์ userManager', 'สถานะอนุมัติ'];
        rows.push(headers);

        $('#user-table-tbody tr[data-user-id]').each(function () {
            var $tds = $(this).find('td');
            rows.push([
                $.trim($tds.eq(0).text()),
                $.trim($tds.eq(2).text()),
                $.trim($tds.eq(3).text()),
                $.trim($tds.eq(5).text()),
                $.trim($tds.eq(6).text()),
                $.trim($tds.eq(7).text()),
                $.trim($tds.eq(8).text()),
                $.trim($tds.eq(9).text()),
                $.trim($tds.eq(10).text()),
                $.trim($tds.eq(11).text())
            ]);
        });

        var csvContent = '\uFEFF' + rows.map(function (r) {
            return r.map(function (cell) {
                return '"' + String(cell).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\r\n');

        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'cfps-users-' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    $(function () {
        /* ดึงข้อมูลใหม่อัตโนมัติทุก 10 วินาที เพื่อแสดงผู้สมัครใหม่โดยไม่ต้องรีเฟรช */
        setInterval(function () {
            if (document.hidden || activeFetchXhr || $(MODAL_SELECTOR).hasClass('in')
                || $(document.activeElement).is('.js-user-filter-input')) {
                return;
            }
            loadTableByUrl(currentTableStateUrl, true, false, true);
        }, 10000);

        /* พิมพ์ค้นหาแบบ Real-time Debounce 300ms (ไม่ทำเคอร์เซอร์หลุด) */
        $(document).on('input change', '.js-user-filter-input', function (e) {
            if (e.type === 'change' && $(this).is('input[type="text"]')) {
                return; // ป้องกันยิงซ้ำกับ event input บนช่องข้อความ
            }
            clearTimeout(filterDebounceTimer);
            filterDebounceTimer = setTimeout(function () {
                loadTableByUrl(buildFilterUrl(), true, true);
            }, 300);
        });

        /* ปุ่มล้างตัวกรองทั้งหมด */
        $(document).on('click', '.js-reset-user-filters', function () {
            $(CARD_SELECTOR).find('.js-user-filter-input').val('');
            var baseUrl = $('.cfps-user-ajax-scope').attr('data-index-url') || window.location.pathname;
            loadTableByUrl(baseUrl, false, true);
        });

        /* ปุ่มส่งออก CSV */
        $(document).on('click', '.js-export-user-csv', function () {
            exportCurrentTableToCsv();
        });

        /* คลิกลิงก์เรียงลำดับคอลัมน์ / เปลี่ยนหน้า Pagination / ปุ่มทั้งหมด */
        $(document).on('click', 'a.js-user-nav-link', function (e) {
            var href = $(this).attr('href');
            if (!href || href === '#') {
                return;
            }
            e.preventDefault();
            loadTableByUrl(href, false, true);
        });

        /* คลิกปุ่มเปิดหน้าต่าง Modal (+ เพิ่มผู้ใช้, ตั้งค่าเปิดรับสมัคร, แก้ไข, รีเซ็ตรหัสผ่าน) */
        $(document).on('click', 'a.js-user-modal', function (e) {
            if (e.ctrlKey || e.metaKey || e.shiftKey) {
                return;
            }
            var href = $(this).attr('href');
            if (!href || href === '#') {
                return;
            }
            e.preventDefault();
            var title = $(this).data('modal-title') || $.trim($(this).text()) || 'จัดการผู้ใช้งาน';
            openUserModal(href, title);
        });

        /* ดัก Submit ฟอร์มใน Modal (#cfps-user-modal) */
        $(document).on('submit', MODAL_SELECTOR + ' form', function (e) {
            e.preventDefault();
            var form = this;
            var $form = $(form);
            var $modal = $(MODAL_SELECTOR);
            var actionUrl = $form.attr('action') || $modal.data('currentUrl') || window.location.href;
            var submitter = e.originalEvent && e.originalEvent.submitter ? e.originalEvent.submitter : null;

            var formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.set(submitter.name, submitter.value || '');
            }

            var $btns = $form.find('button[type="submit"], input[type="submit"]');
            $btns.prop('disabled', true);

            $.ajax({
                url: actionUrl,
                type: ($form.attr('method') || 'POST').toUpperCase(),
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CFPS-Ajax': '1',
                    'X-CFPS-Modal': '1'
                }
            }).done(function (res) {
                if (res && typeof res === 'object' && ('success' in res)) {
                    if (res.success) {
                        $modal.modal('hide');
                        showFlashes(res.flashes, 'บันทึกข้อมูลเรียบร้อยแล้ว');
                    } else {
                        showFlashes(res.flashes, 'ไม่สามารถดำเนินการได้');
                    }
                    loadTableByUrl(currentTableStateUrl, true, false);
                    refreshRegistrationBanner();
                    return;
                }
                // Validation ไม่ผ่าน: แสดงฟอร์มพร้อมข้อความแจ้งเตือนใน Modal เดิม
                $modal.find('.modal-body').html(res);
            }).fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                showToast(msg, 'error');
            }).always(function () {
                $btns.prop('disabled', false);
            });
        });

        /* ดักปุ่มคำสั่ง AJAX POST รายแถว (อนุมัติ, ปฏิเสธ, userManager, ลบผู้ใช้) */
        $(document).on('click', '.js-user-post-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            if ($btn.prop('disabled')) {
                return;
            }
            var confirmMsg = $btn.attr('data-confirm-msg');
            if (confirmMsg && !window.confirm(confirmMsg)) {
                return;
            }

            var payload = {};
            var extra = $btn.attr('data-post-params');
            if (extra) {
                try { $.extend(payload, JSON.parse(extra)); } catch (_) {}
            }
            if (window.yii && yii.getCsrfParam && yii.getCsrfToken) {
                payload[yii.getCsrfParam()] = yii.getCsrfToken();
            }

            $btn.prop('disabled', true);
            $.ajax({
                url: $btn.attr('data-url'),
                type: 'POST',
                data: payload,
                headers: { 'X-CFPS-Ajax': '1' }
            }).done(function (res) {
                if (res && typeof res === 'object') {
                    showFlashes(res.flashes, 'ดำเนินการเรียบร้อยแล้ว');
                } else {
                    showToast('ดำเนินการเรียบร้อยแล้ว', 'success');
                }
                loadTableByUrl(currentTableStateUrl, true, false);
                refreshRegistrationBanner();
            }).fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'ไม่สามารถดำเนินการได้';
                showToast(msg, 'error');
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });

        /* ดักฟอร์มปิดรับสมัครในแถบ _registration-status.php (ถ้ามีปุ่ม POST) */
        $(document).on('submit', '#user-registration-banner form', function (e) {
            var $form = $(this);
            if (($form.attr('method') || 'POST').toUpperCase() !== 'POST') {
                return;
            }
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: $form.attr('action') || window.location.href,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CFPS-Ajax': '1' }
            }).done(function (res) {
                if (res && typeof res === 'object') {
                    showFlashes(res.flashes, 'อัปเดตสถานะการรับสมัครแล้ว');
                }
                refreshRegistrationBanner();
            });
        });
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
