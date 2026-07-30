<?php

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Auth/SupabaseAuth.php';
require_once __DIR__ . '/../src/Quiz/QuizGenerator.php';
require_once __DIR__ . '/../src/Quiz/QuizSession.php';
require_once __DIR__ . '/../src/Api/GroqClient.php';
require_once __DIR__ . '/../src/Api/SupabaseClient.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

SupabaseAuth::require_auth();

$topic = $_GET['topic'] ?? null;

$valid_topics = ["Language", "Math", "Science","Geography","History","Sports", "Pop Culture", "Engineering","Politics"];
if ($topic === null || !in_array($topic, $valid_topics)) {
    $_SESSION['flash_error'] = 'Please select a valid topic';
    header('Location: /dashboard.php');
    exit;
}

$generator = new QuizGenerator();

try {
    $client_quiz = $generator->create_quiz($topic, APP_QUESTIONS_PER_QUIZ, "mixed");
} catch (Exception $e) {
    error_log("Quiz generation failed: " . $e->getMessage());
    $_SESSION['flash_error'] = 'Could not generate a quiz right now — please try again';
    header('Location: /dashboard.php');
    exit;
}

$time_limit = APP_TIME_LIMIT_SECONDS;
$questions = $client_quiz['questions'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz: <?php echo htmlspecialchars($topic); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <h1><?php echo htmlspecialchars($topic); ?> Quiz</h1>
    <div id="timer" data-time-limit="<?php echo $time_limit; ?>"></div>

    <form id="quiz-form" action="/submit.php" method="POST">
        <?php foreach ($questions as $i => $q): ?>
            <div class="question">
                <p><?php echo htmlspecialchars($q['question']); ?></p>
                <?php foreach ($q['options'] as $j => $option): ?>
                    <label>
                        <input type="radio" name="answer[<?php echo $i; ?>]" value="<?php echo $j; ?>">
                        <span><?php echo htmlspecialchars($option); ?></span>
                    </label><br>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit">Submit Quiz</button>
    </form>

    <script src="/assets/js/timer.js"></script>
</body>
</html>