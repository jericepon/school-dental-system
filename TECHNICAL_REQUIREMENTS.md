# Technical Requirements Document

## School Dental Records and Appointment Management System

| Document field | Value |
| --- | --- |
| Document type | Technical Requirements Document (TRD) |
| Project | School Dental Records and Appointment Management System |
| Version | 1.0 |
| Prepared | November 5, 2026 |
| Status | College project submission |

## 1. Purpose

This document describes the technical scope, requirements, data model, security controls, deployment environment, and acceptance criteria for the School Dental Records and Appointment Management System. It is based on the source code and setup guidance in this repository. It is intended to support project implementation, review, testing, and academic evaluation.

The system is an educational web application for a school or college dental clinic. It provides students with appointment requests and access to their own dental history, and provides dentists or clinic staff with appointment, record, reporting, audit, and backup workflows.

## 2. Scope

### 2.1 Included

- Student account registration, sign-in, and sign-out.
- Role-specific student and dentist/clinic-staff pages.
- Student appointment requests and appointment-status tracking.
- Dentist appointment filtering and status management.
- Dental examination record creation, viewing, editing, and deletion by dentists.
- Student access to their own dental history.
- Dentist dashboards, appointment and record summaries, activity history, and print-friendly reports.
- Protected backup creation and restoration for database snapshots and application files.

### 2.2 Outside the current project scope

- Patient self-service diagnosis or treatment advice.
- Online payments, insurance processing, pharmacy, laboratory, or electronic health-record integrations.
- Email, SMS, or push notifications.
- Multiple clinic branches, appointment capacity management, or automated scheduling optimization.
- A native mobile application or public API.
- Production healthcare compliance certification. The application is an educational project and must undergo independent security, privacy, and legal review before handling real patient information.

## 3. Users and Permissions

| User role | Capabilities |
| --- | --- |
| Student | Register and sign in; request appointments; view and filter their appointments; view their own dental history; delete an appointment only where the application permits it. |
| Dentist / clinic staff | Review and filter student appointments; update appointment status; manage dental records; view dashboards, reports, and activity history; create or restore backups. |

Access to student and dentist pages is guarded by authenticated session and role checks. Student-facing data operations must be scoped to the signed-in student's ID. Dentist operations are restricted to the dentist role.

## 4. System Overview and Architecture

### 4.1 Architecture

The system uses a server-rendered, request/response web architecture:

1. A browser submits an HTTP request to a PHP page served by Apache or another compatible web server.
2. PHP performs session, authorization, input-validation, and application logic.
3. PHP accesses MySQL through PDO using prepared statements for parameterized input.
4. The server returns HTML, with CSS and JavaScript assets used for presentation and client-side behavior.

The application is organized into shared configuration and security helpers, authentication pages, student pages, dentist pages, and database setup scripts. No PHP framework or JavaScript application framework is required by the current source. Lucide icons are loaded from a version-pinned public CDN.

### 4.2 Technology Stack

| Layer | Technology / requirement |
| --- | --- |
| Server-side application | PHP 8.0 or later |
| Database | MySQL-compatible database, accessed through PDO MySQL |
| Web server | Apache through XAMPP is the documented local setup; an equivalent PHP-capable web server may be used if configured correctly |
| Front end | HTML, CSS, and JavaScript |
| Backup archive | PHP Zip extension (`ZipArchive`) |
| Browser | Current desktop or mobile browser with HTML5 and JavaScript support |

## 5. Functional Requirements

The implementation column indicates whether the requirement is present in the repository as reviewed for this document. It is not a claim that the system has passed runtime testing.

| ID | Requirement | Acceptance condition | Implementation status |
| --- | --- | --- | --- |
| FR-01 | Account authentication | A user can sign in with email and password; passwords are verified using PHP's password API; successful sign-in routes the user to the page for their role. | Implemented |
| FR-02 | Student registration and sign-out | A visitor can register with a name, unique valid email, and password of at least 10 characters. Sign-out is a CSRF-protected POST request. | Implemented |
| FR-03 | Role-specific access | A student cannot access dentist pages, and a dentist cannot access student pages without the correct authenticated role. | Implemented |
| FR-04 | Student appointment request | A student can submit a preferred date, time, and reason. The date must be today or later, and the reason is limited to 255 characters. New requests start as `Pending`. | Implemented |
| FR-05 | Student appointment view | A student can filter and review their own appointment requests and statuses. A student can remove only their own appointments in `Pending` or `Cancelled` status. | Implemented |
| FR-06 | Dentist appointment management | A dentist can view requests, filter by date range and status, search by student name/email or reason, and set a request to `Pending`, `Confirmed`, `Completed`, or `Cancelled`. | Implemented |
| FR-07 | Dental record management | A dentist can create, view, edit, and delete examination records. A record includes student, dentist, examination date, diagnosis, treatment, and optional remarks. | Implemented |
| FR-08 | Student dental history | A student can review their own examination history and associated diagnosis, treatment, and recommendations. | Implemented |
| FR-09 | Clinic dashboard and reports | Dentist views include appointment and record statistics, monthly summaries for six months, and print-friendly report output. | Implemented |
| FR-10 | Activity history | Significant authentication, appointment, dental-record, and backup actions are recorded with available user, entity, timestamp, and IP context. | Implemented |
| FR-11 | Backup and restoration | An authorized dentist can create a ZIP backup containing a JSON database snapshot and eligible project files, and restore a valid supported archive. | Implemented |

## 6. Data Requirements

The migration script creates the following tables in the `johnwincellopez_schooldental` database. IDs are auto-incrementing primary keys unless otherwise noted. Timestamps use database defaults.

| Table | Data and constraints |
| --- | --- |
| `users` | `id`, `name`, unique `email`, password hash, role (`student` or `dentist`), and `created_at`. |
| `appointments` | `id`, student and optional dentist user IDs, appointment date/time, reason, status (`Pending`, `Confirmed`, `Completed`, or `Cancelled`), and `created_at`. Student deletion cascades to associated appointments; dentist deletion clears the optional dentist reference. |
| `dental_records` | `id`, student and dentist user IDs, checkup date, diagnosis, treatment, optional remarks, and `created_at`. User deletion cascades to associated records. |
| `activity_logs` | `id`, optional user ID and user name, action, optional entity and entity ID, IP address, and `created_at`. Deleting a user preserves the log and clears its user reference. |
| `login_attempts` | `id`, email, IP address, and attempt timestamp; indexed by email, IP address, and time for throttling checks. |

The database connection uses the `utf8mb4` character set. Database connection values are read from `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`; local defaults are supplied for XAMPP. Production credentials must be provided through the server environment and must not use sample credentials.

## 7. Security and Privacy Requirements

### 7.1 Controls implemented in the codebase

- Store passwords using `password_hash()` and verify them using `password_verify()`.
- Regenerate the session ID after successful authentication.
- Configure session cookies as `HttpOnly`, `SameSite=Lax`, and `Secure` when HTTPS is detected.
- Validate CSRF tokens on state-changing forms.
- Use prepared PDO statements for user-supplied values in database queries.
- Escape dynamic HTML output using UTF-8-aware `htmlspecialchars()`.
- Present an arithmetic challenge at sign-in and block sign-in after five failed attempts for the same email/IP pair within a 15-minute window.
- Record selected security and application events in the activity log.
- Store backup archives under `storage/backups`, with Apache and IIS access-denial configuration provided for that directory.
- Validate backup filename, format, database tables, permitted restore paths, and file hashes during restoration.

### 7.2 Deployment security requirements

- Use HTTPS for any non-local deployment and verify that the web server redirects HTTP to HTTPS.
- Use a least-privilege database account and strong, environment-provided credentials.
- Remove or restrict public access to `database/migrate.php` and `database/seed.php` after setup. The seed endpoint is explicitly restricted to loopback requests; the migration endpoint should still be removed or restricted in deployed environments.
- Disable sample accounts and change all sample passwords before any deployment beyond a local classroom environment.
- Configure web-server access denial for `storage/backups` if the server is not Apache or IIS, and restrict filesystem permissions on the backup directory.
- Keep backup copies in separate protected storage. A backup on the same host is not disaster recovery.
- Limit access to dental and audit data to authorized users; define institutional retention, deletion, and incident-response policies before using real student information.
- Treat backup archives as sensitive: they contain password hashes, student records, audit data, and application files.

The application does not by itself establish regulatory compliance, a production threat model, or a formal data-retention policy. The arithmetic challenge and login throttling are basic controls and should not be considered a substitute for a broader security review.

## 8. Non-Functional Requirements

| ID | Requirement |
| --- | --- |
| NFR-01 Usability | Common student and dentist workflows shall be available through role-specific navigation and forms with server-side validation feedback. |
| NFR-02 Responsive interface | Pages shall remain usable on desktop and mobile viewport sizes. |
| NFR-03 Accessibility | Form controls shall have programmatic labels; status and error information shall be available as text, and pages shall support keyboard operation. Accessibility should be checked against WCAG 2.1 AA before production use. |
| NFR-04 Maintainability | Shared session, CSRF, escaping, database, layout, and analytics logic shall remain in the existing shared modules rather than being duplicated across pages. |
| NFR-05 Data integrity | Foreign keys and constrained role/status fields shall preserve relationships and valid values. User input shall be validated on the server regardless of browser-side validation. |
| NFR-06 Recoverability | Authorized staff shall be able to create and restore supported backups. A restore must reject malformed, incomplete, or path-manipulating archives. |
| NFR-07 Compatibility | The application shall run on a PHP 8.0+ deployment with PDO MySQL and Zip enabled, and a supported MySQL-compatible database. |
| NFR-08 Performance | For the intended classroom-scale dataset, common page requests, filtering, and report calculations should complete without noticeable delay. Production-scale targets require representative load testing and are not defined by the current project. |

## 9. Installation and Operational Requirements

### 9.1 Local setup

1. Install XAMPP or an equivalent environment with PHP 8.0+, Apache, MySQL, PDO MySQL, and PHP Zip enabled.
2. Place the project in the web server's document root (for XAMPP, `htdocs/school-dental-system`).
3. Start the web server and database service.
4. Configure database environment variables if not using local defaults.
5. Run `database/migrate.php` to create the database and tables.
6. Run `database/seed.php` locally if sample accounts and demonstration data are needed.
7. Open the project root through the local web server.

### 9.2 Backup operations

The application creates ZIP archives with a JSON database snapshot and a manifest of included application-file hashes. The archive excludes `.env`, prior storage/backups, `.git`, `vendor`, and `node_modules`. Local backups older than 30 days are pruned when a new backup is created. Automated daily backups may be scheduled through Windows Task Scheduler using the PHP CLI; copied off-host backups remain necessary for recovery from host or disk loss.

## 10. Acceptance and Verification

The project is acceptable for its stated educational scope when the following checks pass in a configured local environment:

1. Run the migration on an empty database and confirm that all five tables and their declared foreign keys are created.
2. Run the seed script from localhost and sign in with the generated student and dentist test accounts. Confirm that registration rejects a duplicate/invalid email and a password shorter than 10 characters, and that sign-out requires a valid CSRF token.
3. Confirm that unauthenticated users are redirected to sign-in and that each role is denied access to the other role's protected pages.
4. As a student, create an appointment with a valid future date; verify invalid dates and missing or overlong reasons are rejected; confirm the student can view only their own appointments and history and can remove only their own pending or cancelled requests.
5. As a dentist, filter appointments, update statuses, and create, edit, view, and delete a dental record; confirm changes are reflected in student history and reports as appropriate.
6. Submit a state-changing form without a valid CSRF token and confirm the change is rejected.
7. Verify login failure throttling after five failed attempts for one email/IP pair within 15 minutes, and verify that successful sign-in clears the corresponding attempts.
8. Create a backup, verify the archive includes the database snapshot and manifest, then restore it in a disposable test environment and confirm the restored data and files are usable.
9. Confirm backup files cannot be downloaded anonymously through the configured web server.
10. Review the main workflows at desktop and mobile viewport sizes and check keyboard access and readable validation messages.

These are recommended verification procedures derived from the requirements; the document does not imply they have been executed.

## 11. Assumptions and Constraints

- The intended initial deployment is a locally hosted college project using XAMPP or a comparable PHP/MySQL stack.
- The database is MySQL-compatible and supports the SQL features used by the migration and reporting code.
- The project has no defined production availability, data-volume, or response-time SLA.
- Sample accounts and demonstration clinical information are for local testing only.
- Before real student health data is entered, the institution must approve the deployment, access policy, hosting, retention, privacy, and security arrangements.

## 12. Source References

This document was prepared from the implementation and setup guidance in:

- `README.md` - project overview, installation, and operational notes.
- `_product_requirements_content.php` - product scope, roles, and acceptance expectations.
- `config/database.php` and `config/security.php` - database and shared security behavior.
- `database/migrate.php` - schema and relationships.
- `auth/`, `student/`, and `dentist/` - authentication and role-specific workflows.
- `config/backup.php` and `dentist/backup.php` - backup and restore behavior.
