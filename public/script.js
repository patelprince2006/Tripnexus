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

// ===================================================================
// Password Strength Validation
// Rules: min 8 chars, 1 uppercase, 1 lowercase, 1 special character
// ===================================================================
function validatePasswordStrength(password) {
    const rules = {
        minLength: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password),
    };
    const allPassed = rules.minLength && rules.uppercase && rules.lowercase && rules.special;
    return { rules, allPassed };
}

function updatePasswordIndicator(passwordInput) {
    const password = passwordInput.value;
    const { rules } = validatePasswordStrength(password);

    // Find the indicator container (sibling of the input group)
    const container = passwordInput.closest('.mb-3, .mb-4') || passwordInput.parentElement.parentElement;
    let indicator = container.querySelector('.pwd-strength-indicator');

    // Create indicator if it doesn't exist
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.className = 'pwd-strength-indicator mt-2';
        indicator.innerHTML = `
            <div class="pwd-strength-bar mb-1" style="height: 4px; border-radius: 2px; background: #e9ecef; overflow: hidden;">
                <div class="pwd-strength-fill" style="height: 100%; width: 0%; border-radius: 2px; transition: all 0.3s ease;"></div>
            </div>
            <div class="pwd-rules" style="font-size: 0.78rem; line-height: 1.6;">
                <div class="pwd-rule" data-rule="minLength"><span class="pwd-icon">○</span> Minimum 8 characters</div>
                <div class="pwd-rule" data-rule="uppercase"><span class="pwd-icon">○</span> At least one uppercase letter (A-Z)</div>
                <div class="pwd-rule" data-rule="lowercase"><span class="pwd-icon">○</span> At least one lowercase letter (a-z)</div>
                <div class="pwd-rule" data-rule="special"><span class="pwd-icon">○</span> At least one special character (!@#$%^&*)</div>
            </div>
        `;
        // Insert after password input group
        const inputGroup = passwordInput.closest('.input-group') || passwordInput.parentElement;
        inputGroup.parentElement.insertBefore(indicator, inputGroup.nextSibling);
    }

    if (password.length === 0) {
        indicator.style.display = 'none';
        return;
    }
    indicator.style.display = 'block';

    // Update each rule
    const ruleEls = indicator.querySelectorAll('.pwd-rule');
    ruleEls.forEach(el => {
        const ruleName = el.getAttribute('data-rule');
        const passed = rules[ruleName];
        const icon = el.querySelector('.pwd-icon');
        if (passed) {
            el.style.color = '#198754';
            icon.textContent = '✓';
        } else {
            el.style.color = '#dc3545';
            icon.textContent = '✗';
        }
    });

    // Update strength bar
    const passedCount = Object.values(rules).filter(Boolean).length;
    const fill = indicator.querySelector('.pwd-strength-fill');
    const percent = (passedCount / 4) * 100;
    fill.style.width = percent + '%';

    if (passedCount <= 1) {
        fill.style.background = '#dc3545'; // red
    } else if (passedCount === 2) {
        fill.style.background = '#fd7e14'; // orange
    } else if (passedCount === 3) {
        fill.style.background = '#ffc107'; // yellow
    } else {
        fill.style.background = '#198754'; // green
    }
}

// ===================================================================
// Registration Form Validation
// ===================================================================
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordError = document.getElementById('passwordError');

    if (registerForm && passwordInput) {
        // Real-time password strength indicator
        passwordInput.addEventListener('input', () => {
            updatePasswordIndicator(passwordInput);
        });

        // Real-time confirm password match
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', () => {
                if (passwordInput.value === confirmPasswordInput.value) {
                    if (passwordError) passwordError.textContent = "";
                    confirmPasswordInput.classList.remove('is-invalid');
                    confirmPasswordInput.classList.add('is-valid');
                } else {
                    confirmPasswordInput.classList.remove('is-valid');
                    if (passwordError) passwordError.textContent = "Passwords do not match!";
                    confirmPasswordInput.classList.add('is-invalid');
                }
            });
        }

        // On submit validation
        registerForm.addEventListener('submit', (e) => {
            const password = passwordInput.value;
            const { rules, allPassed } = validatePasswordStrength(password);

            if (!allPassed) {
                e.preventDefault();
                let msg = 'Password must contain:';
                if (!rules.minLength) msg += '\n• At least 8 characters';
                if (!rules.uppercase) msg += '\n• At least one uppercase letter (A-Z)';
                if (!rules.lowercase) msg += '\n• At least one lowercase letter (a-z)';
                if (!rules.special) msg += '\n• At least one special character (!@#$%^&*)';
                alert(msg);
                return;
            }

            if (confirmPasswordInput && password !== confirmPasswordInput.value) {
                e.preventDefault();
                if (passwordError) passwordError.textContent = "Passwords do not match!";
                confirmPasswordInput.classList.add('is-invalid');
                return;
            }

            if (passwordError) passwordError.textContent = "";
            if (confirmPasswordInput) confirmPasswordInput.classList.remove('is-invalid');
        });
    }

    // ===================================================================
    // New Password / Reset Password Form Validation
    // ===================================================================
    const resetForm = document.getElementById('resetPasswordForm');
    const newPwdInput = document.getElementById('newPassword');
    const confirmNewPwdInput = document.getElementById('confirmNewPassword');
    const resetPwdError = document.getElementById('resetPasswordError');

    if (resetForm && newPwdInput) {
        // Real-time strength indicator
        newPwdInput.addEventListener('input', () => {
            updatePasswordIndicator(newPwdInput);
        });

        // Real-time confirm match
        if (confirmNewPwdInput) {
            confirmNewPwdInput.addEventListener('input', () => {
                if (newPwdInput.value === confirmNewPwdInput.value) {
                    if (resetPwdError) resetPwdError.textContent = "";
                    confirmNewPwdInput.classList.remove('is-invalid');
                    confirmNewPwdInput.classList.add('is-valid');
                } else {
                    confirmNewPwdInput.classList.remove('is-valid');
                    if (resetPwdError) resetPwdError.textContent = "Passwords do not match!";
                    confirmNewPwdInput.classList.add('is-invalid');
                }
            });
        }

        // On submit
        resetForm.addEventListener('submit', (e) => {
            const password = newPwdInput.value;
            const { rules, allPassed } = validatePasswordStrength(password);

            if (!allPassed) {
                e.preventDefault();
                let msg = 'Password must contain:';
                if (!rules.minLength) msg += '\n• At least 8 characters';
                if (!rules.uppercase) msg += '\n• At least one uppercase letter (A-Z)';
                if (!rules.lowercase) msg += '\n• At least one lowercase letter (a-z)';
                if (!rules.special) msg += '\n• At least one special character (!@#$%^&*)';
                alert(msg);
                return;
            }

            if (confirmNewPwdInput && password !== confirmNewPwdInput.value) {
                e.preventDefault();
                if (resetPwdError) resetPwdError.textContent = "Passwords do not match!";
                confirmNewPwdInput.classList.add('is-invalid');
                return;
            }
        });
    }
});

// ===================================================================
// Generic Email Format Validation
// ===================================================================
document.addEventListener('DOMContentLoaded', () => {
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

// Swaps the values between From and To inputs
function swapLocations() {
    const from = document.getElementById('fromInput');
    const to = document.getElementById('toInput');
    const temp = from.value || from.placeholder;
    from.value = to.value || to.placeholder;
    to.value = temp;
}

// Enforce date inputs to start from today
document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;

    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.min || input.min < todayStr) {
            input.min = todayStr;
        }
    });
});

// Enables or disables the Return date field based on trip type
function toggleFlightReturn(isRoundTrip) {
    const container = document.getElementById('flightReturnDateGroup');
    const input = document.getElementById('flightReturnDate');
    if (!container || !input) return;

    if (isRoundTrip) {
        container.style.opacity = "1";
        input.disabled = false;
        input.required = true;
    } else {
        container.style.opacity = "0.5";
        input.disabled = true;
        input.required = false;
        input.value = "";
    }
}

function toggleBusReturn(isRoundTrip) {
    const container = document.getElementById('busReturnDateGroup');
    const input = document.getElementById('busReturnDate');
    if (!container || !input) return;

    if (isRoundTrip) {
        container.style.opacity = "1";
        input.disabled = false;
        input.required = true;
    } else {
        container.style.opacity = "0.5";
        input.disabled = true;
        input.required = false;
        input.value = "";
    }
}

// Initialize toggles on page load
document.addEventListener('DOMContentLoaded', () => {
    const flightRoundTrip = document.getElementById('roundTrip');
    if (flightRoundTrip) {
        toggleFlightReturn(flightRoundTrip.checked);
    }

    const busRoundTrip = document.getElementById('busRoundTrip');
    if (busRoundTrip) {
        toggleBusReturn(busRoundTrip.checked);
    }
});

// Keep return date >= departure date (and >= today)
document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;

    function applyMinDate(departureInput, returnInput) {
        if (!departureInput || !returnInput) return;
        const depValue = departureInput.value || todayStr;
        returnInput.min = depValue < todayStr ? todayStr : depValue;
        if (returnInput.value && returnInput.value < returnInput.min) {
            returnInput.value = "";
        }
    }

    // Flight (home + search page)
    const flightDep = document.getElementById('flightDepartureDate');
    const flightReturn = document.getElementById('flightReturnDate') || document.getElementById('returnDate');
    applyMinDate(flightDep, flightReturn);
    if (flightDep) {
        flightDep.addEventListener('change', () => applyMinDate(flightDep, flightReturn));
    }

    // Bus (home)
    const busDep = document.getElementById('busDepartureDate');
    const busReturn = document.getElementById('busReturnDate');
    applyMinDate(busDep, busReturn);
    if (busDep) {
        busDep.addEventListener('change', () => applyMinDate(busDep, busReturn));
    }

    // Hotel (home)
    const hotelCheckIn = document.getElementById('hotelCheckIn');
    const hotelCheckOut = document.getElementById('hotelCheckOut');

    function applyHotelMinDate() {
        if (!hotelCheckIn || !hotelCheckOut) return;
        
        // Check-out must be at least 1 day after check-in
        const checkInVal = hotelCheckIn.value;
        if (checkInVal) {
            const nextDay = new Date(checkInVal);
            nextDay.setDate(nextDay.getDate() + 1);
            
            const yyyy = nextDay.getFullYear();
            const mm = String(nextDay.getMonth() + 1).padStart(2, '0');
            const dd = String(nextDay.getDate()).padStart(2, '0');
            const nextDayStr = `${yyyy}-${mm}-${dd}`;
            
            hotelCheckOut.min = nextDayStr;
            
            if (hotelCheckOut.value && hotelCheckOut.value < nextDayStr) {
                hotelCheckOut.value = nextDayStr;
            }
        } else {
            hotelCheckOut.min = todayStr;
        }
    }

    applyHotelMinDate();
    if (hotelCheckIn) {
        hotelCheckIn.addEventListener('change', applyHotelMinDate);
    }
});


