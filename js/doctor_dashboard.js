document.addEventListener("DOMContentLoaded", () => {

    //  Sidebar Toggle 

    const sidebar =
        document.getElementById("doctorSidebar");

    const sidebarToggle =
        document.getElementById("doctorSidebarToggle");

    if (sidebar && sidebarToggle) {

        sidebarToggle.addEventListener(
            "click",
            () => {

                sidebar.classList.toggle("active");

            }
        );

        document.addEventListener(
            "click",
            (event) => {

                if (
                    window.innerWidth <= 991 &&
                    !sidebar.contains(event.target) &&
                    !sidebarToggle.contains(event.target)
                ) {

                    sidebar.classList.remove("active");

                }

            }
        );

    }

    //    Auto Hide Messages
    
    const messages =
        document.querySelectorAll(".doctor-message");

    messages.forEach((message) => {

        setTimeout(() => {

            message.style.opacity = "0";

            message.style.transform =
                "translateY(-10px)";

            message.style.transition =
                "all .4s ease";

            setTimeout(() => {

                message.remove();

            }, 400);

        }, 5000);

    });

    
    //    Card Hover Animation
    

    const summaryCards =
        document.querySelectorAll(
            ".doctor-summary-card"
        );

    summaryCards.forEach((card) => {

        card.addEventListener(
            "mouseenter",
            () => {

                card.style.transform =
                    "translateY(-6px)";

            }
        );

        card.addEventListener(
            "mouseleave",
            () => {

                card.style.transform =
                    "translateY(0)";

            }
        );

    });

    
    //    Quick Action Hover

    const quickActions =
        document.querySelectorAll(
            ".doctor-quick-actions a"
        );

    quickActions.forEach((action) => {

        action.addEventListener(
            "mouseenter",
            () => {

                const icon =
                    action.querySelector("i");

                if (icon) {

                    icon.style.transform =
                        "scale(1.1) rotate(-8deg)";

                }

            }
        );

        action.addEventListener(
            "mouseleave",
            () => {

                const icon =
                    action.querySelector("i");

                if (icon) {

                    icon.style.transform =
                        "scale(1) rotate(0deg)";

                }

            }
        );

    });

    //    Smooth Scroll

    document
        .querySelectorAll('a[href^="#"]')
        .forEach((anchor) => {

            anchor.addEventListener(
                "click",
                function (event) {

                    const target =
                        document.querySelector(
                            this.getAttribute("href")
                        );

                    if (!target) {
                        return;
                    }

                    event.preventDefault();

                    target.scrollIntoView({

                        behavior: "smooth",

                        block: "start"

                    });

                }
            );

        });

    //    Highlight Active Navigation

    const navigationLinks =
        document.querySelectorAll(
            ".doctor-navigation a"
        );

    navigationLinks.forEach((link) => {

        if (
            link.href === window.location.href
        ) {

            link.classList.add("active");

        } else {

            link.classList.remove("active");

        }

    });

    //    Dashboard Greeting

    const hour =
        new Date().getHours();

    const welcomeTitle =
        document.querySelector(
            ".doctor-welcome-banner h2"
        );

    if (welcomeTitle) {

        let greeting = "";

        if (hour < 12) {

            greeting = "Good Morning,";

        } else if (hour < 18) {

            greeting = "Good Afternoon,";

        } else {

            greeting = "Good Evening,";

        }

        welcomeTitle.innerHTML =
            welcomeTitle.innerHTML.replace(
                "Welcome,",
                greeting
            );

    }

    //    Count Animation

    const counters =
        document.querySelectorAll(
            ".doctor-summary-card strong"
        );

    counters.forEach((counter) => {

        const target =
            parseInt(counter.textContent);

        if (isNaN(target)) {
            return;
        }

        let current = 0;

        const increment =
            Math.max(
                1,
                Math.ceil(target / 40)
            );

        const timer =
            setInterval(() => {

                current += increment;

                if (current >= target) {

                    current = target;

                    clearInterval(timer);

                }

                counter.textContent =
                    current;

            }, 25);

    });

    //    Fade In Sections

    const observer =
        new IntersectionObserver(

            (entries) => {

                entries.forEach((entry) => {

                    if (
                        entry.isIntersecting
                    ) {

                        entry.target.classList.add(
                            "fade-visible"
                        );

                    }

                });

            },

            {
                threshold: 0.15
            }

        );

    document
        .querySelectorAll(
            ".doctor-panel, .doctor-profile-section"
        )
        .forEach((section) => {

            section.classList.add(
                "fade-hidden"
            );

            observer.observe(section);

        });

});