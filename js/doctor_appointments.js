document.addEventListener("DOMContentLoaded", function () {
       
    //    Complete Appointment Confirmation

    const completeForms =
        document.querySelectorAll(
            ".complete-appointment-form"
        );

    completeForms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function (event) {

                const confirmed = window.confirm(
                    "Are you sure you want to mark this appointment as completed?"
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            }
        );

    });

    
    //    Cancel Appointment Confirmation

    const cancelForms =
        document.querySelectorAll(
            ".cancel-doctor-appointment-form"
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

    
    //    Approve Appointment Confirmation

    const approveForms =
        document.querySelectorAll(
            'form input[name="action"][value="approve_appointment"]'
        );

    approveForms.forEach(function (actionInput) {

        const form = actionInput.closest("form");

        if (!form) {
            return;
        }

        form.addEventListener(
            "submit",
            function (event) {

                const confirmed = window.confirm(
                    "Approve this appointment?"
                );

                if (!confirmed) {
                    event.preventDefault();
                }

            }
        );

    });

    
    //    Auto-hide Messages

    const messages =
        document.querySelectorAll(
            ".doctor-appointment-message"
        );

    messages.forEach(function (message) {

        setTimeout(function () {

            message.style.opacity = "0";
            message.style.transform =
                "translateY(-8px)";

            setTimeout(function () {
                message.remove();
            }, 300);

        }, 5000);

    });

    //    Disable Submitted Buttons

    const actionForms =
        document.querySelectorAll(
            ".doctor-appointment-action-form"
        );

    actionForms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function (event) {

                if (event.defaultPrevented) {
                    return;
                }

                const submitButton =
                    form.querySelector(
                        'button[type="submit"]'
                    );

                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;

                const originalContent =
                    submitButton.innerHTML;

                submitButton.dataset.originalContent =
                    originalContent;

                submitButton.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i> Processing';

            }
        );

    });

    /*Table Search
       Optional: Works only if a search input is added later with this ID. */

    const searchInput =
        document.getElementById(
            "doctorAppointmentSearch"
        );

    const appointmentRows =
        document.querySelectorAll(
            ".doctor-appointment-table tbody tr"
        );

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            function () {

                const searchValue =
                    searchInput.value
                        .trim()
                        .toLowerCase();

                appointmentRows.forEach(
                    function (row) {

                        const rowText =
                            row.textContent
                                .toLowerCase();

                        row.style.display =
                            rowText.includes(
                                searchValue
                            )
                                ? ""
                                : "none";

                    }
                );

            }
        );

    };

    //    Smooth Scroll to Sections

    const internalLinks =
        document.querySelectorAll(
            'a[href^="#"]'
        );

    internalLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function (event) {

                const targetId =
                    link.getAttribute("href");

                if (
                    !targetId ||
                    targetId === "#"
                ) {
                    return;
                }

                const targetElement =
                    document.querySelector(
                        targetId
                    );

                if (!targetElement) {
                    return;
                }

                event.preventDefault();

                targetElement.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }
        );

    });


});