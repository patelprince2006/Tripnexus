const navEl = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        // If scrolled down, turn navbar dark
        navEl.classList.add('navbar-scrolled');
    } else {
        // If at top, keep transparent
        navEl.classList.remove('navbar-scrolled');
    }
});

// Password Validation
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordError = document.getElementById('passwordError');

    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            if (passwordInput.value !== confirmPasswordInput.value) {
                e.preventDefault(); // Prevent form submission
                passwordError.textContent = "Passwords do not match!";
                confirmPasswordInput.classList.add('is-invalid');
            } else {
                passwordError.textContent = "";
                confirmPasswordInput.classList.remove('is-invalid');
            }
        });

        // Optional: Real-time validation
        confirmPasswordInput.addEventListener('input', () => {
            if (passwordInput.value === confirmPasswordInput.value) {
                passwordError.textContent = "";
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            } else {
                confirmPasswordInput.classList.remove('is-valid');
            }
        });
    }
});

// Swaps the values between From and To inputs
function swapLocations() {
    const from = document.getElementById('fromInput');
    const to = document.getElementById('toInput');
    const temp = from.value || from.placeholder;
    from.value = to.value || to.placeholder;
    to.value = temp;
}

// Enables or disables the Return date field based on trip type
function toggleReturn(isRoundTrip) {
    const container = document.getElementById('returnContainer');
    const input = document.getElementById('returnInput');
    if (isRoundTrip) {
        container.classList.remove('opacity-50', 'bg-opacity-10');
        input.disabled = false;
    } else {
        container.classList.add('opacity-50', 'bg-opacity-10');
        input.disabled = true;
        input.value = "";
    }
}

// Form Validation
document.addEventListener('DOMContentLoaded', () => {
    // 1. Registration Form Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorDiv = document.getElementById('passwordError');

            // Password Match Check
            if (password !== confirmPassword) {
                e.preventDefault();
                errorDiv.textContent = "Passwords do not match!";
                return;
            }

            // Password Strength Check (Minimum 8 characters)
            if (password.length < 8) {
                e.preventDefault();
                errorDiv.textContent = "Password must be at least 8 characters long.";
                return;
            }
        });
    }

    // 2. Generic Email Format Validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', () => {
            const emailRegEx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegEx.test(input.value) && input.value !== "") {
                input.classList.add('is-invalid');
                alert("Please enter a valid email address.");
            } else {
                input.classList.remove('is-invalid');
            }
        });
    });
});