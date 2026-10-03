document.addEventListener("DOMContentLoaded", function () {

       // Management Tabs

    const tabButtons =
        document.querySelectorAll(".tab-button");

    const tabSections =
        document.querySelectorAll(".management-section");

    function activateTab(button) {

        if (!button) {
            return;
        }

        const targetId =
            button.getAttribute("data-tab");

        const targetSection =
            document.getElementById(targetId);

        if (!targetSection) {
            return;
        }

        tabButtons.forEach(function (tabButton) {

            const isActive =
                tabButton === button;

            tabButton.classList.toggle(
                "active",
                isActive
            );

            tabButton.setAttribute(
                "aria-selected",
                isActive ? "true" : "false"
            );

            tabButton.setAttribute(
                "tabindex",
                isActive ? "0" : "-1"
            );

        });

        tabSections.forEach(function (section) {

            const isActive =
                section === targetSection;

            section.classList.toggle(
                "active",
                isActive
            );

            section.hidden = !isActive;

        });

    }

    tabButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                activateTab(button);

            }
        );

    });

       // Open Tab From URL

    const urlParameters =
        new URLSearchParams(
            window.location.search
        );

    const requestedTab =
        urlParameters.get("tab");

    let requestedButton = null;

    if (requestedTab) {

        requestedButton =
            document.querySelector(
                '.tab-button[data-tab="' +
                requestedTab +
                '"]'
            );

    }

    const initialButton =
        requestedButton ||
        document.querySelector(
            ".tab-button.active"
        ) ||
        tabButtons[0];

    if (initialButton) {

        activateTab(initialButton);

    }
    
       // Modal Controls

    const modalConfigurations = [
        {
            buttonId: "addDoctorBtn",
            modalId: "addDoctorModal"
        },
        {
            buttonId: "createBillBtn",
            modalId: "createBillModal"
        },
        {
            buttonId: "addResourceBtn",
            modalId: "addResourceModal"
        }
    ];

    let lastFocusedElement = null;

    function getActiveModal() {

        return document.querySelector(
            ".modal.active"
        );

    }

    function lockPageScroll() {

        document.body.classList.add(
            "modal-open"
        );

        document.body.style.overflow =
            "hidden";

    }

    function unlockPageScroll() {

        const activeModal =
            getActiveModal();

        const sidebarOpen =
            document.body.classList.contains(
                "sidebar-open"
            );

        if (!activeModal && !sidebarOpen) {

            document.body.classList.remove(
                "modal-open"
            );

            document.body.style.overflow =
                "";

        }

    }

    function openModal(modal) {

        if (!modal) {
            return;
        }

        const currentlyOpenModal =
            getActiveModal();

        if (
            currentlyOpenModal &&
            currentlyOpenModal !== modal
        ) {

            currentlyOpenModal.classList.remove(
                "active"
            );

            currentlyOpenModal.setAttribute(
                "aria-hidden",
                "true"
            );

        }

        lastFocusedElement =
            document.activeElement;

        modal.classList.add("active");

        modal.setAttribute(
            "aria-hidden",
            "false"
        );

        lockPageScroll();

        const firstFocusableElement =
            modal.querySelector(
                [
                    "input:not([type='hidden']):not([disabled])",
                    "select:not([disabled])",
                    "textarea:not([disabled])",
                    "button:not([disabled])",
                    "[href]",
                    '[tabindex]:not([tabindex="-1"])'
                ].join(",")
            );

        if (firstFocusableElement) {

            window.setTimeout(
                function () {

                    firstFocusableElement.focus();

                },
                0
            );

        }

    }

    function closeModal(
        modal,
        restoreFocus = true
    ) {

        if (!modal) {
            return;
        }

        modal.classList.remove("active");

        modal.setAttribute(
            "aria-hidden",
            "true"
        );

        unlockPageScroll();

        if (
            restoreFocus &&
            lastFocusedElement &&
            document.contains(
                lastFocusedElement
            ) &&
            typeof lastFocusedElement.focus ===
                "function"
        ) {

            lastFocusedElement.focus();

        }

    }

    modalConfigurations.forEach(
        function (configuration) {

            const openButton =
                document.getElementById(
                    configuration.buttonId
                );

            const modal =
                document.getElementById(
                    configuration.modalId
                );

            if (!openButton || !modal) {
                return;
            }

            openButton.addEventListener(
                "click",
                function () {

                    openModal(modal);

                }
            );

        }
    );

    const closeModalButtons =
        document.querySelectorAll(
            ".close-modal"
        );

    closeModalButtons.forEach(
        function (button) {

            button.addEventListener(
                "click",
                function () {

                    const modal =
                        button.closest(
                            ".modal"
                        );

                    closeModal(modal);

                }
            );

        }
    );

    const allModals =
        document.querySelectorAll(
            ".modal"
        );

    allModals.forEach(function (modal) {

        modal.addEventListener(
            "click",
            function (event) {

                if (event.target === modal) {

                    closeModal(modal);

                }

            }
        );

    });

       // Mobile Sidebar

    const sidebar =
        document.getElementById(
            "adminSidebar"
        ) ||
        document.querySelector(
            ".admin-sidebar"
        );

    const sidebarToggle =
        document.getElementById(
            "adminSidebarToggle"
        );

    const sidebarOverlay =
        document.getElementById(
            "adminSidebarOverlay"
        );

    function isSidebarOpen() {

        return Boolean(
            sidebar &&
            (
                sidebar.classList.contains(
                    "active"
                ) ||
                sidebar.classList.contains(
                    "show"
                )
            )
        );

    }

    function openSidebar() {

        if (!sidebar) {
            return;
        }

        sidebar.classList.add("active");

        sidebar.classList.add("show");

        document.body.classList.add(
            "sidebar-open"
        );

        document.body.style.overflow =
            "hidden";

        if (sidebarToggle) {

            sidebarToggle.setAttribute(
                "aria-expanded",
                "true"
            );

        }

        if (sidebarOverlay) {

            sidebarOverlay.classList.add(
                "active"
            );

            sidebarOverlay.classList.add(
                "show"
            );

            sidebarOverlay.setAttribute(
                "aria-hidden",
                "false"
            );

        }

    }

    function closeSidebar() {

        if (!sidebar) {
            return;
        }

        sidebar.classList.remove("active");

        sidebar.classList.remove("show");

        document.body.classList.remove(
            "sidebar-open"
        );

        if (sidebarToggle) {

            sidebarToggle.setAttribute(
                "aria-expanded",
                "false"
            );

        }

        if (sidebarOverlay) {

            sidebarOverlay.classList.remove(
                "active"
            );

            sidebarOverlay.classList.remove(
                "show"
            );

            sidebarOverlay.setAttribute(
                "aria-hidden",
                "true"
            );

        }

        const activeModal =
            document.querySelector(
                ".modal.active"
            );

        if (!activeModal) {

            document.body.style.overflow =
                "";

        }

    }

    if (sidebarToggle) {

        sidebarToggle.addEventListener(
            "click",
            function () {

                if (isSidebarOpen()) {

                    closeSidebar();

                } else {

                    openSidebar();

                }

            }
        );

    }

    if (sidebarOverlay) {

        sidebarOverlay.addEventListener(
            "click",
            closeSidebar
        );

    }

    const sidebarLinks =
        document.querySelectorAll(
            ".admin-navigation a, .admin-sidebar a"
        );

    sidebarLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function () {

                if (window.innerWidth <= 900) {

                    closeSidebar();

                }

            }
        );

    });

    window.addEventListener(
        "resize",
        function () {

            if (window.innerWidth > 900) {

                closeSidebar();

            }

        }
    );

      // Escape Key Controls

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key !== "Escape") {
                return;
            }

            const activeModal =
                document.querySelector(
                    ".modal.active"
                );

            if (activeModal) {

                closeModal(activeModal);

                return;

            }

            if (isSidebarOpen()) {

                closeSidebar();

            }

        }
    );

       // Confirm Status Changes

    const actionForms =
        document.querySelectorAll(
            ".status-form, .table-action-form"
        );

    actionForms.forEach(function (form) {

        const actionInput =
            form.querySelector(
                'input[name="action"]'
            );

        const action =
            actionInput
                ? actionInput.value
                : "";

        const statusActions = [
            "toggle_doctor_status",
            "toggle_patient_status"
        ];

        const requiresConfirmation =
            form.classList.contains(
                "status-form"
            ) ||
            statusActions.includes(action);

        if (!requiresConfirmation) {
            return;
        }

        form.addEventListener(
            "submit",
            function (event) {

                const confirmed =
                    window.confirm(
                        "Are you sure you want to change this account status?"
                    );

                if (!confirmed) {

                    event.preventDefault();

                }

            }
        );

    });

       // Modal Form Validation

    const modalForms =
        document.querySelectorAll(
            ".modal form"
        );

    modalForms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function (event) {

                if (!form.checkValidity()) {

                    event.preventDefault();

                    form.reportValidity();

                    return;

                }

                const requiredFields =
                    form.querySelectorAll(
                        "[required]"
                    );

                for (
                    const field of requiredFields
                ) {

                    if (
                        typeof field.value ===
                            "string" &&
                        field.value.trim() === ""
                    ) {

                        event.preventDefault();

                        window.alert(
                            "Please complete all required fields."
                        );

                        field.focus();

                        return;

                    }

                }

            }
        );

    });

       // Auto-hide Messages

    const feedbackMessages =
        document.querySelectorAll(
            ".success-message, .error-message"
        );

    feedbackMessages.forEach(
        function (message) {

            window.setTimeout(
                function () {

                    message.style.transition =
                        "opacity 0.4s ease";

                    message.style.opacity = "0";

                    window.setTimeout(
                        function () {

                            message.remove();

                        },
                        400
                    );

                },
                4000
            );

        }
    );

       // Prevent Double Form Submission

    const allForms =
        document.querySelectorAll("form");

    allForms.forEach(function (form) {

        form.addEventListener(
            "submit",
            function () {

                if (!form.checkValidity()) {
                    return;
                }

                const submitButton =
                    form.querySelector(
                        'button[type="submit"], input[type="submit"]'
                    );

                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;

                if (
                    submitButton.tagName ===
                    "BUTTON"
                ) {

                    submitButton.dataset.originalText =
                        submitButton.innerHTML;

                    submitButton.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

                } else {

                    submitButton.dataset.originalValue =
                        submitButton.value;

                    submitButton.value =
                        "Processing...";

                }

            }
        );

    });

});