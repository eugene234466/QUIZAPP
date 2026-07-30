
<?php

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/Auth/SupabaseAuth.php';

// already logged in? no need to see the login form
if (SupabaseAuth::is_logged_in()) {
    header('Location: /dashboard.php');
    exit;
}

$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required';
    } else {
        try {
            $response = SupabaseAuth::sign_in($email, $password);
            header('Location: /dashboard.php');
            exit;
        } catch (Exception $e) {
            error_log("Login failed: " . $e->getMessage());
            $error = 'Invalid email or password';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Log In</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<h1>Log In</h1>

<?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST" action="/login.php">
    <label>Email
        <input type="email" name="email" required>
    </label><br>
    <label>Password
        <input type="password" name="password" required>
    </label><br>
    <button type="submit">Log In</button>
</form>

<p>Don't have an account? <a href="/signup.php">Sign up</a></p>

</body>
</html>
