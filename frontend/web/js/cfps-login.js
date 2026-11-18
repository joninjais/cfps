(function () {
    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-toggle-password]');
        if (!toggle) return;
        var input = document.getElementById(toggle.getAttribute('data-toggle-password'));
        if (!input) return;
        var isPw = input.type === 'password';
        input.type = isPw ? 'text' : 'password';
        var icon = toggle.querySelector('.fa');
        if (icon) {
            icon.className = isPw ? 'fa fa-eye-slash' : 'fa fa-eye';
        }
    });

    var pwInput = document.getElementById('loginform-password');
    var capsHint = document.getElementById('cfps-capslock-hint');
    if (pwInput && capsHint) {
        var checkCaps = function (e) {
            if (e.getModifierState && e.getModifierState('CapsLock')) {
                capsHint.style.display = 'block';
            } else {
                capsHint.style.display = 'none';
            }
        };
        pwInput.addEventListener('keyup', checkCaps);
        pwInput.addEventListener('keydown', checkCaps);
        pwInput.addEventListener('blur', function () { capsHint.style.display = 'none'; });
    }

    var loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function () {
            setTimeout(function () {
                if (loginForm.querySelector('.has-error')) return;
                var btn = loginForm.querySelector('.cfps-login-submit');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> กำลังตรวจสอบสิทธิ์...';
                }
            }, 60);
        });
    }
})();