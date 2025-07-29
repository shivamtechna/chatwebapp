    <?php include("components/header.php"); ?>
    <title>Register</title>
</head>
<body>
    <section class="form__section">
        <form class="main_section" method="POST" enctype="multipart/form-data" action="backend/register">
            <div class="form__title">Register Form</div>
            <div class="form__sub-title">Please Register & Start Chatting</div>
            <label class="form__input-label">Full Name</label>
            <div class="form__input-wrapper">
                <input class="form__input" placeholder="Type your Full Name" autocomplete="off" type="text"
                    name="fullName">
                <div class="error" id="error-fullName"></div>
            </div>
            <label class="form__input-label">Email</label>
            <div class="form__input-wrapper">
                <input class="form__input" placeholder="Type your Email" autocomplete="off" type="email" name="email">
                <div class="error" id="error-email"></div>
            </div>
            <label class="form__input-label">Phone</label>
            <div class="form__input-wrapper">
                <input class="form__input" placeholder="Type your Phone" autocomplete="off" type="text" name="phone">
                <div class="error" id="error-phone"></div>
            </div>
            <label class="form__input-label">Image</label>
            <div class="form__input-wrapper">
                <input class="form__input" placeholder="Enter your Image" autocomplete="off" type="file" name="image">
                <div class="error" id="error-image"></div>
            </div>
            <label class="form__input-label">Password</label>
            <div class="form__input-wrapper">
                <input type="password" id="password-input" autocomplete="off" class="form__input form__input--has-svg"
                    placeholder="Enter password" name="password" />
                <span class="form__pass-toggle">
                    <i class="ri-eye-line"></i>
                </span>
                <div class="error" id="error-password"></div>
            </div>
            <button class="form__submit-btn">Submit</button>
            <a href="index" class="form__sign-up">Login to your account</a>
        </form>
    </section>
    <!-- ---------------JS---------------- -->
    <script src="assets/js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // passwordText
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

        // password validation
        const form = document.querySelector('form.main_section');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Clear errors & red borders
            document.querySelectorAll('.error').forEach(el => el.textContent = '');
            document.querySelectorAll('input').forEach(input => input.classList.remove('error-input'));

            let hasError = false;

            const fullName = form.fullName.value.trim();
            const email = form.email.value.trim();
            const phone = form.phone.value.trim();
            const image = form.image.files[0];
            const password = form.password.value.trim();

            // Full Name Validation
            if (fullName === '') {
                document.getElementById('error-fullName').textContent = 'Full Name is required';
                form.fullName.classList.add('error-input');
                hasError = true;
            }

            // Email Validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                document.getElementById('error-email').textContent = 'Email is required';
                form.email.classList.add('error-input');
                hasError = true;
            } else if (!emailPattern.test(email)) {
                document.getElementById('error-email').textContent = 'Enter a valid email';
                form.email.classList.add('error-input');
                hasError = true;
            }

            // Phone Validation
            const phonePattern = /^\d{10}$/;
            if (phone === '') {
                document.getElementById('error-phone').textContent = 'Phone is required';
                form.phone.classList.add('error-input');
                hasError = true;
            } else if (!phonePattern.test(phone)) {
                document.getElementById('error-phone').textContent = 'Enter a valid 10-digit phone number';
                form.phone.classList.add('error-input');
                hasError = true;
            }

            // Image validation
            if (!image) {
                document.getElementById('error-image').textContent = 'Image is required';
                form.image.classList.add('error-input');
                hasError = true;
            } else if (!['image/jpeg', 'image/png', 'image/jpg'].includes(image.type)) {
                document.getElementById('error-image').textContent = 'Only JPG/PNG images allowed';
                form.image.classList.add('error-input');
                hasError = true;
            }

            // Password validation
            if (password === '') {
                document.getElementById('error-password').textContent = 'Password is required';
                form.password.classList.add('error-input');
                hasError = true;
            } else if (password.length < 6) {
                document.getElementById('error-password').textContent = 'Password must be at least 6 characters';
                form.password.classList.add('error-input');
                hasError = true;
            }

            if (!hasError) {
                form.submit();
            }
        });
    </script>
</body>
</html>