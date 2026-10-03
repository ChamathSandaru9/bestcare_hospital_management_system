## BestCare Hospital Management System

A web-based Hospital Management System developed as a university assessment project. The system is designed to manage and support common hospital operations through separate functions for administrators, doctors and patients.

## 📌 Project Overview

BestCare Hospital Management System provides a centralized platform for managing hospital-related activities such as patient registration, appointments, medical records, laboratory reports, billing, and user management.

The project demonstrates practical knowledge of web development, database management, authentication, security, and role-based system functionality.

✨ Main Features

* 🔐 User authentication and role-based access
* 👤 Patient registration and management
* 👨‍⚕️ Doctor management
* 📅 Appointment scheduling and management
* 🏥 Medical records management
* 🧪 Laboratory reports management
* 💳 Billing and payment management
* 👥 User and staff management
* 📊 Administrative functions
* 🛡️ CSRF protection
* ✅ Input validation
* 🔒 Password hashing and secure authentication

## 👥 User Roles

The system supports different user roles with role-specific functionality:

* Administrator – Manage users, doctors, staff, appointments, and administrative functions
* Doctor – Manage appointments and patient-related medical information
* Patient – Manage appointments and access medical and laboratory information

## 🛠️ Technologies Used

### Frontend

* HTML5
* CSS3
* JavaScript

### Backend

* PHP

### Database

* MySQL

### Development Environment

* XAMPP
* Apache
* Visual Studio Code

## 🔐 Security Features

The system includes several security measures to protect user data and system operations:

* Password hashing
* Prepared SQL statements
* Input validation
* Session-based authentication
* Role-based access control
* CSRF token protection
* Protection against unauthorized actions

## 🚀 Installation

### 1. Install XAMPP

Install XAMPP with Apache and MySQL.

### 2. Clone the Repository

```bash
git clone https://github.com/ChamathSandaru9/bestcare-hospital-management-system.git
```

### 3. Move the Project

Place the project folder inside:

```text
C:\xampp\htdocs\
```

### 4. Start XAMPP

Start:

* Apache
* MySQL

### 5. Configure the Database

1. Open **phpMyAdmin**:

```text
http://localhost/phpmyadmin
```

2. Create a new MySQL database for the BestCare Hospital Management System.

3. Select the newly created database and open the **Import** tab.

4. Select the SQL database file located in the project's `database` folder:

```text
bestcare.sql
```

5. Click **Import** or **Go** to import the database.

6. Open the database configuration file:

```text
db_process/includes/connection.php
```

7. Update the database connection details according to your local MySQL configuration.


### 6. Run the System

Open your browser and visit:

```text
http://localhost/bestcare/
```

## 📁 Project Structure

```text
bestcare_hospital_management/
│
├── css/
│   └── CSS files
│
├── db_process/
│   └── Database processing files
│
├── image/
│   └── Images and hospital assets
│
├── js/
│   └── JavaScript files
│
├── uploads/
│   └── Uploaded files
│
├── .gitattributes
│
├── admin_appointments.php
├── admin_management.php
│
├── bestcare.sql
│
├── doctor_appointments.php
├── doctor_dashboard.php
├── doctor_patients.php
│
├── footer.php
├── heder.php
├── index.php
│
├── login.php
├── logout.php
├── register.php
│
├── patient_appointments.php
├── patient_dashboard.php
├── patient_records.php
│
└── README.md
```


## 🎓 Academic Project

This project was developed as part of a university assessment to demonstrate practical skills in:

* Web application development
* PHP programming
* MySQL database management
* Frontend development
* Authentication and authorization
* Web security
* CRUD operations
* Software development practices

## 📚 Learning Outcomes

Through this project, practical experience was gained in designing and developing a database-driven web application, implementing user roles and authentication, handling database operations using PHP and MySQL, and applying basic web security practices.

## 👨‍💻 Author

**Chamath Sandaru**

University Project – BestCare Hospital Management System
