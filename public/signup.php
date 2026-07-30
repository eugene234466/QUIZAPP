<?php

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Auth/SupabaseAuth.php';

SupabaseAuth::require_auth();

$result = $_SESSION["last_result"] ?? null;
$was_saved = $_SESSION["last_result_saved"] ?? true;

if ($result === null) {
    header('Location: /dashboard.php');
    exit;
}

unset($_SESSION['last_result']);
unset($_SESSION['last_result_saved']);

$percentage = round(($result["score"] / $result["total_questions"]) * 100);
$time_display = floor($result["time_taken_sec"] / 60) . ":" . str_pad($result["time_taken_sec"] % 60, 2, '0', STR_PAD_LEFT);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Results</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="result-card">
    <p class="result-eyebrow"><?php echo htmlspecialchars($result["topic"]); ?> quiz complete</p>
    <div class="result-score"><?php echo htmlspecialchars($result['score']); ?>/<?php echo htmlspecialchars($result['total_questions']); ?></div>
    <p class="result-subline"><?php echo htmlspecialchars($percentage); ?>% correct · finished in <?php echo htmlspecialchars($time_display); ?></p>

    <?php if (!$was_saved): ?>
        <p class="result-warning">This result couldn't be saved to your history</p>
    <?php endif; ?>

    <div class="result-actions">
        <a href="/dashboard.php" class="primary">Play again</a>
        <a href="/history.php" class="secondary">View history</a>
    </div>
</div>

</body>
</html>