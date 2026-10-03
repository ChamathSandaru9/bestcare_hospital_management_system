
document.addEventListener("DOMContentLoaded", function () {

    /* Hero Slider */

    const slides = document.querySelectorAll(".hero-slide");
    const dots = document.querySelectorAll(".slider-dot");
    const previousButton = document.querySelector(".previous-slide");
    const nextButton = document.querySelector(".next-slide");
    const heroSlider = document.querySelector(".hero-slider");

    let currentSlide = 0;
    let slideTimer = null;

    const slideDelay = 5000;

    function showSlide(index) {

        if (slides.length === 0) {
            return;
        }

        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }

        slides.forEach(function (slide) {
            slide.classList.remove("active");
        });

        dots.forEach(function (dot) {
            dot.classList.remove("active");
        });

        slides[currentSlide].classList.add("active");

        if (dots[currentSlide]) {
            dots[currentSlide].classList.add("active");
        }
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function previousSlide() {
        showSlide(currentSlide - 1);
    }

    function startAutoSlide() {

        stopAutoSlide();

        if (slides.length > 1) {
            slideTimer = setInterval(nextSlide, slideDelay);
        }
    }

    function stopAutoSlide() {

        if (slideTimer !== null) {
            clearInterval(slideTimer);
            slideTimer = null;
        }
    }

    if (nextButton) {

        nextButton.addEventListener("click", function () {
            nextSlide();
            startAutoSlide();
        });

    }

    if (previousButton) {

        previousButton.addEventListener("click", function () {
            previousSlide();
            startAutoSlide();
        });

    }

    dots.forEach(function (dot, index) {

        dot.addEventListener("click", function () {
            showSlide(index);
            startAutoSlide();
        });

    });

    if (heroSlider) {

        heroSlider.addEventListener("mouseenter", stopAutoSlide);

        heroSlider.addEventListener("mouseleave", startAutoSlide);

        heroSlider.addEventListener(
            "touchstart",
            stopAutoSlide,
            { passive: true }
        );

        heroSlider.addEventListener(
            "touchend",
            startAutoSlide,
            { passive: true }
        );

    }

    document.addEventListener("keydown", function (event) {

        if (event.key === "ArrowRight") {
            nextSlide();
            startAutoSlide();
        }

        if (event.key === "ArrowLeft") {
            previousSlide();
            startAutoSlide();
        }

    });

    showSlide(0);
    startAutoSlide();

    /*  Smooth Scrolling */

    const internalLinks = document.querySelectorAll('a[href^="#"]');

    internalLinks.forEach(function (link) {

        link.addEventListener("click", function (event) {

            const targetId = link.getAttribute("href");

            if (!targetId || targetId === "#") {
                return;
            }

            const targetElement = document.querySelector(targetId);

            if (targetElement) {

                event.preventDefault();

                targetElement.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }

        });

    });


    /* Active Navigation Link on Scroll */

    const sections = document.querySelectorAll(
        "#home, #about, #services, #departments, #emergency, #contact"
    );

    const sectionLinks = document.querySelectorAll(
        '.nav-links a[href^="#"]'
    );

    function updateActiveNavigation() {

        let currentSectionId = "";

        sections.forEach(function (section) {

            const sectionTop = section.offsetTop - 180;
            const sectionHeight = section.offsetHeight;

            if (
                window.scrollY >= sectionTop &&
                window.scrollY < sectionTop + sectionHeight
            ) {
                currentSectionId = section.getAttribute("id");
            }

        });

        sectionLinks.forEach(function (link) {

            link.classList.remove("active");

            if (
                currentSectionId &&
                link.getAttribute("href") === "#" + currentSectionId
            ) {
                link.classList.add("active");
            }

        });

    }

    window.addEventListener("scroll", updateActiveNavigation);
    updateActiveNavigation();


    /* Contact Form Validation */

    const contactForm = document.getElementById("contactForm");

    if (contactForm) {

        contactForm.addEventListener("submit", function (event) {

            const nameInput = document.getElementById("contactName");
            const emailInput = document.getElementById("contactEmail");
            const subjectInput = document.getElementById("contactSubject");
            const messageInput = document.getElementById("contactMessage");

            let isValid = true;

            clearAllErrors();

            if (!nameInput || nameInput.value.trim() === "") {

                showError(
                    nameInput,
                    "Please enter your full name."
                );

                isValid = false;

            } else if (!/^[A-Za-z\s.'-]{2,100}$/.test(nameInput.value.trim())) {

                showError(
                    nameInput,
                    "Please enter a valid name."
                );

                isValid = false;

            }

            if (!emailInput || emailInput.value.trim() === "") {

                showError(
                    emailInput,
                    "Please enter your email address."
                );

                isValid = false;

            } else if (!isValidEmail(emailInput.value.trim())) {

                showError(
                    emailInput,
                    "Please enter a valid email address."
                );

                isValid = false;

            }

            if (!subjectInput || subjectInput.value.trim() === "") {

                showError(
                    subjectInput,
                    "Please enter a subject."
                );

                isValid = false;

            } else if (subjectInput.value.trim().length < 3) {

                showError(
                    subjectInput,
                    "The subject must contain at least 3 characters."
                );

                isValid = false;

            }

            if (!messageInput || messageInput.value.trim() === "") {

                showError(
                    messageInput,
                    "Please enter your message."
                );

                isValid = false;

            } else if (messageInput.value.trim().length < 10) {

                showError(
                    messageInput,
                    "The message must contain at least 10 characters."
                );

                isValid = false;

            }

            if (!isValid) {
                event.preventDefault();
            }

        });

    }

    function showError(inputElement, message) {

        if (!inputElement) {
            return;
        }

        const formGroup = inputElement.closest(".form-group");

        if (!formGroup) {
            return;
        }

        const errorElement = formGroup.querySelector(".field-error");

        inputElement.classList.add("input-error");

        if (errorElement) {
            errorElement.textContent = message;
        }

    }

    function clearAllErrors() {

        const inputs = document.querySelectorAll(
            "#contactForm input, #contactForm textarea"
        );

        const errorMessages = document.querySelectorAll(
            "#contactForm .field-error"
        );

        inputs.forEach(function (input) {
            input.classList.remove("input-error");
        });

        errorMessages.forEach(function (error) {
            error.textContent = "";
        });

    }

    function isValidEmail(email) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    }


    /* Back to Top Button */

    const backToTopButton = document.getElementById("backToTop");

    function updateBackToTopButton() {

        if (!backToTopButton) {
            return;
        }

        if (window.scrollY > 450) {
            backToTopButton.classList.add("show");
        } else {
            backToTopButton.classList.remove("show");
        }

    }

    window.addEventListener("scroll", updateBackToTopButton);

    if (backToTopButton) {

        backToTopButton.addEventListener("click", function () {

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        });

    }

    updateBackToTopButton();

});