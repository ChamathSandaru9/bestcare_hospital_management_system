<?php
session_start();

$pageTitle = "BestCare Hospital";

include "heder.php";
?>

<!-- Home Page Stylesheet -->
<link rel="stylesheet" href="css/index.css">

<main>

    <!-- Hero Slider -->

    <section class="hero-slider" id="home">

        <!-- Slide 1 -->
        <article class="hero-slide active">

            <img
                src="image/slide1.png"
                alt="BestCare Hospital healthcare professionals"
            >

            <div class="hero-overlay"></div>

            <div class="hero-content">

                <span class="hero-subtitle">
                    Welcome to BestCare Hospital
                </span>

                <h1>
                    Your Health Is Our
                    <span>Top Priority</span>
                </h1>

                <p>
                    BestCare Hospital provides reliable, compassionate,
                    and patient-centred healthcare services in Matara.
                </p>

                <div class="hero-buttons">

                    <a href="register.php" class="hero-primary-button">

                        <i class="fa-solid fa-user-plus"></i>

                        Register as a Patient

                    </a>

                    <a href="#services" class="hero-secondary-button">

                        Explore Our Services

                    </a>

                </div>

            </div>

        </article>

        <!-- Slide 2 -->
        <article class="hero-slide">

            <img
                src="image/slide2.png"
                alt="BestCare Hospital medical services"
            >

            <div class="hero-overlay"></div>

            <div class="hero-content">

                <span class="hero-subtitle">
                    Professional Medical Care
                </span>

                <h1>
                    Comprehensive
                    <span>Healthcare Services</span>
                </h1>

                <p>
                    Access general consultations, specialist care,
                    laboratory services, pharmacy support, and emergency care.
                </p>

                <div class="hero-buttons">

                    <a href="#services" class="hero-primary-button">

                        <i class="fa-solid fa-stethoscope"></i>

                        View Services

                    </a>

                    <a href="login.php" class="hero-secondary-button">

                        Book an Appointment

                    </a>

                </div>

            </div>

        </article>

        <!-- Slide 3 -->
        <article class="hero-slide">

            <img
                src="image/slide3.png"
                alt="BestCare Hospital medical departments"
            >

            <div class="hero-overlay"></div>

            <div class="hero-content">

                <span class="hero-subtitle">
                    Specialised Medical Departments
                </span>

                <h1>
                    Expert Care Across
                    <span>Multiple Specialities</span>
                </h1>

                <p>
                    Our departments provide specialised care for patients
                    with different medical conditions and healthcare needs.
                </p>

                <div class="hero-buttons">

                    <a href="#departments" class="hero-primary-button">

                        <i class="fa-solid fa-hospital"></i>

                        View Departments

                    </a>

                    <a href="#contact" class="hero-secondary-button">

                        Contact Us

                    </a>

                </div>

            </div>

        </article>

        <!-- Slide 4 -->
        <article class="hero-slide">

            <img
                src="image/slide4.png"
                alt="BestCare Hospital laboratory facilities"
            >

            <div class="hero-overlay"></div>

            <div class="hero-content">

                <span class="hero-subtitle">
                    Accurate and Reliable
                </span>

                <h1>
                    Advanced
                    <span>Laboratory Services</span>
                </h1>

                <p>
                    Our modern laboratory provides secure, reliable,
                    and accurate diagnostic reports for patients and doctors.
                </p>

                <div class="hero-buttons">

                    <a href="#services" class="hero-primary-button">

                        <i class="fa-solid fa-flask-vial"></i>

                        Laboratory Services

                    </a>

                    <a href="#emergency" class="hero-secondary-button">

                        Emergency Support

                    </a>

                </div>

            </div>

        </article>

        <!-- Slider Controls -->

        <button
            type="button"
            class="slider-arrow previous-slide"
            aria-label="Show previous slide"
        >

            <i class="fa-solid fa-chevron-left"></i>

        </button>

        <button
            type="button"
            class="slider-arrow next-slide"
            aria-label="Show next slide"
        >

            <i class="fa-solid fa-chevron-right"></i>

        </button>

        <!-- Slider Dots -->

        <div class="slider-dots">

            <button
                type="button"
                class="slider-dot active"
                aria-label="Show slide 1"
            ></button>

            <button
                type="button"
                class="slider-dot"
                aria-label="Show slide 2"
            ></button>

            <button
                type="button"
                class="slider-dot"
                aria-label="Show slide 3"
            ></button>

            <button
                type="button"
                class="slider-dot"
                aria-label="Show slide 4"
            ></button>

        </div>

    </section>

    <!-- Quick Information -->

    <section class="quick-information">

        <div class="home-container quick-information-grid">

            <article class="quick-information-card">

                <i class="fa-solid fa-truck-medical"></i>

                <div>

                    <h3>24/7 Emergency</h3>

                    <p>Emergency medical assistance available every day.</p>

                </div>

            </article>

            <article class="quick-information-card">

                <i class="fa-solid fa-user-doctor"></i>

                <div>

                    <h3>Qualified Doctors</h3>

                    <p>Professional care from experienced medical specialists.</p>

                </div>

            </article>

            <article class="quick-information-card">

                <i class="fa-solid fa-flask-vial"></i>

                <div>

                    <h3>Modern Laboratory</h3>

                    <p>Reliable diagnostic testing and laboratory reporting.</p>

                </div>

            </article>

            <article class="quick-information-card">

                <i class="fa-solid fa-calendar-check"></i>

                <div>

                    <h3>Online Appointments</h3>

                    <p>Registered patients can request appointments online.</p>

                </div>

            </article>

        </div>

    </section>

    <!-- About BestCare Hospital -->

    <section class="home-section about-section" id="about">

        <div class="home-container about-grid">

            <div class="about-image">

                <img
                    src="image/about.png"
                    alt="BestCare Hospital healthcare team"
                >

                <div class="about-image-badge">

                    <i class="fa-solid fa-heart-pulse"></i>

                    <div>

                        <strong>Trusted Healthcare</strong>

                        <span>Compassion • Excellence • Trust</span>

                    </div>

                </div>

            </div>

            <div class="about-content">

                <span class="section-label">
                    About BestCare Hospital
                </span>

                <h2>
                    Patient-Centred Healthcare in Matara
                </h2>

                <p>
                    BestCare Hospital is a private healthcare facility
                    established to provide efficient and reliable medical
                    services to patients and families in Matara.
                </p>

                <p>
                    The hospital combines experienced healthcare professionals,
                    modern medical facilities, secure patient information
                    management, and user-friendly digital services.
                </p>

                <div class="about-values">

                    <article>

                        <i class="fa-solid fa-bullseye"></i>

                        <div>

                            <h3>Our Mission</h3>

                            <p>
                                To provide accessible, safe, and compassionate
                                healthcare services to every patient.
                            </p>

                        </div>

                    </article>

                    <article>

                        <i class="fa-solid fa-eye"></i>

                        <div>

                            <h3>Our Vision</h3>

                            <p>
                                To become a trusted healthcare provider through
                                quality service and responsible use of technology.
                            </p>

                        </div>

                    </article>

                </div>

                <div class="about-features">

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Qualified healthcare professionals
                    </span>

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Secure digital medical information
                    </span>

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Modern laboratory facilities
                    </span>

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        Friendly patient support
                    </span>

                </div>

            </div>

        </div>

    </section>

    <!-- Medical Services -->

    <section class="home-section services-section" id="services">

        <div class="home-container">

            <div class="section-heading">

                <span class="section-label">
                    Our Medical Services
                </span>

                <h2>
                    Healthcare Services for Different Patient Needs
                </h2>

                <p>
                    BestCare Hospital provides essential medical services
                    through qualified staff and organised hospital facilities.
                </p>

            </div>

            <div class="services-grid">

                <article class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-stethoscope"></i>

                    </div>

                    <h3>General Consultation</h3>

                    <p>
                        Professional medical consultations for routine health
                        concerns, check-ups, and general treatment.
                    </p>

                </article>

                <article class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-heart-pulse"></i>

                    </div>

                    <h3>Specialist Care</h3>

                    <p>
                        Access experienced specialists across different medical
                        departments and healthcare fields.
                    </p>

                </article>

                <article class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-flask-vial"></i>

                    </div>

                    <h3>Laboratory Services</h3>

                    <p>
                        Reliable diagnostic testing with secure laboratory
                        reporting for doctors and patients.
                    </p>

                </article>

                <article class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-truck-medical"></i>

                    </div>

                    <h3>Emergency Care</h3>

                    <p>
                        Immediate medical assistance and emergency support
                        available 24 hours a day.
                    </p>

                </article>

                <article class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-prescription-bottle-medical"></i>

                    </div>

                    <h3>Pharmacy Services</h3>

                    <p>
                        Convenient access to prescribed medicines and
                        pharmaceutical guidance.
                    </p>

                </article>

                <article class="service-card">

                    <div class="service-icon">

                        <i class="fa-solid fa-bed-pulse"></i>

                    </div>

                    <h3>Inpatient Care</h3>

                    <p>
                        Hospital rooms and beds allocated to patients who
                        require continued medical care.
                    </p>

                </article>

            </div>

        </div>

    </section>

    <!-- Hospital Departments -->

    <section class="home-section departments-section" id="departments">

        <div class="home-container">

            <div class="section-heading">

                <span class="section-label">
                    Hospital Departments
                </span>

                <h2>
                    Specialised Departments for Better Care
                </h2>

                <p>
                    Our departments support patients with different health
                    concerns and treatment requirements.
                </p>

            </div>

            <div class="departments-grid">

                <article class="department-card">

                    <i class="fa-solid fa-heart"></i>

                    <h3>Cardiology</h3>

                    <p>
                        Care and treatment for heart and cardiovascular
                        conditions.
                    </p>

                </article>

                <article class="department-card">

                    <i class="fa-solid fa-brain"></i>

                    <h3>Neurology</h3>

                    <p>
                        Assessment and treatment of nervous-system conditions.
                    </p>

                </article>

                <article class="department-card">

                    <i class="fa-solid fa-child-reaching"></i>

                    <h3>Pediatrics</h3>

                    <p>
                        Medical care and health support for infants and children.
                    </p>

                </article>

                <article class="department-card">

                    <i class="fa-solid fa-bone"></i>

                    <h3>Orthopedics</h3>

                    <p>
                        Treatment for bones, joints, muscles, and movement
                        conditions.
                    </p>

                </article>

                <article class="department-card">

                    <i class="fa-solid fa-ear-listen"></i>

                    <h3>ENT</h3>

                    <p>
                        Diagnosis and treatment for ear, nose, and throat
                        conditions.
                    </p>

                </article>

                <article class="department-card">

                    <i class="fa-solid fa-venus"></i>

                    <h3>Gynecology</h3>

                    <p>
                        Specialised healthcare services and support for women.
                    </p>

                </article>

            </div>

        </div>

    </section>

    <!-- Emergency Contact -->

    <section class="emergency-section" id="emergency">

        <div class="home-container emergency-content">

            <div class="emergency-main">

                <div class="emergency-icon">

                    <i class="fa-solid fa-truck-medical"></i>

                </div>

                <div>

                    <span>24/7 Emergency Service</span>

                    <h2>Do You Need Immediate Medical Assistance?</h2>

                    <p>
                        Contact BestCare Hospital immediately for urgent
                        medical support.
                    </p>

                </div>

            </div>

            <div class="emergency-contact-cards">

                <a href="tel:+94412221234" class="emergency-contact-card">

                    <i class="fa-solid fa-phone"></i>

                    <div>

                        <span>Emergency Hotline</span>

                        <strong>+94 41 222 1234</strong>

                    </div>

                </a>

                <a href="mailto:info@bestcarehospital.lk"
                   class="emergency-contact-card">

                    <i class="fa-solid fa-envelope"></i>

                    <div>

                        <span>Hospital Email</span>

                        <strong>info@bestcarehospital.lk</strong>

                    </div>

                </a>

            </div>

        </div>

    </section>

    <!-- Contact / General Query Form -->

    <section class="home-section contact-section" id="contact">

        <div class="home-container contact-grid">

            <div class="contact-information">

                <span class="section-label">
                    Contact BestCare Hospital
                </span>

                <h2>How Can We Help You?</h2>

                <p>
                    Send a general question about hospital services,
                    departments, appointments, or facilities.
                </p>

                <div class="contact-details">

                    <article>

                        <i class="fa-solid fa-location-dot"></i>

                        <div>

                            <h3>Hospital Address</h3>

                            <p>Matara, Sri Lanka</p>

                        </div>

                    </article>

                    <article>

                        <i class="fa-solid fa-phone"></i>

                        <div>

                            <h3>Telephone</h3>

                            <p>+94 41 222 1234</p>

                        </div>

                    </article>

                    <article>

                        <i class="fa-solid fa-envelope"></i>

                        <div>

                            <h3>Email</h3>

                            <p>info@bestcarehospital.lk</p>

                        </div>

                    </article>

                    <article>

                        <i class="fa-solid fa-clock"></i>

                        <div>

                            <h3>Opening Hours</h3>

                            <p>Open 24 Hours</p>

                        </div>

                    </article>

                </div>

            </div>

            <div class="contact-form-container">

                <h2>Submit a General Query</h2>

                <?php if (isset($_SESSION["contact_success"])): ?>

                    <div class="form-message success-message">

                        <?php
                        echo htmlspecialchars($_SESSION["contact_success"]);
                        unset($_SESSION["contact_success"]);
                        ?>

                    </div>

                <?php endif; ?>

                <?php if (isset($_SESSION["contact_error"])): ?>

                    <div class="form-message error-message">

                        <?php
                        echo htmlspecialchars($_SESSION["contact_error"]);
                        unset($_SESSION["contact_error"]);
                        ?>

                    </div>

                <?php endif; ?>

                <form
                    action="db_process/contact_process.php"
                    method="POST"
                    id="contactForm"
                    novalidate
                >

                    <div class="form-row">

                        <div class="form-group">

                            <label for="contactName">
                                Full Name
                            </label>

                            <input
                                type="text"
                                id="contactName"
                                name="name"
                                maxlength="100"
                                placeholder="Enter your full name"
                                required
                            >

                            <small class="field-error"></small>

                        </div>

                        <div class="form-group">

                            <label for="contactEmail">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="contactEmail"
                                name="email"
                                maxlength="100"
                                placeholder="Enter your email address"
                                required
                            >

                            <small class="field-error"></small>

                        </div>

                    </div>

                    <div class="form-group">

                        <label for="contactSubject">
                            Subject
                        </label>

                        <input
                            type="text"
                            id="contactSubject"
                            name="subject"
                            maxlength="150"
                            placeholder="Enter the subject"
                            required
                        >

                        <small class="field-error"></small>

                    </div>

                    <div class="form-group">

                        <label for="contactMessage">
                            Message
                        </label>

                        <textarea
                            id="contactMessage"
                            name="message"
                            rows="6"
                            maxlength="1000"
                            placeholder="Write your question or message"
                            required
                        ></textarea>

                        <small class="field-error"></small>

                    </div>

                    <button type="submit" class="contact-submit-button">

                        <i class="fa-solid fa-paper-plane"></i>

                        Submit Query

                    </button>

                </form>

            </div>

        </div>

    </section>

    <!-- Back to Top -->

    <button
        type="button"
        id="backToTop"
        class="back-to-top"
        aria-label="Return to the top of the page"
    >

        <i class="fa-solid fa-arrow-up"></i>

    </button>

</main>

<!-- Home Page JavaScript -->
<script src="js/index.js"></script>

<?php
include "footer.php";
?>