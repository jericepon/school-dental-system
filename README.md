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
  - Create, edit, view, and delete dental examination records.
  - View appointment statistics and charts, filter reports, and open print previews.

- **Authentication & Security:**
  - Secure login & student self-registration system.
  - Password hashing using PHP `password_hash()` and verification with `password_verify()`.
  - Role-based access, session ID regeneration, CSRF tokens, and secure session cookies.
  - Arithmetic CAPTCHA and a limit of five failed sign-ins per email/IP in 15 minutes.
  - Audit history for login, logout, appointment, record, and backup activity.
- **Recovery & Reporting:**
  - Dentist-only backups containing application files and JSON database snapshots.
  - Validated restore workflow, protected backup storage, filters, and browser print preview.

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
│   ├── database.php        # PDO Database connection
│   ├── security.php        # Session, CSRF, escaping, and audit helpers
│   └── backup.php          # Backup and restore helpers
│
├── database/
│   ├── migrate.php         # Automated database & table builder
│   ├── seed.php            # Local, non-destructive sample data loader
│   └── backup.php          # CLI backup entry point for scheduled jobs
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
│   ├── dashboard.php       # Dentist management portal and reports
│   └── backup.php          # Dentist-only backup and restore screen
│
├── index.php               # System landing page
├── storage/backups/        # Protected backup archives
└── README.md               # Instructions guide
```

---

## 📋 Prerequisites

Before setting up the project, make sure you have installed:
- **XAMPP** (or WAMP / Laragon) with **PHP 8.0+**, **MySQL**, and the PHP **Zip** extension enabled.
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

  The seed script is limited to local requests and does not erase existing data. The migration is safe to rerun to create newly added tables.

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

## 🔐 Security and Recovery

- Run `database/migrate.php` after installing or updating the project so the audit and login-throttling tables are created. The migration uses `CREATE TABLE IF NOT EXISTS` and preserves existing application data.
- Login requires an arithmetic challenge and blocks sign-in after five failed attempts for the same email and IP address within 15 minutes. Passwords are stored with PHP's password hashing API.
- Forms use CSRF tokens. Audit entries include the actor when known, activity, affected record, timestamp, and IP address.
- Dentist accounts can create a backup from **Backup & Restore**. Archives contain database snapshots and application files, excluding `.env`, prior backups, and dependency folders. Restore replaces current application data and selected project files; always create a fresh backup before restoring.
- Enable the PHP Zip extension for backup and restore. Backups are stored under `storage/backups`, which is denied by the included Apache and IIS access rules. If using another web server, configure it to deny all direct requests to this directory.
- For an automated daily backup on Windows, create a Task Scheduler task that runs `C:\xampp\php\php.exe` with the argument `C:\xampp\htdocs\school-dental-system\database\backup.php`. Set the task to run under an account that can access the project and backup directory. Copy archives to separate protected storage regularly; a backup on the same disk is not sufficient disaster recovery.
- Local backup archives older than 30 days are automatically pruned when a new backup is created.
- Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the PHP server environment for deployment. Local defaults are provided for XAMPP. Do not use the sample accounts or development database credentials on a public server.
- HTTPS is required when hosted online. Configure a valid TLS certificate in the web server and redirect HTTP to HTTPS. The session cookie is marked `Secure` automatically when the request uses HTTPS.
- Before public deployment, remove or restrict access to `database/migrate.php` and `database/seed.php`, disable sample accounts, use least-privilege database credentials, and verify that backups cannot be downloaded anonymously.

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