<?php
// index.php
require_once __DIR__ . '/config/security.php';
start_secure_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="School Dental Care connects students with campus dental appointments and clinic records.">
    <title>School Dental Records & Appointment System</title>
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" sizes="16x16 32x32 48x48">
    <script src="assets/js/script.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="landing-page">
        <header class="landing-header">
            <a class="landing-brand" href="index.php" aria-label="School Dental Care home">
                <img class="landing-logo" src="assets/img/logo.jpeg" alt="">
                <span class="landing-brand-copy"><strong>School Dental Care</strong><small>Campus clinic portal</small></span>
            </a>
            <nav class="landing-nav" aria-label="Main navigation">
                <!-- <a href="#student-care">Student care</a>
                <a href="#clinic-care">Clinic team</a> -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
                        <a class="button-link" href="student/dashboard.php">Open portal<i data-lucide="arrow-up-right"></i></a>
                    <?php else: ?>
                        <a class="button-link" href="dentist/dashboard.php">Open dashboard<i data-lucide="arrow-up-right"></i></a>
                    <?php endif; ?>
                    <form method="POST" action="auth/logout.php" class="no-print">
                        <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                        <button type="submit" class="button-secondary">Sign out</button>
                    </form>
                <?php else: ?>
                    <a href="auth/login.php">Sign in</a>
                    <a class="button-link" href="auth/register.php">Register<i data-lucide="arrow-up-right"></i></a>
                <?php endif; ?>
            </nav>
        </header>

        <main>
            <section class="landing-hero" aria-labelledby="landing-title">
                <img class="landing-hero-image" src="https://images.unsplash.com/photo-1606811971618-4486d14f3f99?auto=format&amp;fit=crop&amp;w=2200&amp;q=85" alt="Modern dental clinic prepared for a patient visit">
                <div class="landing-hero-shade"></div>
                <div class="landing-hero-content">
                    <p class="landing-kicker"><span></span>Care for your campus community</p>
                    <h1 id="landing-title">A healthier smile<br>starts here.</h1>
                    <p class="landing-lede">Request an appointment, follow your visit, and keep your dental care connected with your school clinic.</p>
                    <div class="landing-actions">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
                                <a class="button-link landing-primary" href="student/dashboard.php">Go to student portal<i data-lucide="arrow-right"></i></a>
                            <?php else: ?>
                                <a class="button-link landing-primary" href="dentist/dashboard.php">Go to clinic dashboard<i data-lucide="arrow-right"></i></a>
                            <?php endif; ?>
                            <span class="landing-welcome">Welcome back, <?php echo html_escape($_SESSION['name']); ?></span>
                        <?php else: ?>
                            <a class="button-link landing-primary" href="auth/login.php">Sign in to your portal<i data-lucide="arrow-right"></i></a>
                            <a class="landing-secondary" href="auth/register.php">Create a student account</a>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="landing-image-caption">Your campus dental clinic</span>
            </section>

            <section class="landing-paths" aria-labelledby="paths-title">
                <div class="landing-section-heading">
                    <p class="eyebrow">One connected clinic</p>
                    <h2 id="paths-title">Care that works for everyone.</h2>
                </div>
                <article class="landing-path" id="student-care">
                    <span class="landing-path-icon"><i data-lucide="calendar-check-2"></i></span>
                    <div><h3>For students</h3><p>Request a visit, keep track of appointment updates, and revisit your dental history when you need it.</p></div>
                    <a href="auth/login.php" aria-label="Sign in for student care"><i data-lucide="arrow-up-right"></i></a>
                </article>
                <article class="landing-path" id="clinic-care">
                    <span class="landing-path-icon"><i data-lucide="stethoscope"></i></span>
                    <div><h3>For the clinic team</h3><p>Coordinate appointment requests, document examinations, and review clinic activity and reports.</p></div>
                    <a href="auth/login.php" aria-label="Sign in for the clinic team"><i data-lucide="arrow-up-right"></i></a>
                </article>
            </section>
        </main>

        <footer class="landing-footer">
            <span>School Dental Care</span>
            <span>Campus clinic portal</span>
        </footer>
    </div>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</body>
</html>