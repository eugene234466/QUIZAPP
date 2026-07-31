<?php

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Auth/SupabaseAuth.php';
require_once __DIR__ . '/../src/Api/SupabaseClient.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

SupabaseAuth::require_auth();

$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$topics = ["Language", "Math", "Science", "Geography", "History", "Sports", "Pop Culture", "Engineering", "Politics", "Signals & Systems", "DSA"];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<h1>Choose a Topic</h1>

<?php if ($flash_error): ?>
    <p class="error"><?php echo htmlspecialchars($flash_error); ?></p>
<?php endif; ?>

<form action="/quiz.php" method="GET">
    <?php foreach ($topics as $topic): ?>
        <label>
            <input type="radio" name="topic" value="<?php echo htmlspecialchars($topic); ?>" required>
            <?php echo htmlspecialchars($topic); ?>
        </label><br>
    <?php endforeach; ?>

    <button type="submit">Start Quiz</button>
</form>

<p><a href="/history.php">View past results</a> | <a href="/logout.php">Log out</a></p>

</body>
</html>