<?php


class QuizScorer {

    static function score($user_id, $submitted_answers) {
        if (!QuizSession::exists()) {
            throw new Exception("No active quiz session to score against");
        }

        $questions = QuizSession::get_questions();
        $topic = QuizSession::get_topic();
        $elapsed = QuizSession::elapsed_seconds();

        if (count($submitted_answers) !== count($questions)) {
            throw new Exception("Answer count does not match question count");
        }

        $score = 0;
        foreach ($questions as $i => $question) {
            $submitted = $submitted_answers[$i] ?? null;
            if ($submitted !== null && (int)$submitted === $question['correct_index']) {
                $score++;
            }
        }

        $result = [
            "user_id" => $user_id,
            "topic" => $topic,
            "score" => $score,
            "total_questions" => count($questions),
            "time_taken_sec" => $elapsed
        ];

        return $result;
    }
}