(function ($) {
    $('.registration-status').each(function () {
        var root = $(this);
        var openPanel = root.find('.registration-status-open');
        var closedPanel = root.find('.registration-status-closed');
        var link = root.find('.registration-status-link');
        var countdown = root.find('.registration-status-countdown');
        var fallback = root.find('.registration-status-fallback');
        var error = root.find('.registration-status-error');
        var deadline = 0;

        function closeStatus() {
            deadline = 0;
            openPanel.attr('hidden', true);
            closedPanel.removeAttr('hidden');
            link.removeAttr('href');
        }

        function updateCountdown() {
            if (!deadline) {
                return;
            }
            var seconds = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
            if (seconds === 0) {
                closeStatus();
                return;
            }
            var days = Math.floor(seconds / 86400);
            var hours = Math.floor(seconds % 86400 / 3600);
            var minutes = Math.floor(seconds % 3600 / 60);
            var remainder = seconds % 60;
            var parts = [];
            if (days) {
                parts.push(days + ' วัน');
            }
            if (days || hours) {
                parts.push(hours + ' ชม.');
            }
            parts.push(minutes + ' นาที', remainder + ' วินาที');
            countdown.text('เหลือเวลา ' + parts.join(' ')).removeAttr('hidden');
            fallback.attr('hidden', true);
        }

        function applyStatus(open, url, remaining, startedAt) {
            if (!open || !url || remaining <= 0) {
                closeStatus();
                return;
            }
            deadline = startedAt + remaining * 1000;
            link.attr('href', url);
            closedPanel.attr('hidden', true);
            openPanel.removeAttr('hidden');
            updateCountdown();
        }

        function refreshStatus() {
            var startedAt = Date.now();
            $.ajax({url: root.attr('data-status-url'), dataType: 'json', cache: false})
                .done(function (result) {
                    error.attr('hidden', true);
                    applyStatus(result.open, result.url, result.remaining, startedAt);
                })
                .fail(function () {
                    deadline = 0;
                    openPanel.attr('hidden', true);
                    closedPanel.attr('hidden', true);
                    link.removeAttr('href');
                    error.removeAttr('hidden');
                })
                .always(function () {
                    setTimeout(refreshStatus, 10000);
                });
        }

        applyStatus(!openPanel.is('[hidden]'), link.attr('href') || 'open',
            Number(root.attr('data-remaining')), Date.now());
        setInterval(updateCountdown, 1000);
        document.addEventListener('visibilitychange', updateCountdown);
        setTimeout(refreshStatus, 10000);
    });
})(jQuery);
