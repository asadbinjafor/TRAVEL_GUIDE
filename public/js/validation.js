(function () {
    function showError(input, msg) {
        let el = input.parentElement.querySelector('.js-error');
        if (!el) {
            el = document.createElement('div');
            el.className = 'field-error js-error';
            input.parentElement.appendChild(el);
        }
        el.textContent = msg;
    }

    function clearErrors(form) {
        form.querySelectorAll('.js-error').forEach((e) => e.remove());
    }

    function validateEmail(input) {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
            showError(input, 'Enter a valid email.');
            return false;
        }
        return true;
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            clearErrors(registerForm);
            let ok = true;
            const email = registerForm.querySelector('#email');
            const pass = registerForm.querySelector('#password');
            const confirm = registerForm.querySelector('#password_confirm');
            if (!validateEmail(email)) ok = false;
            if (pass.value.length < 8) {
                showError(pass, 'Password must be at least 8 characters.');
                ok = false;
            }
            if (pass.value !== confirm.value) {
                showError(confirm, 'Passwords do not match.');
                ok = false;
            }
            if (!ok) e.preventDefault();
        });
    }

    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            clearErrors(profileForm);
            let ok = true;
            const email = profileForm.querySelector('#email');
            const np = profileForm.querySelector('#new_password');
            const nc = profileForm.querySelector('#new_password_confirm');
            if (!validateEmail(email)) ok = false;
            if (np.value || nc.value) {
                if (np.value.length < 8) {
                    showError(np, 'New password must be at least 8 characters.');
                    ok = false;
                }
                if (np.value !== nc.value) {
                    showError(nc, 'Passwords do not match.');
                    ok = false;
                }
            }
            if (!ok) e.preventDefault();
        });
    }

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            clearErrors(loginForm);
            const email = loginForm.querySelector('#email');
            if (!validateEmail(email)) e.preventDefault();
        });
    }
})();
