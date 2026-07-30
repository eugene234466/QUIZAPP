<?php

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Auth/SupabaseAuth.php';
require_once __DIR__ . '/../src/Api/SupabaseClient.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

SupabaseAuth::require_auth();

$user_id = SupabaseAuth::current_user_id();

$supabase = new SupabaseClient();
$supabase->setAccessToken(SupabaseAuth::current_access_token());

$fetch_failed = false;

try {
    $results = $supabase->select('quiz_results',
        ['user_id' => $user_id],
        'created_at.desc'
    );
} catch (Exception $e) {
    error_log("Failed to fetch history: " . $e->getMessage());
    $results = [];
    $fetch_failed = true;
}

function format_time($seconds) {
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return $mins . ":" . str_pad($secs, 2, '0', STR_PAD_LEFT);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz History</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<h1>Quiz History</h1>

<?php if ($fetch_failed): ?>
    <p>Couldn't load your history right now — try again later.</p>
<?php elseif (empty($results)): ?>
    <p>No quizzes taken yet — go take one!</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Topic</th>
                <th>Score</th>
                <th>Time Taken</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <?php $percentage = round(($row['score'] / $row['total_questions']) * 100); ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['topic']); ?></td>
                    <td><?php echo htmlspecialchars($row['score']) . '/' . htmlspecialchars($row['total_questions']) . ' (' . htmlspecialchars($percentage) . '%)'; ?></td>
                    <td><?php echo htmlspecialchars(format_time($row['time_taken_sec'])); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="/dashboard.php">Take another quiz</a></p>

</body>
</html>