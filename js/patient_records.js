
document.addEventListener("DOMContentLoaded", function () {

    
    //    Query Form Elements
    

    const queryForm =
        document.getElementById("patientQueryForm");

    const subjectInput =
        document.getElementById("query_subject");

    const messageInput =
        document.getElementById("query_message");

    
    //    Validation Helpers
    

    function showError(inputElement, message) {

        if (!inputElement) {
            return;
        }

        const formGroup =
            inputElement.closest(".query-form-group");

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
            inputElement.closest(".query-form-group");

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

    
    //    Subject Validation
    

    function validateSubject() {

        if (!subjectInput) {
            return true;
        }

        clearError(subjectInput);

        const value =
            subjectInput.value.trim();

        if (value === "") {

            showError(
                subjectInput,
                "Please enter a query subject."
            );

            return false;
        }

        if (value.length < 3) {

            showError(
                subjectInput,
                "Subject must contain at least 3 characters."
            );

            return false;
        }

        if (value.length > 150) {

            showError(
                subjectInput,
                "Subject cannot exceed 150 characters."
            );

            return false;
        }

        return true;

    }

    
    //    Message Validation
    

    function validateMessage() {

        if (!messageInput) {
            return true;
        }

        clearError(messageInput);

        const value =
            messageInput.value.trim();

        if (value === "") {

            showError(
                messageInput,
                "Please enter your query message."
            );

            return false;
        }

        if (value.length < 10) {

            showError(
                messageInput,
                "Message must contain at least 10 characters."
            );

            return false;
        }

        if (value.length > 2000) {

            showError(
                messageInput,
                "Message cannot exceed 2000 characters."
            );

            return false;
        }

        return true;

    }

    
    //    Live Validation
    

    if (subjectInput) {

        subjectInput.addEventListener(
            "input",
            validateSubject
        );

    }

    if (messageInput) {

        messageInput.addEventListener(
            "input",
            validateMessage
        );

    }

    //    Form Submission     

    if (queryForm) {

        queryForm.addEventListener(
            "submit",
            function (event) {

                const subjectValid =
                    validateSubject();

                const messageValid =
                    validateMessage();

                if (
                    !subjectValid ||
                    !messageValid
                ) {

                    event.preventDefault();

                    const firstInvalidField =
                        queryForm.querySelector(
                            ".input-error"
                        );

                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }

                }

            }
        );

    }

    //    Smooth Scroll for Record Tabs

    const recordTabLinks =
        document.querySelectorAll(
            ".records-tabs a"
        );

    recordTabLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function (event) {

                const targetId =
                    link.getAttribute("href");

                if (
                    !targetId ||
                    !targetId.startsWith("#")
                ) {
                    return;
                }

                const targetSection =
                    document.querySelector(targetId);

                if (!targetSection) {
                    return;
                }

                event.preventDefault();

                targetSection.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

                history.replaceState(
                    null,
                    "",
                    targetId
                );

            }
        );

    });

    
    //    Highlight Active Tab on Scroll

    const recordSections =
        document.querySelectorAll(
            ".records-section"
        );

    function updateActiveTab() {

        let activeSectionId = "";

        recordSections.forEach(function (section) {

            const sectionTop =
                section.offsetTop - 170;

            const sectionBottom =
                sectionTop +
                section.offsetHeight;

            if (
                window.scrollY >= sectionTop &&
                window.scrollY < sectionBottom
            ) {
                activeSectionId =
                    section.getAttribute("id");
            }

        });

        recordTabLinks.forEach(function (link) {

            link.classList.remove("active");

            if (
                activeSectionId &&
                link.getAttribute("href") ===
                    "#" + activeSectionId
            ) {
                link.classList.add("active");
            }

        });

    }

    window.addEventListener(
        "scroll",
        updateActiveTab
    );

    updateActiveTab();

    
    //    Auto-hide Messages

    const recordsMessages =
        document.querySelectorAll(
            ".records-message"
        );

    recordsMessages.forEach(function (message) {

        setTimeout(function () {

            message.style.opacity = "0";

            message.style.transform =
                "translateY(-8px)";

            setTimeout(function () {
                message.remove();
            }, 300);

        }, 5000);

    });

    //    Open Hashed Section on Page Load
    

    if (window.location.hash) {

        const targetSection =
            document.querySelector(
                window.location.hash
            );

        if (targetSection) {

            setTimeout(function () {

                targetSection.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }, 150);

        }

    }

});