document.addEventListener("DOMContentLoaded", function () {

    const appointmentForm =
        document.getElementById("appointmentForm");

    const departmentSelect =
        document.getElementById("department_id");

    const doctorSelect =
        document.getElementById("doctor_id");

    const appointmentDateInput =
        document.getElementById("appointment_date");

    const appointmentTimeInput =
        document.getElementById("appointment_time");

    const selectedDoctorCard =
        document.getElementById("selectedDoctorCard");

    const doctorSpecialization =
        document.getElementById("doctorSpecialization");

    const doctorAvailability =
        document.getElementById("doctorAvailability");

    const resetButton =
        document.getElementById("resetAppointmentButton");

    
    //    Validation Helpers

    function showError(inputElement, message) {

        if (!inputElement) {
            return;
        }

        const formGroup =
            inputElement.closest(".appointment-form-group");

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
            inputElement.closest(".appointment-form-group");

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

    
    //    Set Minimum Date
   

    function getTodayDate() {

        const today = new Date();

        const year = today.getFullYear();

        const month =
            String(today.getMonth() + 1).padStart(2, "0");

        const day =
            String(today.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;

    }

    if (appointmentDateInput) {
        appointmentDateInput.min = getTodayDate();
    }

    
    //    Department and Doctor Filtering
    

    function resetDoctorSelection() {

        if (!doctorSelect) {
            return;
        }

        doctorSelect.value = "";
        doctorSelect.disabled = true;

        if (selectedDoctorCard) {
            selectedDoctorCard.hidden = true;
        }

        if (doctorSpecialization) {
            doctorSpecialization.textContent = "—";
        }

        if (doctorQualification) {
            doctorQualification.textContent = "—";
        }

        if (doctorAvailability) {
            doctorAvailability.textContent = "—";
        }

    }

    function filterDoctorsByDepartment() {

        if (!departmentSelect || !doctorSelect) {
            return;
        }

        clearError(departmentSelect);
        clearError(doctorSelect);

        const selectedDepartment =
            departmentSelect.value;

        doctorSelect.value = "";

        const doctorOptions =
            doctorSelect.querySelectorAll(
                "option[data-department]"
            );

        let matchingDoctorCount = 0;

        doctorOptions.forEach(function (option) {

            const doctorDepartment =
                option.getAttribute("data-department");

            if (
                selectedDepartment !== "" &&
                doctorDepartment === selectedDepartment
            ) {
                option.hidden = false;
                option.disabled = false;
                matchingDoctorCount++;
            } else {
                option.hidden = true;
                option.disabled = true;
            }

        });

        const defaultOption =
            doctorSelect.querySelector(
                "option:not([data-department])"
            );

        if (selectedDepartment === "") {

            doctorSelect.disabled = true;

            if (defaultOption) {
                defaultOption.textContent =
                    "Select a department first";
            }

        } else if (matchingDoctorCount === 0) {

            doctorSelect.disabled = true;

            if (defaultOption) {
                defaultOption.textContent =
                    "No doctors available";
            }

        } else {

            doctorSelect.disabled = false;

            if (defaultOption) {
                defaultOption.textContent =
                    "Select Doctor";
            }

        }

        if (selectedDoctorCard) {
            selectedDoctorCard.hidden = true;
        }

    }

    if (departmentSelect) {

        departmentSelect.addEventListener(
            "change",
            filterDoctorsByDepartment
        );

    }

    
    //    Selected Doctor Details
    

    function updateSelectedDoctorInformation() {

        if (!doctorSelect) {
            return;
        }

        clearError(doctorSelect);

        const selectedOption =
            doctorSelect.options[
                doctorSelect.selectedIndex
            ];

        if (
            !selectedOption ||
            !selectedOption.value
        ) {
            if (selectedDoctorCard) {
                selectedDoctorCard.hidden = true;
            }

            return;
        }

        if (doctorSpecialization) {
            doctorSpecialization.textContent =
                selectedOption.getAttribute(
                    "data-specialization"
                ) || "Not available";
        }

        if (doctorQualification) {
            doctorQualification.textContent =
                selectedOption.getAttribute(
                    "data-qualification"
                ) || "Not available";
        }

        if (doctorAvailability) {
            doctorAvailability.textContent =
                selectedOption.getAttribute(
                    "data-availability"
                ) || "Not available";
        }

        if (selectedDoctorCard) {
            selectedDoctorCard.hidden = false;
        }

    }

    if (doctorSelect) {

        doctorSelect.addEventListener(
            "change",
            updateSelectedDoctorInformation
        );

    }

    
    //    Field Validation
    

    function validateDepartment() {

        if (!departmentSelect) {
            return true;
        }

        clearError(departmentSelect);

        if (departmentSelect.value === "") {

            showError(
                departmentSelect,
                "Please select a department."
            );

            return false;
        }

        return true;

    }

    function validateDoctor() {

        if (!doctorSelect) {
            return true;
        }

        clearError(doctorSelect);

        if (
            doctorSelect.disabled ||
            doctorSelect.value === ""
        ) {

            showError(
                doctorSelect,
                "Please select a doctor."
            );

            return false;
        }

        return true;

    }

    function validateAppointmentDate() {

        if (!appointmentDateInput) {
            return true;
        }

        clearError(appointmentDateInput);

        const selectedDate =
            appointmentDateInput.value;

        if (selectedDate === "") {

            showError(
                appointmentDateInput,
                "Please select an appointment date."
            );

            return false;
        }

        if (selectedDate < getTodayDate()) {

            showError(
                appointmentDateInput,
                "You cannot select a past date."
            );

            return false;
        }

        return true;

    }

    function validateAppointmentTime() {

        if (!appointmentTimeInput) {
            return true;
        }

        clearError(appointmentTimeInput);

        const selectedTime =
            appointmentTimeInput.value;

        if (selectedTime === "") {

            showError(
                appointmentTimeInput,
                "Please select an appointment time."
            );

            return false;
        }

        if (
            selectedTime < "08:00" ||
            selectedTime > "20:00"
        ) {

            showError(
                appointmentTimeInput,
                "Appointment time must be between 8:00 AM and 8:00 PM."
            );

            return false;
        }

        if (
            appointmentDateInput &&
            appointmentDateInput.value === getTodayDate()
        ) {

            const now = new Date();

            const currentHour =
                String(now.getHours()).padStart(2, "0");

            const currentMinute =
                String(now.getMinutes()).padStart(2, "0");

            const currentTime =
                `${currentHour}:${currentMinute}`;

            if (selectedTime <= currentTime) {

                showError(
                    appointmentTimeInput,
                    "Please select a future time."
                );

                return false;
            }

        }

        return true;

    }

    
    //    Live Validation
    

    if (appointmentDateInput) {

        appointmentDateInput.addEventListener(
            "change",
            validateAppointmentDate
        );

    }

    if (appointmentTimeInput) {

        appointmentTimeInput.addEventListener(
            "change",
            validateAppointmentTime
        );

    }

    
    //    Booking Form Submission
    

    if (appointmentForm) {

        appointmentForm.addEventListener(
            "submit",
            function (event) {

                const departmentValid =
                    validateDepartment();

                const doctorValid =
                    validateDoctor();

                const dateValid =
                    validateAppointmentDate();

                const timeValid =
                    validateAppointmentTime();

                const formValid =
                    departmentValid &&
                    doctorValid &&
                    dateValid &&
                    timeValid;

                if (!formValid) {

                    event.preventDefault();

                    const firstInvalidField =
                        appointmentForm.querySelector(
                            ".input-error"
                        );

                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }

                }

            }
        );

    }

    
    //    Reset Form
   

    if (resetButton) {

        resetButton.addEventListener(
            "click",
            function () {

                setTimeout(function () {

                    resetDoctorSelection();

                    if (departmentSelect) {
                        departmentSelect.value = "";
                    }

                    if (appointmentDateInput) {
                        appointmentDateInput.value = "";
                    }

                    if (appointmentTimeInput) {
                        appointmentTimeInput.value = "";
                    }

                    const errorFields =
                        document.querySelectorAll(
                            ".appointment-form .input-error"
                        );

                    const errorMessages =
                        document.querySelectorAll(
                            ".appointment-form .field-error"
                        );

                    errorFields.forEach(function (field) {
                        field.classList.remove("input-error");
                    });

                    errorMessages.forEach(function (message) {
                        message.textContent = "";
                    });

                }, 0);

            }
        );

    }

    
    //    Cancel Appointment Confirmation
    

    const cancelForms =
        document.querySelectorAll(
            ".cancel-appointment-form"
        );

    cancelForms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function (event) {

                const confirmed = window.confirm(
                    "Are you sure you want to cancel this appointment?"
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            }
        );

    });

    
    //    Auto-hide Messages
    

    const appointmentMessages =
        document.querySelectorAll(
            ".appointment-message"
        );

    appointmentMessages.forEach(function (message) {

        setTimeout(function () {

            message.style.opacity = "0";
            message.style.transform =
                "translateY(-8px)";

            setTimeout(function () {
                message.remove();
            }, 300);

        }, 5000);

    });

    
    //    Initial State
    

    resetDoctorSelection();

});