/**
 * Client-Side JavaScript Form Validation (Bootstrap 5)
 * Kiểm tra dữ liệu form đăng ký / đăng nhập trước khi gửi lên server
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Validate Form Đăng Nhập
    const loginForm = document.querySelector('form#login-form') || document.querySelector('form[action*="dang-nhap"]');
    if (loginForm) {
        initLoginFormValidation(loginForm);
    }

    // 2. Validate Form Đăng Ký
    const registerForm = document.querySelector('form#register-form') || document.querySelector('form[action*="dang-ky"]');
    if (registerForm) {
        initRegisterFormValidation(registerForm);
    }

    function initLoginFormValidation(form) {
        const emailInput = form.querySelector('input[type="email"], input[name="email"]');
        const passInput = form.querySelector('input[type="password"], input[name="password"]');

        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Validate Email
            if (!emailInput || !validateEmail(emailInput.value.trim())) {
                setFieldInvalid(emailInput, 'Vui lòng nhập địa chỉ email hợp lệ (ví dụ: user@example.com)');
                isValid = false;
            } else {
                setFieldValid(emailInput);
            }

            // Validate Password
            if (!passInput || passInput.value.length < 6) {
                setFieldInvalid(passInput, 'Mật khẩu phải có ít nhất 6 ký tự');
                isValid = false;
            } else {
                setFieldValid(passInput);
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Real-time blur event
        if (emailInput) {
            emailInput.addEventListener('blur', function () {
                if (validateEmail(this.value.trim())) setFieldValid(this);
                else setFieldInvalid(this, 'Email không đúng định dạng');
            });
        }

        if (passInput) {
            passInput.addEventListener('blur', function () {
                if (this.value.length >= 6) setFieldValid(this);
                else setFieldInvalid(this, 'Mật khẩu tối thiểu 6 ký tự');
            });
        }
    }

    function initRegisterFormValidation(form) {
        const nameInput = form.querySelector('input[name="name"]');
        const emailInput = form.querySelector('input[type="email"], input[name="email"]');
        const phoneInput = form.querySelector('input[name="phone"]');
        const passInput = form.querySelector('input[name="password"]');
        const confirmInput = form.querySelector('input[name="password_confirmation"]');
        const termsInput = form.querySelector('input[name="terms"]');

        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Họ tên
            if (!nameInput || nameInput.value.trim().length < 2) {
                setFieldInvalid(nameInput, 'Vui lòng nhập họ và tên của bạn (tối thiểu 2 ký tự)');
                isValid = false;
            } else {
                setFieldValid(nameInput);
            }

            // Email
            if (!emailInput || !validateEmail(emailInput.value.trim())) {
                setFieldInvalid(emailInput, 'Email không đúng định dạng (ví dụ: email@domain.com)');
                isValid = false;
            } else {
                setFieldValid(emailInput);
            }

            // Số điện thoại (nếu có)
            if (phoneInput && phoneInput.value.trim().length > 0) {
                if (!validatePhone(phoneInput.value.trim())) {
                    setFieldInvalid(phoneInput, 'Số điện thoại không hợp lệ (10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09)');
                    isValid = false;
                } else {
                    setFieldValid(phoneInput);
                }
            }

            // Mật khẩu
            if (!passInput || passInput.value.length < 6) {
                setFieldInvalid(passInput, 'Mật khẩu phải chứa ít nhất 6 ký tự');
                isValid = false;
            } else {
                setFieldValid(passInput);
            }

            // Nhập lại mật khẩu
            if (confirmInput) {
                if (confirmInput.value !== passInput.value || confirmInput.value === '') {
                    setFieldInvalid(confirmInput, 'Mật khẩu xác nhận không trùng khớp');
                    isValid = false;
                } else {
                    setFieldValid(confirmInput);
                }
            }

            // Điều khoản
            if (termsInput && !termsInput.checked) {
                setFieldInvalid(termsInput, 'Bạn cần đồng ý với Điều khoản dịch vụ & Chính sách của SFood');
                isValid = false;
            } else if (termsInput) {
                setFieldValid(termsInput);
            }

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        // Real-time events
        if (nameInput) {
            nameInput.addEventListener('input', function () {
                if (this.value.trim().length >= 2) setFieldValid(this);
            });
        }
        if (emailInput) {
            emailInput.addEventListener('input', function () {
                if (validateEmail(this.value.trim())) setFieldValid(this);
            });
        }
        if (passInput) {
            passInput.addEventListener('input', function () {
                if (this.value.length >= 6) setFieldValid(this);
                if (confirmInput && confirmInput.value.length > 0) {
                    if (confirmInput.value === this.value) setFieldValid(confirmInput);
                    else setFieldInvalid(confirmInput, 'Mật khẩu xác nhận không khớp');
                }
            });
        }
        if (confirmInput) {
            confirmInput.addEventListener('input', function () {
                if (passInput && this.value === passInput.value) setFieldValid(this);
                else setFieldInvalid(this, 'Mật khẩu xác nhận không khớp');
            });
        }
    }

    function setFieldInvalid(input, message) {
        if (!input) return;
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        let feedback = input.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
    }

    function setFieldValid(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }

    function validateEmail(email) {
        const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(email);
    }

    function validatePhone(phone) {
        const re = /^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/;
        return re.test(phone.replace(/\s+/g, ''));
    }
});

