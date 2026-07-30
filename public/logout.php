<?php

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/Auth/SupabaseAuth.php';

SupabaseAuth::sign_out();

header('Location: /login.php');
exit;