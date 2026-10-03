document.addEventListener("DOMContentLoaded", function () {

    // Mobile Sidebar 

    const sidebar = document.getElementById("patientSidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");

    let sidebarOverlay = document.querySelector(".sidebar-overlay");

    if (!sidebarOverlay) {
        sidebarOverlay = document.createElement("div");
        sidebarOverlay.className = "sidebar-overlay";
        document.body.appendChild(sidebarOverlay);
    }

    function openSidebar() {

        if (!sidebar) {
            return;
        }

        sidebar.classList.add("mobile-open");
        sidebarOverlay.classList.add("show");
        document.body.classList.add("no-scroll");

        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector("i");

            if (icon) {
                icon.classList.remove("fa-bars");
                icon.classList.add("fa-xmark");
            }

            sidebarToggle.setAttribute(
                "aria-label",
                "Close navigation menu"
            );
        }
    }

    function closeSidebar() {

        if (!sidebar) {
            return;
        }

        sidebar.classList.remove("mobile-open");
        sidebarOverlay.classList.remove("show");
        document.body.classList.remove("no-scroll");

        if (sidebarToggle) {
            const icon = sidebarToggle.querySelector("i");

            if (icon) {
                icon.classList.remove("fa-xmark");
                icon.classList.add("fa-bars");
            }

            sidebarToggle.setAttribute(
                "aria-label",
                "Open navigation menu"
            );
        }
    }

    if (sidebarToggle && sidebar) {

        sidebarToggle.addEventListener("click", function () {

            if (sidebar.classList.contains("mobile-open")) {
                closeSidebar();
            } else {
                openSidebar();
            }

        });

    }

    sidebarOverlay.addEventListener("click", function () {
        closeSidebar();
    });

    if (sidebar) {

        const sidebarLinks = sidebar.querySelectorAll("a");

        sidebarLinks.forEach(function (link) {

            link.addEventListener("click", function () {

                if (window.innerWidth <= 992) {
                    closeSidebar();
                }

            });

        });

    }

    document.addEventListener("keydown", function (event) {

        if (
            event.key === "Escape" &&
            sidebar &&
            sidebar.classList.contains("mobile-open")
        ) {
            closeSidebar();
        }

    });

    window.addEventListener("resize", function () {

        if (window.innerWidth > 992) {
            closeSidebar();
        }

    });


    
    //    Shared Validation Helpers

    function showError(inputElement, message) {

        if (!inputElement) {
            return;
        }

        const formGroup =
            inputElement.closest(".dashboard-form-group");

        if (!formGroup) {
            return;
        }

        const errorElement =
            formGroup.querySelector(".field-error");

        inputElement.classList.add("input-error");

        if (errorElement) {
            errorElement.textContent = message;
        }

    }

    function clearError(inputElement) {

        if (!inputElement) {
            return;
        }

        const formGroup =
            inputElement.closest(".dashboard-form-group");

        if (!formGroup) {
            return;
        }

        const errorElement =
            formGroup.querySelector(".field-error");

        inputElement.classList.remove("input-error");

        if (errorElement) {
            errorElement.textContent = "";
        }

    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^[0-9]{10,15}$/.test(phone);
    }


    
    //    Profile Form Validation
    

    const profileForm = document.getElementById("profileForm");

    const phoneInput = document.getElementById("phone");
    const emailInput = document.getElementById("email");
    const emergencyContactInput =
        document.getElementById("emergency_contact");
    const addressInput = document.getElementById("address");

    function validatePhone() {

        if (!phoneInput) {
            return true;
        }

        clearError(phoneInput);

        const value = phoneInput.value.trim();

        if (value === "") {
            showError(
                phoneInput,
                "Please enter your phone number."
            );

            return false;
        }

        if (!isValidPhone(value)) {
            showError(
                phoneInput,
                "Phone number must contain 10 to 15 digits."
            );

            return false;
        }

        return true;
    }

    function validateEmail() {

        if (!emailInput) {
            return true;
        }

        clearError(emailInput);

        const value = emailInput.value.trim();

        if (value === "") {
            showError(
                emailInput,
                "Please enter your email address."
            );

            return false;
        }

        if (!isValidEmail(value)) {
            showError(
                emailInput,
                "Please enter a valid email address."
            );

            return false;
        }

        return true;
    }

    function validateEmergencyContact() {

        if (!emergencyContactInput) {
            return true;
        }

        clearError(emergencyContactInput);

        const value =
            emergencyContactInput.value.trim();

        if (value === "") {
            showError(
                emergencyContactInput,
                "Please enter an emergency contact number."
            );

            return false;
        }

        if (!isValidPhone(value)) {
            showError(
                emergencyContactInput,
                "Emergency contact must contain 10 to 15 digits."
            );

            return false;
        }

        return true;
    }

    function validateAddress() {

        if (!addressInput) {
            return true;
        }

        clearError(addressInput);

        const value = addressInput.value.trim();

        if (value === "") {
            showError(
                addressInput,
                "Please enter your residential address."
            );

            return false;
        }

        if (value.length < 5) {
            showError(
                addressInput,
                "Address must contain at least 5 characters."
            );

            return false;
        }

        if (value.length > 500) {
            showError(
                addressInput,
                "Address cannot exceed 500 characters."
            );

            return false;
        }

        return true;
    }

    if (phoneInput) {

        phoneInput.addEventListener("input", function () {

            this.value = this.value.replace(/[^0-9]/g, "");

            if (this.value.length > 15) {
                this.value = this.value.slice(0, 15);
            }

            validatePhone();

        });

    }

    if (emailInput) {

        emailInput.addEventListener("input", function () {
            validateEmail();
        });

    }

    if (emergencyContactInput) {

        emergencyContactInput.addEventListener(
            "input",
            function () {

                this.value =
                    this.value.replace(/[^0-9]/g, "");

                if (this.value.length > 15) {
                    this.value = this.value.slice(0, 15);
                }

                validateEmergencyContact();

            }
        );

    }

    if (addressInput) {

        addressInput.addEventListener("input", function () {
            validateAddress();
        });

    }

    if (profileForm) {

        profileForm.addEventListener("submit", function (event) {

            const phoneValid = validatePhone();
            const emailValid = validateEmail();
            const emergencyValid =
                validateEmergencyContact();
            const addressValid = validateAddress();

            const formValid =
                phoneValid &&
                emailValid &&
                emergencyValid &&
                addressValid;

            if (!formValid) {

                event.preventDefault();

                const firstInvalidField =
                    profileForm.querySelector(".input-error");

                if (firstInvalidField) {
                    firstInvalidField.focus();
                }

            }

        });

    }


    
    //    Change Password Validation
    

    const passwordForm =
        document.getElementById("changePasswordForm");

    const currentPasswordInput =
        document.getElementById("current_password");

    const newPasswordInput =
        document.getElementById("new_password");

    const confirmPasswordInput =
        document.getElementById("confirm_password");

    function passwordMeetsRequirements(password) {

        return (
            password.length >= 8 &&
            /[A-Z]/.test(password) &&
            /[0-9]/.test(password)
        );

    }

    function validateCurrentPassword() {

        if (!currentPasswordInput) {
            return true;
        }

        clearError(currentPasswordInput);

        if (currentPasswordInput.value.trim() === "") {

            showError(
                currentPasswordInput,
                "Please enter your current password."
            );

            return false;
        }

        return true;
    }

    function validateNewPassword() {

        if (!newPasswordInput) {
            return true;
        }

        clearError(newPasswordInput);

        const value = newPasswordInput.value;

        if (value === "") {

            showError(
                newPasswordInput,
                "Please enter a new password."
            );

            return false;
        }

        if (!passwordMeetsRequirements(value)) {

            showError(
                newPasswordInput,
                "Use at least 8 characters, one uppercase letter, and one number."
            );

            return false;
        }

        if (
            currentPasswordInput &&
            currentPasswordInput.value !== "" &&
            value === currentPasswordInput.value
        ) {

            showError(
                newPasswordInput,
                "New password must be different from your current password."
            );

            return false;
        }

        return true;
    }

    function validateConfirmPassword() {

        if (!confirmPasswordInput) {
            return true;
        }

        clearError(confirmPasswordInput);

        const value = confirmPasswordInput.value;

        if (value === "") {

            showError(
                confirmPasswordInput,
                "Please confirm your new password."
            );

            return false;
        }

        if (
            newPasswordInput &&
            value !== newPasswordInput.value
        ) {

            showError(
                confirmPasswordInput,
                "Passwords do not match."
            );

            return false;
        }

        return true;
    }

    if (currentPasswordInput) {

        currentPasswordInput.addEventListener(
            "input",
            validateCurrentPassword
        );

    }

    if (newPasswordInput) {

        newPasswordInput.addEventListener(
            "input",
            function () {

                validateNewPassword();

                if (
                    confirmPasswordInput &&
                    confirmPasswordInput.value !== ""
                ) {
                    validateConfirmPassword();
                }

            }
        );

    }

    if (confirmPasswordInput) {

        confirmPasswordInput.addEventListener(
            "input",
            validateConfirmPassword
        );

    }

    if (passwordForm) {

        passwordForm.addEventListener(
            "submit",
            function (event) {

                const currentValid =
                    validateCurrentPassword();

                const newValid =
                    validateNewPassword();

                const confirmValid =
                    validateConfirmPassword();

                if (
                    !currentValid ||
                    !newValid ||
                    !confirmValid
                ) {

                    event.preventDefault();

                    const firstInvalidField =
                        passwordForm.querySelector(
                            ".input-error"
                        );

                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }

                }

            }
        );

    }


    
    //    Auto-hide Messages
    

    const dashboardMessages =
        document.querySelectorAll(".dashboard-message");

    dashboardMessages.forEach(function (message) {

        setTimeout(function () {

            message.style.opacity = "0";
            message.style.transform = "translateY(-8px)";

            setTimeout(function () {
                message.remove();
            }, 300);

        }, 5000);

    });


    
    //    Smooth Scroll to Profile Settings
    

    if (window.location.hash === "#profileSettings") {

        const profileSettings =
            document.getElementById("profileSettings");

        if (profileSettings) {

            setTimeout(function () {

                profileSettings.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }, 150);

        }

    }

});