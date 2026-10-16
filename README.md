# 🦷 School Dental Records and Appointment Management System

A lightweight PHP & MySQL web application designed for campus dental clinics to manage student oral health records, track dental checkup histories, and streamline appointment requests.

---

## 📑 Table of Contents
1. [Features](#-features)
2. [Folder Structure](#-folder-structure)
3. [Prerequisites](#-prerequisites)
4. [Step-by-Step Installation Guide](#-step-by-step-installation-guide)
5. [Default Test Accounts](#-default-test-accounts)
6. [How to Use the System](#-how-to-use-the-system)
7. [Troubleshooting](#-troubleshooting)

---

## ✨ Features

- **Student Portal:**
  - Book dental appointments with preferred dates, times, and reasons.
  - Track appointment statuses (*Pending*, *Confirmed*, *Completed*, *Cancelled*).
  - View personal dental history, diagnoses, and treatment recommendations.

- **Dentist / Clinic Staff Portal:**
  - View and manage student appointment requests.
  - Confirm or update appointment statuses in real-time.
  - Create and save detailed dental examination records (Diagnosis, Treatment, Remarks).

- **Authentication & Security:**
  - Secure login & student self-registration system.
  - Password hashing using `BCRYPT`.
  - Role-based session access control.

---

## 📁 Folder Structure

Ensure your project files inside `htdocs/school-dental-system/` look like this:

```text
school-dental-system/
│
├── assets/
│   ├── css/
│   │   └── style.css       # Dental Teal Design Theme
│   └── js/
│       └── script.js      # Frontend validations
│
├── config/
│   └── database.php        # PDO Database connection
│
├── database/
│   ├── migrate.php         # Automated database & table builder
│   └── seed.php            # Automated sample data loader
│
├── auth/
│   ├── login.php           # User login page
│   ├── register.php        # Student registration page
│   └── logout.php         # Session logout script
│
├── student/
│   └── dashboard.php       # Student portal & appointment booking
│
├── dentist/
│   └── dashboard.php       # Dentist management portal
│
├── index.php               # System landing page
└── README.md               # Instructions guide
```

---

## 📋 Prerequisites

Before setting up the project, make sure you have installed:
- **XAMPP** (or WAMP / Laragon) with **PHP 8.0+** and **MySQL**.
- A web browser (Chrome, Edge, Firefox, or Brave).

---

## 🚀 Step-by-Step Installation Guide

Follow these 4 quick steps to run the application on your computer:

### Step 1: Move Files to `htdocs`
1. Open your XAMPP installation folder (usually `C:\xampp\`).
2. Open the `htdocs` directory (`C:\xampp\htdocs\`).
3. Paste or create your project directory so the location looks like:
   ```text
   C:\xampp\htdocs\school-dental-system\
   ```

---

### Step 2: Start Apache and MySQL
1. Launch the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Both indicators should turn **green**.

---

### Step 3: Run Automated Database Setup
Instead of importing `.sql` files manually in `phpMyAdmin`, use the built-in setup scripts:

## 📌 Database Name Requirement

* **Database Name:** `dental_care_db`
> **Note for Students:** The included `database/migrate.php` script will **automatically create** the `dental_care_db` database for you! However, if you prefer creating it manually in **phpMyAdmin**, make sure to name it `dental_care_db`.

1. **Create the Database and Tables:**  
   Open your browser and visit:  
   👉 `http://localhost/school-dental-system/database/migrate.php`  
   *Output:* You should see **"Dental System Migration Completed Successfully!"**

2. **Load Sample Accounts & Records:**  
   Next, visit:  
   👉 `http://localhost/school-dental-system/database/seed.php`  
   *Output:* You should see **"Dental Database Seeded Successfully!"**

---

### Step 4: Open the Application
Navigate to the homepage:  
👉 `http://localhost/school-dental-system/`

---

## 🔑 Default Test Accounts

Use these pre-seeded credentials to test both user roles immediately:

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Student** | `student@college.edu` | `password123` | Book appointments & view dental history |
| **Dentist** | `dentist@college.edu` | `password123` | Manage appointments & create dental records |

---

## 💻 How to Use the System

### 1️⃣ Testing as a Student
1. Log in using `student@college.edu` / `password123` (or register a new account).
2. Fill out the **Book a Dental Appointment** form with a preferred date, time, and reason (e.g., *"Routine Dental Cleaning"*).
3. Check the **Your Dental Appointments** table to track the status of your request.
4. Scroll down to review past checkups and clinical treatments under **Your Dental History**.

### 2️⃣ Testing as a Dentist
1. Log out from the student account.
2. Log in using `dentist@college.edu` / `password123`.
3. Under **Manage Student Appointments**, update a student's status (e.g., change from `Pending` to `Confirmed` or `Completed`) and click **Save**.
4. Use the **Create New Student Dental Record** form to log checkup details, diagnoses, treatments, and advice for students.

---

## ❓ Troubleshooting

- **Error: `Database Connection Failed`**
  - Make sure **MySQL** is running in your XAMPP Control Panel.
  - Verify that `config/database.php` uses default credentials (`username: 'root'`, `password: ''`).

- **Error: `Table doesn't exist`**
  - Re-run `http://localhost/school-dental-system/database/migrate.php` to regenerate all tables.

- **Page Not Found (404 Error)**
  - Verify that your folder name in `htdocs` strictly matches `school-dental-system`.

---

## 🛡️ Educational Use Notice
This application was created as an educational project demonstrating full-stack PHP/MySQL development, clean architecture, and web database operations.