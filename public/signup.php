<?php

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/Auth/SupabaseAuth.php';

if (SupabaseAuth::is_logged_in()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            SupabaseAuth::sign_up($email, $password);
            $success = true;
        } catch (Exception $e) {
            error_log("Signup failed: " . $e->getMessage());
            $error = 'Could not create account — email may already be in use';
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<h1>Sign Up</h1>

<?php if ($success): ?>
    <p>Account created! Check your email to confirm your address before logging in.</p>
    <p><a href="/login.php">Go to login</a></p>
<?php else: ?>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="/signup.php">
        <label>Email
            <input type="email" name="email" required>
        </label><br>
        <label>Password
            <input type="password" name="password" required minlength="6">
        </label><br>
        <label>Confirm Password
            <input type="password" name="confirm_password" required minlength="6">
        </label><br>
        <button type="submit">Sign Up</button>
    </form>

    <p>Already have an account? <a href="/login.php">Log in</a></p>

<?php endif; ?>

</body>
</html>
