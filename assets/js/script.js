// assets/js/script.js
document.addEventListener("DOMContentLoaded", function () {
    console.log("School Dental System JavaScript Initialized.");

    // Prevent selecting past dates for dental appointment booking
    const dateInput = document.querySelector('input[type="date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
});