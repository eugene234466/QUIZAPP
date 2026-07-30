<?php

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/Auth/SupabaseAuth.php';

if (SupabaseAuth::is_logged_in()) {
    header('Location: /dashboard.php');
} else {
    header('Location: /login.php');
}
exit;