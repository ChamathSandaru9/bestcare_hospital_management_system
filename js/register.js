
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registerForm");

    form.addEventListener("submit", function (e) {

        // Get Form Values
        const firstName = document.querySelector("[name='first_name']").value.trim();
        const lastName = document.querySelector("[name='last_name']").value.trim();
        const gender = document.querySelector("[name='gender']").value;
        const dob = document.querySelector("[name='dob']").value;
        const bloodGroup = document.querySelector("[name='blood_group']").value;
        const phone = document.querySelector("[name='phone']").value.trim();
        const email = document.querySelector("[name='email']").value.trim();
        const emergencyContact = document.querySelector("[name='emergency_contact']").value.trim();
        const address = document.querySelector("[name='address']").value.trim();
        const username = document.querySelector("[name='username']").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;

        // Regular Expressions
        const nameRegex = /^[A-Za-z ]+$/;
        const phoneRegex = /^[0-9]{10}$/;
        const usernameRegex = /^[A-Za-z0-9_]{4,20}$/;

        // At least 8 chars, uppercase, lowercase, number & special character
        const passwordRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.#_-])[A-Za-z\d@$!%*?&.#_-]{8,}$/;

        // First Name
        if (!nameRegex.test(firstName)) {
            alert("First name should contain only letters.");
            e.preventDefault();
            return;
        }

        // Last Name
        if (!nameRegex.test(lastName)) {
            alert("Last name should contain only letters.");
            e.preventDefault();
            return;
        }

        // Gender
        if (gender === "") {
            alert("Please select your gender.");
            e.preventDefault();
            return;
        }

        // Date of Birth
        if (dob === "") {
            alert("Please select your date of birth.");
            e.preventDefault();
            return;
        }

        const birthDate = new Date(dob);
        const today = new Date();

        if (birthDate > today) {
            alert("Date of birth cannot be in the future.");
            e.preventDefault();
            return;
        }

        // Blood Group
        if (bloodGroup === "") {
            alert("Please select your blood group.");
            e.preventDefault();
            return;
        }

        // Phone Number
        if (!phoneRegex.test(phone)) {
            alert("Phone number must contain exactly 10 digits.");
            e.preventDefault();
            return;
        }

        // Email
        if (!email.includes("@")) {
            alert("Please enter a valid email address.");
            e.preventDefault();
            return;
        }

        // Emergency Contact
        if (!phoneRegex.test(emergencyContact)) {
            alert("Emergency contact must contain exactly 10 digits.");
            e.preventDefault();
            return;
        }

        // Address
        if (address.length < 10) {
            alert("Please enter a complete address.");
            e.preventDefault();
            return;
        }

        // Username
        if (!usernameRegex.test(username)) {
            alert("Username must be 4-20 characters and can contain letters, numbers and underscores.");
            e.preventDefault();
            return;
        }

        // Password
        if (!passwordRegex.test(password)) {
            alert(
                "Password must contain:\n\n" +
                "• At least 8 characters\n" +
                "• One uppercase letter\n" +
                "• One lowercase letter\n" +
                "• One number\n" +
                "• One special character"
            );
            e.preventDefault();
            return;
        }

        // Confirm Password
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            e.preventDefault();
            return;
        }

    });

});