<?php

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Auth/SupabaseAuth.php';
require_once __DIR__ . '/../src/Quiz/QuizSession.php';
require_once __DIR__ . '/../src/Quiz/QuizScorer.php';
require_once __DIR__ . '/../src/Api/SupabaseClient.php';
require_once __DIR__ . '/../src/Helpers/functions.php';


// 1. AUTH GUARD
SupabaseAuth::require_auth();

// 2. VALIDATE REQUEST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard.php');
    exit;
}

if (!QuizSession::exists()) {
    $_SESSION['flash_error'] = 'No active quiz — start a new one';
    header('Location: /dashboard.php');
    exit;
}

// 3. PULL SUBMITTED ANSWERS
$submitted_answers = $_POST['answer'] ?? [];

// 4. ENFORCE TIME LIMIT — late = zero credit, quiz discarded
$elapsed = QuizSession::elapsed_seconds();
$grace_period = 10;

if ($elapsed > (APP_TIME_LIMIT_SECONDS + $grace_period)) {
    QuizSession::clear();
    $_SESSION['flash_error'] = 'Time limit exceeded — quiz discarded';
    header('Location: /dashboard.php');
    exit;
}

// 5. SCORE
$current_user_id = $_SESSION['user']['id'] ?? null;

try {
    $result = QuizScorer::score($current_user_id, $submitted_answers);
} catch (Exception $e) {
    error_log("Scoring failed: " . $e->getMessage());
    QuizSession::clear();
    $_SESSION['flash_error'] = 'Something went wrong scoring your quiz';
    header('Location: /dashboard.php');
    exit;
}

// 6. PERSIST TO SUPABASE — failure doesn't block showing the result
$save_failed = false;
$supabase = new SupabaseClient();
$supabase->setAccessToken($_SESSION['access_token'] ?? null);

try {
    $supabase->insert('quiz_results', $result);
} catch (Exception $e) {
    error_log("Insert failed: " . $e->getMessage());
    $save_failed = true;
}

// 7. CLEAR SESSION — always, regardless of save outcome
QuizSession::clear();

// 8. SHOW RESULTS
$_SESSION['last_result'] = $result;
$_SESSION['last_result_saved'] = !$save_failed;
header('Location: /results.php');
exit;