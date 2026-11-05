<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Product requirements', 'help', 'Product requirements and operating rules for the School Dental Care system.');
require __DIR__ . '/../_product_requirements_content.php';
render_dentist_layout_end();