
document.addEventListener("DOMContentLoaded", function () {

    /* Mobile Navigation */

    const menuToggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");
    const navButtons = document.querySelector(".nav-buttons");
    const menuIcon = menuToggle
        ? menuToggle.querySelector("i")
        : null;

    function closeMobileMenu() {

        if (navLinks) {
            navLinks.classList.remove("mobile-active");
        }

        if (navButtons) {
            navButtons.classList.remove("mobile-active");
        }

        if (menuIcon) {
            menuIcon.classList.remove("fa-xmark");
            menuIcon.classList.add("fa-bars");
        }

    }

    if (menuToggle && navLinks && navButtons) {

        menuToggle.addEventListener("click", function () {

            navLinks.classList.toggle("mobile-active");
            navButtons.classList.toggle("mobile-active");

            if (menuIcon) {

                const menuIsOpen =
                    navLinks.classList.contains("mobile-active");

                if (menuIsOpen) {
                    menuIcon.classList.remove("fa-bars");
                    menuIcon.classList.add("fa-xmark");
                } else {
                    menuIcon.classList.remove("fa-xmark");
                    menuIcon.classList.add("fa-bars");
                }

            }

        });

    }

    const navigationLinks = document.querySelectorAll(
        ".nav-links a, .nav-buttons a"
    );

    navigationLinks.forEach(function (link) {

        link.addEventListener("click", function () {
            closeMobileMenu();
        });

    });

    window.addEventListener("resize", function () {

        if (window.innerWidth > 1000) {
            closeMobileMenu();
        }

    });

});