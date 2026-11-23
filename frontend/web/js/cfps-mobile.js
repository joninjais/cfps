(function ($) {
    'use strict';

    /* 1. สร้างแถบควบคุมย่อ-ขยาย Header และระบบ Auto-Hide สำหรับจอแนวนอน */
    function initCollapsibleHeader() {
        var $header = $('.main-header').first();
        var $topmenu = $('.cfps-topmenu').first();

        if (!$header.length || $('body').hasClass('cfps-collapsible-ready')) {
            return;
        }

        $('body').addClass('cfps-collapsible-ready');

        var $origBrand = $header.find('.navbar-brand, .logo').first();
        if (!$origBrand.length) {
            $origBrand = $header.find('a').filter(function () {
                return $(this).find('img').length > 0 || $(this).text().indexOf('CF PS') !== -1;
            }).first();
        }

        var brandHref = '/site/index';
        var logoHtml = '';
        var brandTitle = 'CF PS : Phang-nga';

        if ($origBrand.length) {
            $origBrand.addClass('cfps-original-brand');
            if ($origBrand.parent().hasClass('navbar-header') && $origBrand.siblings().length === 0) {
                $origBrand.parent().addClass('cfps-original-brand-wrap');
            }
            brandHref = $origBrand.attr('href') || brandHref;
            var $img = $origBrand.find('img').first();
            if ($img.length) {
                logoHtml = '<img src="' + $img.attr('src') + '" alt="Logo">';
            }
            var rawText = $.trim($origBrand.text()).replace(/\s+/g, ' ');
            if (rawText) {
                brandTitle = rawText;
            }
        } else {
            var $anyImg = $header.find('img').first();
            if ($anyImg.length) {
                logoHtml = '<img src="' + $anyImg.attr('src') + '" alt="Logo">';
            }
        }

        // ดึงรหัสหน่วยบริการ 5 หลัก (เช่น 11352) มาแสดงบนปุ่มผู้ใช้
        var hoscodeLabel = 'ผู้ใช้';
        $header.find('li, a, span').each(function () {
            var txt = $.trim($(this).text());
            if (/^\d{5}$/.test(txt)) {
                hoscodeLabel = txt;
                return false;
            }
        });

        var menuBtnHtml = $topmenu.length
            ? '<button type="button" class="cfps-hdr-btn js-toggle-topmenu" aria-expanded="false" title="ย่อ/ขยายเมนูหลัก">' +
                  '<i class="fa fa-bars"></i> <span>เมนู</span> <i class="fa fa-caret-down js-caret"></i>' +
              '</button>'
            : '';

        var $compactBar = $(
            '<div class="cfps-mobile-header-bar">' +
                '<a href="' + brandHref + '" class="cfps-mobile-brand">' +
                    logoHtml +
                    '<span class="cfps-mobile-brand-text">' + brandTitle + '</span>' +
                '</a>' +
                '<div class="cfps-mobile-actions">' +
                    menuBtnHtml +
                    '<button type="button" class="cfps-hdr-btn js-toggle-userpanel" aria-expanded="false" title="ย่อ/ขยายข้อมูลผู้ใช้">' +
                        '<i class="fa fa-user-circle"></i> <span>' + hoscodeLabel + '</span> <i class="fa fa-caret-down js-caret"></i>' +
                    '</button>' +
                    '<button type="button" class="cfps-hdr-btn cfps-hdr-btn--hide js-minimize-header" title="ซ่อนแถบหัวเว็บ">' +
                        '<i class="fa fa-chevron-up"></i>' +
                    '</button>' +
                '</div>' +
            '</div>'
        );

        var $restoreBtn = $(
            '<button type="button" class="cfps-header-restore-btn" title="แสดงแถบหัวเว็บ">' +
                '<i class="fa fa-bars"></i> <span>หัวเว็บ / เมนู</span> <i class="fa fa-chevron-down"></i>' +
            '</button>'
        );

        $header.before($compactBar);
        $('body').append($restoreBtn);

        function isAnyPanelOpen() {
            return $header.hasClass('is-mobile-expanded') || $topmenu.hasClass('is-mobile-expanded');
        }

        function closeAllPanels() {
            $header.removeClass('is-mobile-expanded');
            $topmenu.removeClass('is-mobile-expanded');
            $compactBar.find('.js-toggle-userpanel, .js-toggle-topmenu')
                .removeClass('is-active')
                .attr('aria-expanded', 'false')
                .find('.js-caret')
                .removeClass('fa-caret-up')
                .addClass('fa-caret-down');
        }

        $compactBar.on('click', '.js-toggle-userpanel', function (e) {
            e.stopPropagation();
            var willOpen = !$header.hasClass('is-mobile-expanded');
            closeAllPanels();
            if (willOpen) {
                $header.addClass('is-mobile-expanded');
                $(this).addClass('is-active').attr('aria-expanded', 'true')
                    .find('.js-caret').removeClass('fa-caret-down').addClass('fa-caret-up');
            }
        });

        $compactBar.on('click', '.js-toggle-topmenu', function (e) {
            e.stopPropagation();
            var willOpen = !$topmenu.hasClass('is-mobile-expanded');
            closeAllPanels();
            if (willOpen) {
                $topmenu.addClass('is-mobile-expanded');
                $(this).addClass('is-active').attr('aria-expanded', 'true')
                    .find('.js-caret').removeClass('fa-caret-down').addClass('fa-caret-up');
            }
        });

        // ล้าง .dropdown-backdrop ของ Bootstrap 3 ทันทีที่กดกางหมวดหมู่ในโหมดมือถือ
        $topmenu.on('shown.bs.dropdown click', '.cfps-nav-group, .cfps-nav-toggle', function () {
            $('.dropdown-backdrop').remove();
        });

        // ให้ลิงก์ย่อยใน .cfps-nav-menu เปลี่ยนหน้าได้ทันทีบนมือถือโดยไม่ถูกตัวดักเมนูแม่กั้น
        $topmenu.on('click', '.cfps-nav-menu a[href]', function (e) {
            var href = $.trim($(this).attr('href') || '');
            if (!href || href === '#' || href.toLowerCase().indexOf('javascript:') === 0) {
                return;
            }
            if ($(this).is('[data-method], [data-confirm], [data-toggle]')) {
                return;
            }
            e.stopPropagation();
            closeAllPanels();
        });

        // ปุ่มซ่อนหัวเว็บด้วยตนเอง [▲]
        $compactBar.on('click', '.js-minimize-header', function (e) {
            e.stopPropagation();
            closeAllPanels();
            $('body').addClass('cfps-header-minimized');
        });

        // ปุ่มลอยกางหัวเว็บกลับมา [▼ หัวเว็บ / เมนู]
        $restoreBtn.on('click', function (e) {
            e.stopPropagation();
            $('body').removeClass('cfps-header-minimized cfps-scroll-hidden');
        });

        // Auto-Hide เมื่อเลื่อนหน้าจอลงบนมือถือแนวนอน (ความสูงจอ <= 540px)
        var lastScrollY = window.pageYOffset || 0;
        $(window).on('scroll', function () {
            var currentY = window.pageYOffset || document.documentElement.scrollTop || 0;
            if (window.innerHeight <= 540 && !$('body').hasClass('cfps-header-minimized') && !isAnyPanelOpen()) {
                if (currentY > 45 && currentY > lastScrollY + 6) {
                    $('body').addClass('cfps-scroll-hidden');
                } else if (currentY < 20 || currentY < lastScrollY - 18) {
                    $('body').removeClass('cfps-scroll-hidden');
                }
            } else if (window.innerHeight > 540) {
                $('body').removeClass('cfps-scroll-hidden');
            }
            lastScrollY = currentY;
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.cfps-sticky-top, .main-header, .cfps-topmenu, .cfps-mobile-header-bar, .cfps-header-restore-btn').length) {
                closeAllPanels();
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllPanels();
            }
        });
    }

    /* 2. หุ้มตารางอัตโนมัติ + สร้างปุ่มสลับมุมมองการ์ดบนมือถือ */
    function enhanceTablesForMobile($context) {
        var $scope = $context || $(document);

        $scope.find('.content table.table, .deptor-table, .grid-view table').each(function () {
            var $table = $(this);
            if ($table.data('cfpsMobileReady')) {
                return;
            }
            $table.data('cfpsMobileReady', true);

            var $wrapper = $table.parent();
            if (!$wrapper.hasClass('cfps-table-scroll') && !$wrapper.hasClass('table-responsive')) {
                $table.wrap('<div class="cfps-table-scroll"></div>');
                $wrapper = $table.parent();
            } else if ($wrapper.hasClass('table-responsive')) {
                $wrapper.addClass('cfps-table-scroll');
            }

            var $headerRows = $table.find('> thead > tr');
            var hasComplexHeader = $table.find('> thead [colspan], > thead [rowspan]').length > 0;
            if ($headerRows.length === 0 || hasComplexHeader) {
                return;
            }

            var $titleRow = $headerRows.not('.filters').first();
            var headers = [];
            $titleRow.children('th, td').each(function () {
                var text = $.trim($(this).text()).replace(/\s+/g, ' ');
                headers.push(text || 'รายการ');
            });

            if (headers.length < 3) {
                return;
            }

            var hasDataRows = false;
            $table.find('> tbody > tr').each(function () {
                var $cells = $(this).children('td');
                if ($cells.length !== headers.length) {
                    return;
                }
                hasDataRows = true;
                $cells.each(function (idx) {
                    if (!$(this).attr('data-label')) {
                        $(this).attr('data-label', headers[idx]);
                    }
                });
            });

            if (!hasDataRows) {
                return;
            }

            var $toolbar = $(
                '<div class="cfps-mobile-table-bar">' +
                    '<span class="cfps-scroll-hint"><i class="fa fa-arrows-h"></i> ปัดซ้าย-ขวาเพื่อดูตารางครบทุกช่อง</span>' +
                    '<button type="button" class="cfps-view-toggle-btn">' +
                        '<i class="fa fa-th-list"></i> มุมมองการ์ด' +
                    '</button>' +
                '</div>'
            );

            $wrapper.before($toolbar);

            $toolbar.on('click', '.cfps-view-toggle-btn', function () {
                var isCard = $wrapper.toggleClass('is-card-view').hasClass('is-card-view');
                $(this).toggleClass('is-active', isCard);
                if (isCard) {
                    $(this).html('<i class="fa fa-table"></i> มุมมองตาราง');
                    $toolbar.find('.cfps-scroll-hint').text('แสดงข้อมูลเรียงรายแถวแบบการ์ด');
                } else {
                    $(this).html('<i class="fa fa-th-list"></i> มุมมองการ์ด');
                    $toolbar.find('.cfps-scroll-hint').html('<i class="fa fa-arrows-h"></i> ปัดซ้าย-ขวาเพื่อดูตารางครบทุกช่อง');
                }
            });
        });
    }

    $(function () {
        initCollapsibleHeader();
        enhanceTablesForMobile($(document));

        $(document).on('pjax:complete', function (event) {
            enhanceTablesForMobile($(event.target));
        });
    });
})(jQuery);
