    <?php include("components/header.php"); ?>
    <title>Login</title>
</head>
<body>
    <section class="form__section">
        <form class="main_section" action="backend/login" method="POST">
            <div class="form__title">Login Form</div>
            <div class="form__sub-title">Please Sign In to Access Your Account</div>
            <label class="form__input-label">Email or Phone</label>
            <div class="form__input-wrapper">
                <input class="form__input" placeholder="Type your Email or Phone..." autocomplete="off" type="text"
                    name="email_phone">
            </div>
            <label class="form__input-label">Password</label>
            <div class="form__input-wrapper">
                <input type="password" id="password-input" autocomplete="off" class="form__input form__input--has-svg"
                    placeholder="Enter password" name="password" />
                <span class="form__pass-toggle">
                    <i class="ri-eye-line"></i>
                </span>
            </div>
            <a href="forget-password" class="form__remmember">Forgot your password?</a>
            <button class="form__submit-btn">Submit</button>
            <a href="register" class="form__sign-up">Create a New Account</a>
        </form>
    </section>
    <!-- ---------------JS---------------- -->
    <script src="assets/js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const passwordToggle = document.querySelector(".form__pass-toggle");
            const passwordInput = document.querySelector("#password-input");
            const passwordIcon = passwordToggle?.querySelector("i");

            if (passwordToggle && passwordInput && passwordIcon) {
                passwordToggle.addEventListener("click", () => {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    passwordToggle.classList.toggle("form__pass-toggle--active");

                    if (isPassword) {
                        passwordIcon.classList.remove("ri-eye-line");
                        passwordIcon.classList.add("ri-eye-fill");
                    } else {
                        passwordIcon.classList.remove("ri-eye-fill");
                        passwordIcon.classList.add("ri-eye-line");
                    }
                });
            }
        });
    </script>
    <script>
        const form = document.querySelector('.main_section');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Clear previous errors and styles
            document.querySelectorAll('.error-msg').forEach(el => el.remove());
            form.email_phone.classList.remove('error-input');
            form.password.classList.remove('error-input');

            let hasError = false;

            const emailPhone = form.email_phone.value.trim();
            const password = form.password.value.trim();

            if (emailPhone === '') {
                showError(form.email_phone, "Email or Phone is required");
                hasError = true;
            }

            if (password === '') {
                showError(form.password, "Password is required");
                hasError = true;
            }

            if (!hasError) {
                form.submit();
            }

            function showError(input, message) {
                input.classList.add('error-input');
                const error = document.createElement('div');
                error.className = 'error-msg';
                error.style.color = 'red';
                error.style.fontSize = '12px';
                error.textContent = message;
                input.parentNode.appendChild(error);
            }
        });
    </script>
</body>
</html>