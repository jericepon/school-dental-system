<div class="prd-page">
    <div class="prd-toolbar no-print">
        <p>Product requirements document</p>
        <button type="button" class="button-secondary" onclick="window.print()"><i data-lucide="printer"></i>Print document</button>
    </div>

    <nav class="prd-toc" aria-label="Document sections">
        <a href="#product">Product</a>
        <a href="#users">Users</a>
        <a href="#requirements">Requirements</a>
        <a href="#security">Security</a>
        <a href="#acceptance">Acceptance</a>
    </nav>

    <article class="prd-document">
        <section id="product" aria-labelledby="product-title">
            <p class="eyebrow">PRD · School Dental Care</p>
            <h2 id="product-title">Product overview</h2>
            <p>School Dental Care is a role-based web application for campus dental clinics. It supports student appointment requests, dentist scheduling and examination records, operational reporting, activity auditing, and system recovery.</p>
            <dl class="prd-facts">
                <div><dt>Product goal</dt><dd>Make routine student dental-care transactions manageable, traceable, and recoverable.</dd></div>
                <div><dt>Platform</dt><dd>Responsive PHP and MySQL web application.</dd></div>
                <div><dt>Primary outcome</dt><dd>Students can request and review care while dentists can manage appointments and clinical records.</dd></div>
            </dl>
        </section>

        <section id="users" aria-labelledby="users-title">
            <h2 id="users-title">Users and permissions</h2>
            <div class="table-scroll">
                <table class="report-table">
                    <thead><tr><th>Role</th><th>Primary needs</th><th>Permissions</th></tr></thead>
                    <tbody>
                        <tr><td>Student</td><td>Request appointments and review personal care history.</td><td>Manage own appointment requests where permitted; read own appointments and dental records.</td></tr>
                        <tr><td>Dentist / clinic staff</td><td>Coordinate visits, document examinations, and monitor clinic activity.</td><td>Manage appointments, create/edit/delete dental records, view reports, audit history, and manage backups.</td></tr>
                    </tbody>
                </table>
            </div>
            <h3>Student journey</h3>
            <ol>
                <li>Sign in or create a student account.</li>
                <li>Submit an appointment request with a date, time, and reason.</li>
                <li>Track the request and remove it only while it is pending or cancelled.</li>
                <li>Review diagnoses, treatment, and recommendations entered by clinic staff.</li>
            </ol>
            <h3>Dentist journey</h3>
            <ol>
                <li>Review requests and update appointment status.</li>
                <li>Create, edit, or remove a student's examination record.</li>
                <li>Review statistics, monthly charts, summary reports, and activity history.</li>
                <li>Create or restore a protected system backup when needed.</li>
            </ol>
        </section>

        <section id="requirements" aria-labelledby="requirements-title">
            <h2 id="requirements-title">Functional requirements</h2>
            <div class="table-scroll">
                <table class="report-table">
                    <thead><tr><th>ID</th><th>Requirement</th><th>Expected behavior</th></tr></thead>
                    <tbody>
                        <tr><td>FR-01</td><td>Authentication</td><td>Users sign in with email and password; student registration creates student-role accounts.</td></tr>
                        <tr><td>FR-02</td><td>Role dashboards</td><td>Each role receives a focused overview and navigation to its own workflows.</td></tr>
                        <tr><td>FR-03</td><td>Appointment management</td><td>Students request and track appointments; dentists filter requests and update status.</td></tr>
                        <tr><td>FR-04</td><td>Dental records</td><td>Dentists create, view, edit, and delete examination records; students can view their own history.</td></tr>
                        <tr><td>FR-05</td><td>Reports</td><td>Dentists view statistics, monthly charts, a six-month summary, and browser print output.</td></tr>
                        <tr><td>FR-06</td><td>Filtering</td><td>Appointment and record lists support applicable date, status, student, and text filters.</td></tr>
                        <tr><td>FR-07</td><td>Activity history</td><td>Important authentication, appointment, record, and backup actions are recorded with user and time context.</td></tr>
                        <tr><td>FR-08</td><td>Backup and restore</td><td>Authorized dentists create and restore protected backups of application files and database data.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="security" aria-labelledby="security-title">
            <h2 id="security-title">Security and quality requirements</h2>
            <ul>
                <li>Enforce role checks on protected pages and scope student data to the signed-in student.</li>
                <li>Hash passwords with PHP's password hashing API; never store plaintext passwords.</li>
                <li>Use CSRF tokens on state-changing forms and prepared statements for database queries.</li>
                <li>Use an arithmetic CAPTCHA and throttle repeated failed login attempts.</li>
                <li>Escape untrusted values when rendering HTML and record significant actions in the audit log.</li>
                <li>Protect backup files from direct public access; require HTTPS when deployed online.</li>
                <li>Keep the interface responsive, keyboard accessible, and readable in light and dark themes.</li>
            </ul>
        </section>

        <section id="acceptance" aria-labelledby="acceptance-title">
            <h2 id="acceptance-title">Acceptance criteria</h2>
            <ul class="prd-checklist">
                <li>Students can register, sign in, request appointments, filter their requests, and view their own dental records.</li>
                <li>Dentists can update appointment status and perform full dental-record CRUD operations.</li>
                <li>Reports display all-time statistics, monthly appointment and record charts, and six months of status totals.</li>
                <li>State-changing requests reject missing or invalid CSRF tokens; student appointment deletion cannot affect another student.</li>
                <li>Authentication failures are challenged and rate-limited; significant actions are auditable.</li>
                <li>Authorized dentists can create backups and restore a valid backup.</li>
                <li>Protected pages remain usable at desktop and mobile widths in either theme.</li>
            </ul>
        </section>
    </article>
</div>