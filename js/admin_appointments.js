// Setup & DOM Elements//

document.addEventListener("DOMContentLoaded", () => {

    "use strict";

    // DOM Elements

    // Sidebar
    const sidebar = document.getElementById("adminSidebar");
    const sidebarToggle = document.getElementById("adminSidebarToggle");
    const sidebarOverlay = document.getElementById("adminSidebarOverlay");

    // Mobile Header
    const mobileHeader = document.querySelector(".admin-mobile-header");

    // Appointment Modal
    const appointmentModal = document.getElementById("appointmentModal");
    const modalCloseBtn = document.getElementById("closeModal");

    // Appointment Form
    const scheduleForm = document.getElementById("scheduleForm");

    const appointmentActionInput =
        document.getElementById("appointmentAction");

    const appointmentIdInput =
        document.getElementById("appointmentId");

    const appointmentDateInput =
        document.getElementById("appointmentDate");

    const appointmentTimeInput =
        document.getElementById("appointmentTime");

    // Schedule Buttons
    const scheduleButtons =
        document.querySelectorAll(".schedule-btn");

    const editButtons =
        document.querySelectorAll(".edit-btn");

    const cancelButtons =
        document.querySelectorAll(".cancel-btn");

    // Flash Messages
    const flashMessages =
        document.querySelectorAll(".success-message, .error-message");

    //   Constants

    const MOBILE_BREAKPOINT = 900;

    const CSS_CLASSES = {

        sidebarOpen: "active",

        overlayOpen: "active",

        modalOpen: "active"

    };

    //   Helper Functions

    //  Check if current screen is mobile.
    function isMobileView() {

        return window.innerWidth <= MOBILE_BREAKPOINT;

    }

    // Add a CSS class if the element exists.
    function addClass(element, className) {

        if (!element) return;

        element.classList.add(className);

    }

    // Remove a CSS class if the element exists.
    function removeClass(element, className) {

        if (!element) return;

        element.classList.remove(className);

    }

    // Toggle a CSS class.
    function toggleClass(element, className) {

        if (!element) return;

        element.classList.toggle(className);

    }

    // Check if an element contains a class.
    function hasClass(element, className) {

        if (!element) return false;

        return element.classList.contains(className);

    }

    // Disable body scrolling.
    function lockBodyScroll() {

        document.body.style.overflow = "hidden";

    }

    //  Restore body scrolling.
    function unlockBodyScroll() {

        document.body.style.overflow = "";

    }

    // Safely focus an element.
    function focusElement(element) {

        if (element) {

            element.focus();

        }

    }

    // Reset the appointment form.
    function resetAppointmentForm() {

        if (!scheduleForm) return;

        scheduleForm.reset();

        if (appointmentIdInput) {

            appointmentIdInput.value = "";

        }

    }

    // Hide an element.
    function hideElement(element) {

        if (!element) return;

        element.style.display = "none";

    }

    // Show an element.
    function showElement(element, displayType = "block") {

        if (!element) return;

        element.style.display = displayType;

    }

//Check whether the mobile sidebar is open.
function isSidebarOpen() {

    return hasClass(
        sidebar,
        CSS_CLASSES.sidebarOpen
    );

}

// Open the mobile sidebar.
function openSidebar() {

    if (!sidebar || !isMobileView()) return;

    addClass(
        sidebar,
        CSS_CLASSES.sidebarOpen
    );

    addClass(
        sidebarOverlay,
        CSS_CLASSES.overlayOpen
    );

    if (sidebarToggle) {

        sidebarToggle.setAttribute(
            "aria-expanded",
            "true"
        );

    }

    if (sidebarOverlay) {

        sidebarOverlay.setAttribute(
            "aria-hidden",
            "false"
        );

    }

    lockBodyScroll();

}

//Close the mobile sidebar.
function closeSidebar() {

    if (!sidebar) return;

    removeClass(
        sidebar,
        CSS_CLASSES.sidebarOpen
    );

    removeClass(
        sidebarOverlay,
        CSS_CLASSES.overlayOpen
    );

    if (sidebarToggle) {

        sidebarToggle.setAttribute(
            "aria-expanded",
            "false"
        );

    }

    if (sidebarOverlay) {

        sidebarOverlay.setAttribute(
            "aria-hidden",
            "true"
        );

    }

    // Do not restore page scrolling when the appointment modal is still open.
    const modalIsOpen =
        appointmentModal &&
        hasClass(
            appointmentModal,
            CSS_CLASSES.modalOpen
        );

    if (!modalIsOpen) {

        unlockBodyScroll();

    }

}

//Toggle the mobile sidebar.
function toggleSidebar() {

    if (!isMobileView()) return;

    if (isSidebarOpen()) {

        closeSidebar();

    } else {

        openSidebar();

    }

}

// Close the sidebar after selecting a navigation link on mobile.
function handleSidebarLinkClick() {

    if (isMobileView()) {

        closeSidebar();

    }

}

// Reset sidebar state when the screen changes from mobile to desktop.
function handleSidebarResize() {

    if (!isMobileView()) {

        closeSidebar();

    }

}

//   Sidebar Event Listeners

if (sidebarToggle) {

    sidebarToggle.setAttribute(
        "aria-expanded",
        "false"
    );

    sidebarToggle.setAttribute(
        "aria-controls",
        "adminSidebar"
    );

    sidebarToggle.addEventListener(
        "click",
        toggleSidebar
    );

}

if (sidebarOverlay) {

    sidebarOverlay.setAttribute(
        "aria-hidden",
        "true"
    );

    sidebarOverlay.addEventListener(
        "click",
        closeSidebar
    );

}

// Close the sidebar when a navigation link is selected on mobile.
if (sidebar) {

    const sidebarLinks =
        sidebar.querySelectorAll("a");

    sidebarLinks.forEach((link) => {

        link.addEventListener(
            "click",
            handleSidebarLinkClick
        );

    });

}

/* Close the sidebar with the Escape key.
 Modal Escape handling will be added later without creating another duplicate global keydown listener.*/
document.addEventListener(
    "keydown",
    (event) => {

        if (
            event.key === "Escape" &&
            isSidebarOpen()
        ) {

            closeSidebar();

            focusElement(sidebarToggle);

        }

    }
);

// Reset sidebar state when returning to desktop width.
window.addEventListener(
    "resize",
    handleSidebarResize
);

// Store the last focused element before opening the modal so focus can be restored.
let lastFocusedElement = null;

// Check if the appointment modal is open.
function isModalOpen() {

    return hasClass(
        appointmentModal,
        CSS_CLASSES.modalOpen
    );

}

// Open the appointment modal.
function openAppointmentModal(button = null) {

    if (!appointmentModal) return;

    lastFocusedElement = button || document.activeElement;

    addClass(
        appointmentModal,
        CSS_CLASSES.modalOpen
    );

    //Populate form fields from button data attributes.

    if (button) {

        if (
            appointmentIdInput &&
            button.dataset.appointmentId
        ) {

            appointmentIdInput.value =
                button.dataset.appointmentId;

        }

        if (
            appointmentDateInput &&
            button.dataset.date
        ) {

            appointmentDateInput.value =
                button.dataset.date;

        }

        if (
            appointmentTimeInput &&
            button.dataset.time
        ) {

            appointmentTimeInput.value =
                button.dataset.time;

        }

    }

    lockBodyScroll();

    // Focus the first editable field.
    setTimeout(() => {

        if (appointmentDateInput) {

            appointmentDateInput.focus();

        } else if (appointmentTimeInput) {

            appointmentTimeInput.focus();

        }

    }, 100);

}

// Close the appointment modal.
function closeAppointmentModal() {

    if (!appointmentModal) return;

    removeClass(
        appointmentModal,
        CSS_CLASSES.modalOpen
    );

    // Only unlock scrolling when the sidebar isn't open.
    if (!isSidebarOpen()) {

        unlockBodyScroll();

    }

    resetAppointmentForm();

    // Restore focus to the button that opened the modal.
    if (lastFocusedElement) {

        focusElement(lastFocusedElement);

        lastFocusedElement = null;

    }

}

// Populate the form using a button's data attributes.
function populateAppointmentForm(button) {

    if (!button) return;

    if (
        appointmentIdInput &&
        button.dataset.appointmentId
    ) {

        appointmentIdInput.value =
            button.dataset.appointmentId;

    }

    if (
        appointmentDateInput &&
        button.dataset.date
    ) {

        appointmentDateInput.value =
            button.dataset.date;

    }

    if (
        appointmentTimeInput &&
        button.dataset.time
    ) {

        appointmentTimeInput.value =
            button.dataset.time;

    }

}

//   Schedule Buttons

scheduleButtons.forEach((button) => {

    button.addEventListener("click", () => {
        
        if (appointmentActionInput) {
            appointmentActionInput.value = "schedule";
        }

        populateAppointmentForm(button);

        openAppointmentModal(button);

    });

});

//   Edit Buttons

editButtons.forEach((button) => {

    button.addEventListener("click", () => {

         if (appointmentActionInput) {
            appointmentActionInput.value = "reschedule";
        }

        populateAppointmentForm(button);

        openAppointmentModal(button);

    });

});

//   Modal Close Button

if (modalCloseBtn) {

    modalCloseBtn.addEventListener(
        "click",
        closeAppointmentModal
    );

}

//   Close Modal by Clicking Background

if (appointmentModal) {

    appointmentModal.addEventListener(
        "click",
        (event) => {

            if (
                event.target === appointmentModal
            ) {

                closeAppointmentModal();

            }

        }
    );

}


//  Escape Key

document.addEventListener(
    "keydown",
    (event) => {

        if (
            event.key === "Escape" &&
            isModalOpen()
        ) {

            closeAppointmentModal();

        }

    }
);

// Validate the appointment form.
function validateAppointmentForm() {

    if (!appointmentDateInput || !appointmentTimeInput) {

        return false;

    }

    const appointmentDate =
        appointmentDateInput.value.trim();

    const appointmentTime =
        appointmentTimeInput.value.trim();

    if (!appointmentDate) {

        appointmentDateInput.focus();

        alert("Please select an appointment date.");

        return false;

    }

    if (!appointmentTime) {

        appointmentTimeInput.focus();

        alert("Please select an appointment time.");

        return false;

    }

    return true;

}

//Disable submit button while the form is being submitted.
function disableSubmitButton() {

    if (!scheduleForm) return;

    const submitButton =
        scheduleForm.querySelector(
            'button[type="submit"]'
        );

    if (!submitButton) return;

    submitButton.disabled = true;

    submitButton.dataset.originalText =
        submitButton.innerHTML;

    submitButton.innerHTML =
        "Saving...";

}

// Restore submit button.
function enableSubmitButton() {

    if (!scheduleForm) return;

    const submitButton =
        scheduleForm.querySelector(
            'button[type="submit"]'
        );

    if (!submitButton) return;

    submitButton.disabled = false;

    if (submitButton.dataset.originalText) {

        submitButton.innerHTML =
            submitButton.dataset.originalText;

    }

}

//   Schedule Form Submission

if (scheduleForm) {

    scheduleForm.addEventListener(
        "submit",
        function (event) {

            if (!validateAppointmentForm()) {

                event.preventDefault();

                return;

            }

            disableSubmitButton();

        }
    );

}

//   Cancel Appointment/

cancelButtons.forEach((button) => {

    button.addEventListener(
        "click",
        function () {

            const appointmentId =
                button.dataset.appointmentId;

            if (!appointmentId) {

                alert(
                    "Invalid appointment selected."
                );

                return;

            }

            const confirmed =
                window.confirm(
                    "Are you sure you want to cancel this appointment?"
                );

            if (!confirmed) {
                return;
            }

            const csrfTokenInput =
                scheduleForm.querySelector(
                    'input[name="csrf_token"]'
                );

            if (!csrfTokenInput) {

                alert(
                    "Security token is missing. Please refresh the page."
                );

                return;

            }

            const cancelForm =
                document.createElement("form");

            cancelForm.method = "POST";

            cancelForm.action =
                "db_process/admin_appointment_process.php";

            cancelForm.style.display =
                "none";

            const formFields = {
                action: "cancel",
                appointment_id:
                    appointmentId,
                csrf_token:
                    csrfTokenInput.value
            };

            Object.entries(
                formFields
            ).forEach(
                ([name, value]) => {

                    const input =
                        document.createElement(
                            "input"
                        );

                    input.type = "hidden";
                    input.name = name;
                    input.value = value;

                    cancelForm.appendChild(
                        input
                    );

                }
            );

            document.body.appendChild(
                cancelForm
            );

            button.dataset.originalText =
                button.innerHTML;

            button.disabled = true;

            button.textContent =
                "Cancelling...";

            cancelForm.submit();

        }
    );

});

  //Prevent Double Clicks

const actionButtons = document.querySelectorAll(
    ".schedule-btn, .edit-btn"
);

actionButtons.forEach((button) => {

    button.addEventListener(
        "click",
        function () {

            button.blur();

        }
    );

});


  //Restore Submit Button (Browser Back/Forward Cache)


window.addEventListener(
    "pageshow",
    function () {

        enableSubmitButton();

    }
);

  //Flash Messages

// Hide a flash message with a fade-out effect.
function hideFlashMessage(message) {

    if (!message) return;

    message.style.transition =
        "opacity 0.3s ease, transform 0.3s ease";

    message.style.opacity = "0";
    message.style.transform = "translateY(-8px)";

    setTimeout(() => {

        message.remove();

    }, 300);

}

// Automatically hide success and error messages.
function initializeFlashMessages() {

    flashMessages.forEach((message) => {

        message.setAttribute(
            "role",
            "status"
        );

        message.setAttribute(
            "aria-live",
            "polite"
        );

        setTimeout(() => {

            hideFlashMessage(message);

        }, 5000);

    });

}

  //Appointment Date Restrictions

// Return today's date in YYYY-MM-DD format
//using the user's local timezone.
function getTodayDate() {

    const today = new Date();

    const year = today.getFullYear();

    const month = String(
        today.getMonth() + 1
    ).padStart(2, "0");

    const day = String(
        today.getDate()
    ).padStart(2, "0");

    return `${year}-${month}-${day}`;

}

// Prevent selecting appointment dates earlier than today's date.
function initializeDateInput() {

    if (!appointmentDateInput) return;

    appointmentDateInput.setAttribute(
        "min",
        getTodayDate()
    );

}

  //Modal Acessibility

// Return all keyboard-focusable elements currently available inside the modal
function getModalFocusableElements() {

    if (!appointmentModal) return [];

    return Array.from(
        appointmentModal.querySelectorAll(
            [
                "button:not([disabled])",
                "input:not([disabled])",
                "select:not([disabled])",
                "textarea:not([disabled])",
                "a[href]",
                '[tabindex]:not([tabindex="-1"])'
            ].join(",")
        )
    ).filter((element) => {

        return element.offsetParent !== null;

    });

}

// Keep keyboard focus inside the modal while it is open.
function trapModalFocus(event) {

    if (
        event.key !== "Tab" ||
        !isModalOpen()
    ) {

        return;

    }

    const focusableElements =
        getModalFocusableElements();

    if (focusableElements.length === 0) {

        event.preventDefault();

        return;

    }

    const firstElement =
        focusableElements[0];

    const lastElement =
        focusableElements[
            focusableElements.length - 1
        ];

    if (
        event.shiftKey &&
        document.activeElement === firstElement
    ) {

        event.preventDefault();

        lastElement.focus();

        return;

    }

    if (
        !event.shiftKey &&
        document.activeElement === lastElement
    ) {

        event.preventDefault();

        firstElement.focus();

    }

}

// Add modal accessibility attributes.
function initializeModalAccessibility() {

    if (!appointmentModal) return;

    appointmentModal.setAttribute(
        "role",
        "dialog"
    );

    appointmentModal.setAttribute(
        "aria-modal",
        "true"
    );

    appointmentModal.setAttribute(
        "aria-hidden",
        "true"
    );

    const modalHeading =
        appointmentModal.querySelector("h2");

    if (modalHeading) {

        if (!modalHeading.id) {

            modalHeading.id =
                "appointmentModalTitle";

        }

        appointmentModal.setAttribute(
            "aria-labelledby",
            modalHeading.id
        );

    }

    if (modalCloseBtn) {

        modalCloseBtn.setAttribute(
            "type",
            "button"
        );

        modalCloseBtn.setAttribute(
            "aria-label",
            "Close appointment form"
        );

    }

}

  // Modal State Synchronization

// Keep modal ARIA state synchronized with its visual open or closed state.
function updateModalAccessibilityState() {

    if (!appointmentModal) return;

    appointmentModal.setAttribute(
        "aria-hidden",
        isModalOpen() ? "false" : "true"
    );

}

// Observe class changes made by openAppointmentModal() and closeAppointmentModal()
let modalObserver = null;

function initializeModalObserver() {

    if (!appointmentModal) return;

    modalObserver = new MutationObserver(() => {

        updateModalAccessibilityState();

    });

    modalObserver.observe(
        appointmentModal,
        {
            attributes: true,
            attributeFilter: ["class"]
        }
    );

}

  //Shared Keyboard Controls

// Handle Escape and Tab using one global keyboard listener.
function handleGlobalKeydown(event) {

    if (event.key === "Tab") {

        trapModalFocus(event);

        return;

    }

    if (event.key !== "Escape") return;

    // Close the modal first when both the modal and sidebar are active.
    if (isModalOpen()) {

        closeAppointmentModal();

        return;

    }

    if (isSidebarOpen()) {

        closeSidebar();

        focusElement(sidebarToggle);

    }

}


  //Initial Sidebar State

function initializeSidebarAccessibility() {

    if (sidebar) {

        sidebar.setAttribute(
            "aria-label",
            "Administrator navigation"
        );

    }

    if (sidebarToggle) {

        sidebarToggle.setAttribute(
            "type",
            "button"
        );

        sidebarToggle.setAttribute(
            "aria-controls",
            "adminSidebar"
        );

        sidebarToggle.setAttribute(
            "aria-expanded",
            "false"
        );

        if (
            !sidebarToggle.getAttribute(
                "aria-label"
            )
        ) {

            sidebarToggle.setAttribute(
                "aria-label",
                "Open administrator navigation"
            );

        }

    }

    if (sidebarOverlay) {

        sidebarOverlay.setAttribute(
            "aria-hidden",
            "true"
        );

    }

}

  // Browser Page Restoration

// Reset interactive states when the page is restored from browser history.
function handlePageRestore() {

    enableSubmitButton();

    cancelButtons.forEach((button) => {

        button.disabled = false;

        if (button.dataset.originalText) {

            button.innerHTML =
                button.dataset.originalText;

        }

    });

}

  // Final Initialization

function initializeAdminAppointments() {

    initializeSidebarAccessibility();

    initializeModalAccessibility();

    initializeModalObserver();

    initializeDateInput();

    initializeFlashMessages();

    updateModalAccessibilityState();

}

//  Final Event Listeners

document.addEventListener(
    "keydown",
    handleGlobalKeydown
);

window.addEventListener(
    "pageshow",
    handlePageRestore
);

  //Start Page

initializeAdminAppointments();
});