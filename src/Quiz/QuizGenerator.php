<?php
require_once __DIR__ . '/QuizSession.php';
require_once __DIR__ . '/../Api/GroqClient.php';class QuizGenerator {
    protected $groq_client;

    public function __construct() {
        $this->groq_client = new GroqClient();
    }

    function create_quiz($topic, $num_questions, $difficulty) {
        $full_quiz = null;
        $last_error = null;

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            try {
                $full_quiz = $this->groq_client->generate_quiz($topic, $num_questions, $difficulty);
                break;
            } catch (Exception $e) {
                $last_error = $e;
                error_log("Quiz generation attempt $attempt failed: " . $e->getMessage());
            }
        }

        if ($full_quiz === null) {
            throw new Exception("Failed to generate quiz after 2 attempts: " . $last_error->getMessage());
        }

        QuizSession::store($topic, $full_quiz);

        return $this->strip_answers($full_quiz);
    }

    function strip_answers($full_quiz) {
        $client_quiz = ["questions" => []];

        foreach ($full_quiz['questions'] as $q) {
            $client_quiz['questions'][] = [
                "question" => $q['question'],
                "options" => $q['options']
            ];
        }

        return $client_quiz;
    }
}