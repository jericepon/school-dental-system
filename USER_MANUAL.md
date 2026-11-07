# School Dental Care User Manual

This manual explains the student and dentist/clinic-staff workflows in the School Dental Records and Appointment Management System. For local installation and database setup, see [README.md](README.md).

## 1. Access and sign-in

1. Open the system address provided by your school or administrator. On a local XAMPP installation, this is commonly `http://localhost/school-dental-system/`.
2. Select **Sign in** and enter your registered email, password, and the answer to the arithmetic security check. Your role determines which portal opens.
3. Students can select **Register** to create an account using their name, a valid, unused email address, and a password of at least 10 characters. Dentist accounts are provided by the system administrator; public registration creates student accounts only.
4. Five failed sign-in attempts for the same email and IP address within 15 minutes temporarily block further attempts. Check your credentials and retry after the lockout period.
5. Select **Sign out** when finished. Use the portal navigation to move between sections. The theme control switches between light and dark appearance.

## 2. Student portal

### Overview

The Overview page summarizes your appointment activity, upcoming pending or confirmed visits, and recent dental records. Use the navigation to book a visit, review requests, or open your full dental history.

### Request an appointment

1. Open **Book a visit**.
2. Enter a preferred date (today or later), time, and reason for the visit. The reason is limited to 255 characters.
3. Submit **Request appointment**. The request is saved with **Pending** status until clinic staff updates it.
4. Open **Appointments** to check for status changes. The system does not send email, text, or push notifications, so check the portal for updates.

### Review or remove appointment requests

In **Appointments**, use the date range and status filters, then select **Apply filters**. Choose **Reset** to show all your requests again. The table shows each visit's date, time, reason, and current status. Use **Print** for a print-friendly list.

Statuses mean:

- **Pending**: the clinic has not yet finalized the request.
- **Confirmed**: the clinic has accepted the visit request.
- **Completed**: the visit is marked as completed.
- **Cancelled**: the visit is marked as cancelled by the clinic.

You can remove only your own **Pending** or **Cancelled** requests. Confirm the removal when prompted. Confirmed and completed requests cannot be removed from the student portal; contact the clinic if a change is needed.

### View dental history

Open **Dental history** to review examination date, dentist, diagnosis, treatment rendered, and any clinical remarks or recommendations recorded for you. This page is read-only for students. Contact the clinic if a record appears to need correction.

## 3. Dentist / clinic-staff portal

### Overview and clinic activity

The Overview page provides a snapshot of clinic appointments and records. **Statistics & charts** shows appointment totals by status and monthly appointment and record counts for the displayed six-month period; use **Print charts** for a print-friendly copy. **Summary report** shows monthly totals and period totals for appointments, their statuses, and dental records; select **Print report** to print it.

### Review and update appointments

1. Open **Appointments** to review student requests.
2. Narrow the list with a date range, status, or a search for student name, email, or visit reason. Select **Apply filters**; use **Reset** to clear filters.
3. For the appropriate request, select **Pending**, **Confirmed**, **Completed**, or **Cancelled** in its status control and select **Save**. The update is recorded in the system activity history.

Update statuses to reflect the clinic's decision and visit outcome. Appointment requests are not automatically scheduled or capacity-checked by the system; coordinate the actual time with the student according to clinic procedures.

### Create and manage dental records

1. Select **New record** and choose the student.
2. Enter the examination date (today or earlier), diagnosis, and treatment rendered. Add clinical remarks or recommendations if appropriate. Student, date, diagnosis, and treatment are required.
3. Select **Save dental record**. The record becomes visible in that student's Dental history.
4. In **Dental records**, filter by date range and/or student. Select **Edit** to correct or update a record, then save the changes. Select **Delete** and confirm only when the record should be removed; deletion is permanent.

Enter accurate clinical information and follow the institution's privacy and recordkeeping procedures. Students can view their own records but cannot edit them.

### Review activity

Open **Activity** to review the 100 most recent recorded actions, including the user, action, time, and related item when available. This is an audit view, not a complete export of all system history.

### Back up and restore

1. Open **Backup & Restore** and select **Create backup**. The system creates a downloadable archive containing a database snapshot and eligible application files.
2. Download archives to approved, access-controlled storage. Treat them as sensitive information: they contain student data and must not be shared publicly. Backups stored only on this server do not protect against loss of the server or disk.
3. To restore, first create and securely store a fresh backup of the current system. Choose the intended archive and select **Restore** only when certain it is the correct compatible backup.
4. Confirm the restore prompt. Restore replaces current users, appointments, dental records, activity history, login attempts, and included application files. Do not interrupt the restore. If it fails, check with the system administrator; backup operations require the PHP Zip extension.

## 4. General guidance

- Use only your own account and sign out on shared devices. The system enforces student and dentist role access.
- Keep student and clinical information confidential and use the system only under your institution's policies.
- If a page reports an expired session or security-token error, reload the page, sign in again if needed, and resubmit the form.
- If you cannot sign in, verify your email and password, solve the current arithmetic check, and wait 15 minutes after repeated failed attempts. Contact the system administrator for account access issues.
- This is an educational project and does not provide diagnosis, treatment advice, or production healthcare compliance certification. The clinic's professional judgment and institutional procedures remain authoritative.