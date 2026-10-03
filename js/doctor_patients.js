document.addEventListener(
    "DOMContentLoaded",
    function () {

        //    Main Elements

        const patientModal =
            document.getElementById(
                "patientModal"
            ) ||
            document.getElementById(
                "patientProfileModal"
            );

        const modalContent =
            document.querySelector(
                ".patient-modal-content"
            ) ||
            document.querySelector(
                ".patient-profile-modal-dialog"
            );

        const closeModalButton =
            document.getElementById(
                "closeModal"
            ) ||
            document.getElementById(
                "closePatientProfileModal"
            );

        const viewProfileButtons =
            document.querySelectorAll(
                ".view-profile-btn"
            );

        const tabButtons =
            document.querySelectorAll(
                ".tab-btn, .patient-profile-tab"
            );

        const tabContents =
            document.querySelectorAll(
                ".tab-content, .patient-profile-tab-panel"
            );

        const searchForm =
            document.getElementById(
                "doctorPatientSearchForm"
            );

        const searchInput =
            document.getElementById(
                "patient_search"
            ) ||
            document.querySelector(
                'input[name="search"]'
            );

        //    Modal Helpers

        function openPatientModal() {

            if (!patientModal) {
                return;
            }

            patientModal.classList.add(
                "open"
            );

            patientModal.style.display =
                "flex";

            patientModal.setAttribute(
                "aria-hidden",
                "false"
            );

            document.body.classList.add(
                "modal-open"
            );

        }

        function closePatientModal() {

            if (!patientModal) {
                return;
            }

            patientModal.classList.remove(
                "open"
            );

            patientModal.style.display =
                "none";

            patientModal.setAttribute(
                "aria-hidden",
                "true"
            );

            document.body.classList.remove(
                "modal-open"
            );

            /*Remove patient_id from the address bar after closing the modal.
            This prevents the modal from reopening when the page is refreshed.*/

            const currentUrl =
                new URL(
                    window.location.href
                );

            if (
                currentUrl.searchParams.has(
                    "patient_id"
                )
            ) {

                currentUrl.searchParams.delete(
                    "patient_id"
                );

                window.history.replaceState(
                    {},
                    "",
                    currentUrl.pathname +
                    (
                        currentUrl.search
                            ? currentUrl.search
                            : ""
                    )
                );

            }

        }

        //    Close Modal Button

        if (closeModalButton) {

            closeModalButton.addEventListener(
                "click",
                function () {

                    closePatientModal();

                }
            );

        }

        //    Close Modal by Clicking Backdrop

        if (patientModal) {

            patientModal.addEventListener(
                "click",
                function (event) {

                    if (
                        event.target ===
                        patientModal
                    ) {
                        closePatientModal();
                    }

                }
            );

        }

        // Prevent clicks inside the modal dialog from closing the modal.

        if (modalContent) {

            modalContent.addEventListener(
                "click",
                function (event) {

                    event.stopPropagation();

                }
            );

        }

        //    Close Modal with Escape Key

        document.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Escape" &&
                    patientModal &&
                    patientModal.classList.contains(
                        "open"
                    )
                ) {
                    closePatientModal();
                }

            }
        );

        //    Client-side Profile Buttons


        viewProfileButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        const modalName =
                            document.getElementById(
                                "modalName"
                            );

                        const modalId =
                            document.getElementById(
                                "modalId"
                            );

                        const modalGender =
                            document.getElementById(
                                "modalGender"
                            );

                        const modalDob =
                            document.getElementById(
                                "modalDob"
                            );

                        const modalBlood =
                            document.getElementById(
                                "modalBlood"
                            );

                        const patientId =
                            button.dataset.id || "";

                        const patientName =
                            button.dataset.name || "";

                        const patientGender =
                            button.dataset.gender || "";

                        const patientDob =
                            button.dataset.dob || "";

                        const patientBlood =
                            button.dataset.blood || "";

                        if (modalName) {
                            modalName.textContent =
                                patientName;
                        }

                        if (modalId) {
                            modalId.textContent =
                                patientId
                                    ? "BC-" +
                                      String(
                                          patientId
                                      ).padStart(
                                          5,
                                          "0"
                                      )
                                    : "";
                        }

                        if (modalGender) {
                            modalGender.textContent =
                                patientGender;
                        }

                        if (modalDob) {
                            modalDob.textContent =
                                patientDob;
                        }

                        if (modalBlood) {
                            modalBlood.textContent =
                                patientBlood;
                        }

                        const medicalPatientId =
                            document.getElementById(
                                "medicalPatientId"
                            );

                        if (
                            medicalPatientId &&
                            patientId
                        ) {
                            medicalPatientId.value =
                                patientId;
                        }

                        openPatientModal();

                    }
                );

            }
        );

        //    Automatically Open Server-loaded Modal

        if (
            patientModal &&
            (
                patientModal.classList.contains(
                    "open"
                ) ||
                new URLSearchParams(
                    window.location.search
                ).has("patient_id")
            )
        ) {
            openPatientModal();
        }

        //    Tab Helpers

        function showTab(
            selectedTabId,
            selectedButton
        ) {

            tabContents.forEach(
                function (content) {

                    content.classList.remove(
                        "active"
                    );

                }
            );

            tabButtons.forEach(
                function (button) {

                    button.classList.remove(
                        "active"
                    );

                    button.setAttribute(
                        "aria-selected",
                        "false"
                    );

                }
            );

            const selectedTab =
                document.getElementById(
                    selectedTabId
                );

            if (selectedTab) {

                selectedTab.classList.add(
                    "active"
                );

            }

            if (selectedButton) {

                selectedButton.classList.add(
                    "active"
                );

                selectedButton.setAttribute(
                    "aria-selected",
                    "true"
                );

            }

            // Store the active tab so it can be restored after a form redirect.

            try {

                sessionStorage.setItem(
                    "doctorPatientActiveTab",
                    selectedTabId
                );

            } catch (error) {

                // Ignore storage errors.

            }

        }

        //    Tab Switching

        tabButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        const selectedTabId =
                            button.getAttribute(
                                "data-tab"
                            );

                        if (!selectedTabId) {
                            return;
                        }

                        showTab(
                            selectedTabId,
                            button
                        );

                    }
                );

            }
        );

        //    Restore Last Active Tab

        let storedTabId = "";

        try {

            storedTabId =
                sessionStorage.getItem(
                    "doctorPatientActiveTab"
                ) || "";

        } catch (error) {

            storedTabId = "";

        }

        if (storedTabId) {

            const storedButton =
                Array.from(
                    tabButtons
                ).find(
                    function (button) {

                        return (
                            button.getAttribute(
                                "data-tab"
                            ) === storedTabId
                        );

                    }
                );

            const storedTab =
                document.getElementById(
                    storedTabId
                );

            if (
                storedButton &&
                storedTab
            ) {

                showTab(
                    storedTabId,
                    storedButton
                );

            }

        }

        //    Search Validation Helpers

        function showSearchError(
            message
        ) {

            if (!searchInput) {
                return;
            }

            searchInput.classList.add(
                "input-error"
            );

            let errorElement =
                searchForm
                    ? searchForm.querySelector(
                        ".search-error"
                    )
                    : null;

            if (
                !errorElement &&
                searchForm
            ) {

                errorElement =
                    document.createElement(
                        "small"
                    );

                errorElement.className =
                    "search-error";

                errorElement.style.display =
                    "block";

                errorElement.style.width =
                    "100%";

                errorElement.style.color =
                    "#dc3545";

                errorElement.style.fontSize =
                    "11px";

                errorElement.style.marginTop =
                    "5px";

                searchForm.appendChild(
                    errorElement
                );

            }

            if (errorElement) {
                errorElement.textContent =
                    message;
            }

        }

        function clearSearchError() {

            if (!searchInput) {
                return;
            }

            searchInput.classList.remove(
                "input-error"
            );

            const errorElement =
                searchForm
                    ? searchForm.querySelector(
                        ".search-error"
                    )
                    : null;

            if (errorElement) {
                errorElement.textContent = "";
            }

        }

        function validatePatientSearch() {

            if (!searchInput) {
                return true;
            }

            clearSearchError();

            const searchValue =
                searchInput.value.trim();

            if (searchValue === "") {

                showSearchError(
                    "Please enter a patient ID or patient name."
                );

                return false;
            }

            if (searchValue.length < 1) {

                showSearchError(
                    "Please enter a valid search value."
                );

                return false;
            }

            if (searchValue.length > 100) {

                showSearchError(
                    "Search value cannot exceed 100 characters."
                );

                return false;
            }

            return true;

        }

        //    Live Search Validation

        if (searchInput) {

            searchInput.addEventListener(
                "input",
                function () {

                    clearSearchError();

                }
            );

        }

        //    Search Form Submission

        if (searchForm) {

            searchForm.addEventListener(
                "submit",
                function (event) {

                    if (
                        !validatePatientSearch()
                    ) {

                        event.preventDefault();

                        if (searchInput) {
                            searchInput.focus();
                        }

                    }

                }
            );

        }

    }
);

document.addEventListener(
    "DOMContentLoaded",
    function () {

        //    Shared Validation Helpers

        function getFormGroup(
            inputElement
        ) {

            if (!inputElement) {
                return null;
            }

            return inputElement.closest(
                ".form-group, .doctor-patient-form-group"
            );

        }

        function showFieldError(
            inputElement,
            message
        ) {

            if (!inputElement) {
                return;
            }

            const formGroup =
                getFormGroup(
                    inputElement
                );

            if (!formGroup) {
                return;
            }

            inputElement.classList.add(
                "input-error"
            );

            let errorElement =
                formGroup.querySelector(
                    ".field-error"
                );

            if (!errorElement) {

                errorElement =
                    document.createElement(
                        "small"
                    );

                errorElement.className =
                    "field-error";

                formGroup.appendChild(
                    errorElement
                );

            }

            errorElement.textContent =
                message;

        }

        function clearFieldError(
            inputElement
        ) {

            if (!inputElement) {
                return;
            }

            const formGroup =
                getFormGroup(
                    inputElement
                );

            inputElement.classList.remove(
                "input-error"
            );

            if (!formGroup) {
                return;
            }

            const errorElement =
                formGroup.querySelector(
                    ".field-error"
                );

            if (errorElement) {
                errorElement.textContent = "";
            }

        }

        function focusFirstInvalidField(
            formElement
        ) {

            if (!formElement) {
                return;
            }

            const firstInvalidField =
                formElement.querySelector(
                    ".input-error"
                );

            if (firstInvalidField) {
                firstInvalidField.focus();
            }

        }

        //   Medical Record Form

        const medicalRecordForm =
            document.getElementById(
                "medicalRecordForm"
            ) ||
            document.querySelector(
                'form input[name="action"][value="add_medical_record"]'
            )?.closest("form");

        const appointmentSelect =
            medicalRecordForm
                ? medicalRecordForm.querySelector(
                    '[name="appointment_id"]'
                )
                : null;

        const diagnosisInput =
            medicalRecordForm
                ? medicalRecordForm.querySelector(
                    '[name="diagnosis"]'
                )
                : null;

        const treatmentInput =
            medicalRecordForm
                ? medicalRecordForm.querySelector(
                    '[name="treatment"]'
                )
                : null;

        const notesInput =
            medicalRecordForm
                ? medicalRecordForm.querySelector(
                    '[name="notes"]'
                )
                : null;

        function validateAppointmentSelection() {

            if (!appointmentSelect) {
                return true;
            }

            clearFieldError(
                appointmentSelect
            );

            if (
                appointmentSelect.value === ""
            ) {

                showFieldError(
                    appointmentSelect,
                    "Please select a completed appointment."
                );

                return false;
            }

            return true;

        }

        function validateDiagnosis() {

            if (!diagnosisInput) {
                return true;
            }

            clearFieldError(
                diagnosisInput
            );

            const value =
                diagnosisInput.value.trim();

            if (value === "") {

                showFieldError(
                    diagnosisInput,
                    "Please enter the diagnosis."
                );

                return false;
            }

            if (value.length < 3) {

                showFieldError(
                    diagnosisInput,
                    "Diagnosis must contain at least 3 characters."
                );

                return false;
            }

            if (value.length > 1000) {

                showFieldError(
                    diagnosisInput,
                    "Diagnosis cannot exceed 1000 characters."
                );

                return false;
            }

            return true;

        }

        function validateTreatment() {

            if (!treatmentInput) {
                return true;
            }

            clearFieldError(
                treatmentInput
            );

            const value =
                treatmentInput.value.trim();

            if (value === "") {

                showFieldError(
                    treatmentInput,
                    "Please enter the treatment."
                );

                return false;
            }

            if (value.length < 3) {

                showFieldError(
                    treatmentInput,
                    "Treatment must contain at least 3 characters."
                );

                return false;
            }

            if (value.length > 1000) {

                showFieldError(
                    treatmentInput,
                    "Treatment cannot exceed 1000 characters."
                );

                return false;
            }

            return true;

        }

        function validateNotes() {

            if (!notesInput) {
                return true;
            }

            clearFieldError(
                notesInput
            );

            const value =
                notesInput.value.trim();

            if (value.length > 2000) {

                showFieldError(
                    notesInput,
                    "Notes cannot exceed 2000 characters."
                );

                return false;
            }

            return true;

        }

        if (appointmentSelect) {

            appointmentSelect.addEventListener(
                "change",
                validateAppointmentSelection
            );

        }

        if (diagnosisInput) {

            diagnosisInput.addEventListener(
                "input",
                validateDiagnosis
            );

        }

        if (treatmentInput) {

            treatmentInput.addEventListener(
                "input",
                validateTreatment
            );

        }

        if (notesInput) {

            notesInput.addEventListener(
                "input",
                validateNotes
            );

        }

        if (medicalRecordForm) {

            medicalRecordForm.addEventListener(
                "submit",
                function (event) {

                    const appointmentValid =
                        validateAppointmentSelection();

                    const diagnosisValid =
                        validateDiagnosis();

                    const treatmentValid =
                        validateTreatment();

                    const notesValid =
                        validateNotes();

                    if (
                        !appointmentValid ||
                        !diagnosisValid ||
                        !treatmentValid ||
                        !notesValid
                    ) {

                        event.preventDefault();

                        focusFirstInvalidField(
                            medicalRecordForm
                        );

                    }

                }
            );

        }

        //    Prescription Form

        const prescriptionForm =
            document.getElementById(
                "prescriptionForm"
            ) ||
            document.querySelector(
                'form input[name="action"][value="add_prescription"]'
            )?.closest("form");

        const recordSelect =
            prescriptionForm
                ? prescriptionForm.querySelector(
                    '[name="record_id"]'
                )
                : null;

        const medicineInput =
            prescriptionForm
                ? prescriptionForm.querySelector(
                    '[name="medicine"]'
                )
                : null;

        const dosageInput =
            prescriptionForm
                ? prescriptionForm.querySelector(
                    '[name="dosage"]'
                )
                : null;

        const durationInput =
            prescriptionForm
                ? prescriptionForm.querySelector(
                    '[name="duration"]'
                )
                : null;

        const instructionsInput =
            prescriptionForm
                ? prescriptionForm.querySelector(
                    '[name="instructions"]'
                )
                : null;

        function validateRecordSelection() {

            if (!recordSelect) {
                return true;
            }

            clearFieldError(
                recordSelect
            );

            if (recordSelect.value === "") {

                showFieldError(
                    recordSelect,
                    "Please select a medical record."
                );

                return false;
            }

            return true;

        }

        function validateMedicine() {

            if (!medicineInput) {
                return true;
            }

            clearFieldError(
                medicineInput
            );

            const value =
                medicineInput.value.trim();

            if (value === "") {

                showFieldError(
                    medicineInput,
                    "Please enter the medicine name."
                );

                return false;
            }

            if (value.length < 2) {

                showFieldError(
                    medicineInput,
                    "Medicine name must contain at least 2 characters."
                );

                return false;
            }

            if (value.length > 150) {

                showFieldError(
                    medicineInput,
                    "Medicine name cannot exceed 150 characters."
                );

                return false;
            }

            return true;

        }

        function validateDosage() {

            if (!dosageInput) {
                return true;
            }

            clearFieldError(
                dosageInput
            );

            const value =
                dosageInput.value.trim();

            if (value === "") {

                showFieldError(
                    dosageInput,
                    "Please enter the dosage."
                );

                return false;
            }

            if (value.length < 2) {

                showFieldError(
                    dosageInput,
                    "Dosage must contain at least 2 characters."
                );

                return false;
            }

            if (value.length > 100) {

                showFieldError(
                    dosageInput,
                    "Dosage cannot exceed 100 characters."
                );

                return false;
            }

            return true;

        }

        function validateDuration() {

            if (!durationInput) {
                return true;
            }

            clearFieldError(
                durationInput
            );

            const value =
                durationInput.value.trim();

            if (value === "") {

                showFieldError(
                    durationInput,
                    "Please enter the duration."
                );

                return false;
            }

            if (value.length < 2) {

                showFieldError(
                    durationInput,
                    "Duration must contain at least 2 characters."
                );

                return false;
            }

            if (value.length > 100) {

                showFieldError(
                    durationInput,
                    "Duration cannot exceed 100 characters."
                );

                return false;
            }

            return true;

        }

        function validateInstructions() {

            if (!instructionsInput) {
                return true;
            }

            clearFieldError(
                instructionsInput
            );

            const value =
                instructionsInput.value.trim();

            if (value.length > 500) {

                showFieldError(
                    instructionsInput,
                    "Instructions cannot exceed 500 characters."
                );

                return false;
            }

            return true;

        }

        if (recordSelect) {

            recordSelect.addEventListener(
                "change",
                validateRecordSelection
            );

        }

        if (medicineInput) {

            medicineInput.addEventListener(
                "input",
                validateMedicine
            );

        }

        if (dosageInput) {

            dosageInput.addEventListener(
                "input",
                validateDosage
            );

        }

        if (durationInput) {

            durationInput.addEventListener(
                "input",
                validateDuration
            );

        }

        if (instructionsInput) {

            instructionsInput.addEventListener(
                "input",
                validateInstructions
            );

        }

        if (prescriptionForm) {

            prescriptionForm.addEventListener(
                "submit",
                function (event) {

                    const recordValid =
                        validateRecordSelection();

                    const medicineValid =
                        validateMedicine();

                    const dosageValid =
                        validateDosage();

                    const durationValid =
                        validateDuration();

                    const instructionsValid =
                        validateInstructions();

                    if (
                        !recordValid ||
                        !medicineValid ||
                        !dosageValid ||
                        !durationValid ||
                        !instructionsValid
                    ) {

                        event.preventDefault();

                        focusFirstInvalidField(
                            prescriptionForm
                        );

                    }

                }
            );

        }

        //    Patient Query Reply Forms

        const queryReplyForms =
            document.querySelectorAll(
                ".query-reply-form, " +
                'form input[name="action"]' +
                '[value="reply_patient_query"]'
            );

        queryReplyForms.forEach(
            function (element) {

                const form =
                    element.tagName === "FORM"
                        ? element
                        : element.closest("form");

                if (!form) {
                    return;
                }

                const replyInput =
                    form.querySelector(
                        '[name="reply"]'
                    );

                function validateReply() {

                    if (!replyInput) {
                        return true;
                    }

                    clearFieldError(
                        replyInput
                    );

                    const value =
                        replyInput.value.trim();

                    if (value === "") {

                        showFieldError(
                            replyInput,
                            "Please enter a reply."
                        );

                        return false;
                    }

                    if (value.length < 5) {

                        showFieldError(
                            replyInput,
                            "Reply must contain at least 5 characters."
                        );

                        return false;
                    }

                    if (value.length > 2000) {

                        showFieldError(
                            replyInput,
                            "Reply cannot exceed 2000 characters."
                        );

                        return false;
                    }

                    return true;

                }

                if (replyInput) {

                    replyInput.addEventListener(
                        "input",
                        validateReply
                    );

                }

                form.addEventListener(
                    "submit",
                    function (event) {

                        if (!validateReply()) {

                            event.preventDefault();

                            focusFirstInvalidField(
                                form
                            );

                        }

                    }
                );

            }
        );

    }
);

document.addEventListener(
    "DOMContentLoaded",
    function () {

        //    Laboratory Report Form

        const labForm =
            document.getElementById(
                "labReportForm"
            );

        if (labForm) {

            const testName =
                labForm.querySelector(
                    '[name="test_name"]'
                );

            const reportDate =
                labForm.querySelector(
                    '[name="report_date"]'
                );

            const reportFile =
                labForm.querySelector(
                    '[name="report_file"]'
                );

            function showError(
                input,
                message
            ) {

                const group =
                    input.closest(
                        ".form-group, .doctor-patient-form-group"
                    );

                if (!group) return;

                input.classList.add(
                    "input-error"
                );

                let error =
                    group.querySelector(
                        ".field-error"
                    );

                if (!error) {

                    error =
                        document.createElement(
                            "small"
                        );

                    error.className =
                        "field-error";

                    group.appendChild(
                        error
                    );

                }

                error.textContent =
                    message;

            }

            function clearError(
                input
            ) {

                const group =
                    input.closest(
                        ".form-group, .doctor-patient-form-group"
                    );

                input.classList.remove(
                    "input-error"
                );

                if (!group) return;

                const error =
                    group.querySelector(
                        ".field-error"
                    );

                if (error) {

                    error.textContent =
                        "";

                }

            }

            labForm.addEventListener(
                "submit",
                function (event) {

                    let valid = true;

                    clearError(testName);
                    clearError(reportDate);
                    clearError(reportFile);

                    if (
                        testName.value.trim() === ""
                    ) {

                        valid = false;

                        showError(
                            testName,
                            "Please enter the test name."
                        );

                    }

                    if (
                        reportDate.value === ""
                    ) {

                        valid = false;

                        showError(
                            reportDate,
                            "Please select the report date."
                        );

                    }

                    if (
                        reportFile.files.length === 0
                    ) {

                        valid = false;

                        showError(
                            reportFile,
                            "Please select a PDF report."
                        );

                    } else {

                        const file =
                            reportFile.files[0];

                        if (
                            file.type !==
                            "application/pdf"
                        ) {

                            valid = false;

                            showError(
                                reportFile,
                                "Only PDF files are allowed."
                            );

                        }

                        const maxSize =
                            5 * 1024 * 1024;

                        if (
                            file.size > maxSize
                        ) {

                            valid = false;

                            showError(
                                reportFile,
                                "Maximum file size is 5 MB."
                            );

                        }

                    }

                    if (!valid) {

                        event.preventDefault();

                    }

                }
            );

            [
                testName,
                reportDate,
                reportFile
            ].forEach(function (input) {

                if (!input) return;

                input.addEventListener(
                    "input",
                    function () {

                        clearError(
                            input
                        );

                    }
                );

                input.addEventListener(
                    "change",
                    function () {

                        clearError(
                            input
                        );

                    }
                );

            });

        }

        //    Prevent Double Submission

        document
            .querySelectorAll("form")
            .forEach(function (form) {

                form.addEventListener(
                    "submit",
                    function () {

                        const submitButton =
                            form.querySelector(
                                'button[type="submit"]'
                            );

                        if (!submitButton)
                            return;

                        submitButton.disabled = true;

                        const originalHTML =
                            submitButton.innerHTML;

                        submitButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

                        setTimeout(function () {

                            submitButton.disabled = false;

                            submitButton.innerHTML =
                                originalHTML;

                        }, 8000);

                    }
                );

            });

        //    Auto Hide Messages

        const messages =
            document.querySelectorAll(

                ".doctor-patient-message," +

                ".success-message," +

                ".error-message"

            );

        messages.forEach(function (message) {

            setTimeout(function () {

                message.style.opacity = "0";

                message.style.transform =
                    "translateY(-10px)";

                setTimeout(function () {

                    message.remove();

                }, 400);

            }, 5000);

        });

        //    Remember Active Tab

        document
            .querySelectorAll(
                ".tab-btn, .patient-profile-tab"
            )
            .forEach(function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        const tab =
                            button.dataset.tab;

                        if (tab) {

                            sessionStorage.setItem(
                                "doctorPatientTab",
                                tab
                            );

                        }

                    }
                );

            });

        const lastTab =
            sessionStorage.getItem(
                "doctorPatientTab"
            );

        if (lastTab) {

            const button =
                document.querySelector(

                    '[data-tab="' +

                    lastTab +

                    '"]'

                );

            if (button) {

                button.click();

            }

        }

        //    Highlight Required Fields

        document
            .querySelectorAll(
                "input[required], textarea[required], select[required]"
            )
            .forEach(function (field) {

                field.addEventListener(
                    "blur",
                    function () {

                        if (
                            field.value.trim() === ""
                        ) {

                            field.classList.add(
                                "input-error"
                            );

                        } else {

                            field.classList.remove(
                                "input-error"
                            );

                        }

                    }
                );

            });


        //    Smooth Scroll to Top of Modal

        document
            .querySelectorAll(
                ".patient-profile-tab"
            )
            .forEach(function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        const modal =
                            document.querySelector(
                                ".patient-profile-modal-dialog"
                            );

                        if (modal) {

                            modal.scrollTo({

                                top: 0,

                                behavior: "smooth"

                            });

                        }

                    }
                );

            });

    }
);