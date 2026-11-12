(function ($) {
    var form = $('#form-signup');
    var codeInput = $('#signupform-hoscode');
    var nameInput = $('#signup-facility-name');
    var roleInput = $('#signupform-requestedrole');
    var roleLabel = $('#signup-requested-role-label');
    var status = $('#signup-facility-status');
    var roleLabels = {
        provincialAdmin: 'เจ้าหน้าที่ สสจ.',
        hospitalStaff: 'เจ้าหน้าที่โรงพยาบาล (รพ.)',
        pcuStaff: 'เจ้าหน้าที่ รพ.สต.'
    };
    var pendingRequest = null;

    codeInput.on('input change blur', function () {
        var code = $.trim(codeInput.val());
        nameInput.val('');
        roleInput.val('');
        roleLabel.val('');
        status.text('').removeClass('is-error');
        if (pendingRequest) {
            pendingRequest.abort();
            pendingRequest = null;
        }
        if (!/^\d{5}$/.test(code)) {
            return;
        }
        status.text('กำลังค้นหาหน่วยบริการ...');
        pendingRequest = $.getJSON(form.attr('data-facility-url'), {hoscode: code})
            .done(function (result) {
                if ($.trim(codeInput.val()) !== code) {
                    return;
                }
                nameInput.val(result.name || '');
                roleInput.val(result.role || '');
                roleLabel.val(roleLabels[result.role] || '');
                if (!result.name || (result.role !== 'hospitalStaff' && result.role !== 'pcuStaff')) {
                    status.addClass('is-error').text('ไม่พบข้อมูลหน่วยบริการที่สมัครได้ กรุณาตรวจสอบรหัส');
                } else {
                    status.text('พบข้อมูลหน่วยบริการ');
                }
            })
            .fail(function (xhr, reason) {
                if (reason !== 'abort' && $.trim(codeInput.val()) === code) {
                    status.addClass('is-error').text('ไม่สามารถค้นหาหน่วยบริการได้ กรุณาลองอีกครั้ง');
                }
            });
    });
    codeInput.trigger('change');
})(jQuery);
