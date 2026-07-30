<?php

class GroqClient {
    protected $api_key;
    protected $base_url = "https://api.groq.com/openai/v1/chat/completions";
    protected $model = "llama-3.3-70b-versatile";

    public function __construct() {
        $this->api_key = GROQ_API_KEY;
    }

    function generate_quiz($topic, $num_questions, $difficulty) {
        $prompt = $this->build_prompt($topic, $num_questions, $difficulty);

        $body = [
            "model" => $this->model,
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You are a quiz generator. Respond with only valid JSON, no markdown fences, no preamble."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.7,
            "response_format" => [
                "type" => "json_object"
            ]
        ];

        $headers = [
            "Authorization" => "Bearer " . $this->api_key,
            "Content-Type" => "application/json"
        ];

        $response = curl_request('POST', $this->base_url, $headers, json_encode($body));

        if ($response['http_code'] < 200 || $response['http_code'] >= 300) {
            throw new Exception("API request failed with status code: " . $response['http_code'] . " and response: " . $response['body']);
        }

        $decoded = json_decode($response['body'], true);
        $raw_content = $decoded['choices'][0]['message']['content'];

        $quiz_data = json_decode($raw_content, true);

        if ($quiz_data === null) {
            throw new Exception("Failed to decode JSON response: " . json_last_error_msg());
        }

        $this->validate_quiz_data($quiz_data, $num_questions);

        return $quiz_data;
    }

    function build_prompt($topic, $num_questions, $difficulty) {
        return "Generate $num_questions multiple choice quiz questions about $topic, difficulty level: $difficulty.
        Respond with only a JSON object in exactly this shape,
        no other text:
        {
          \"questions\": [
            {
              \"question\": \"...\",
              \"options\": [\"...\", \"...\", \"...\", \"...\"],
              \"correct_index\": 0
            }
          ]
        }
        Rules:
        - exactly $num_questions questions
        - exactly 4 options per question
        - correct_index is 0-based, pointing into options
        - questions should be general knowledge, factually accurate
        - do not repeat questions or options
        ";
    }

    function validate_quiz_data($quiz_data, $num_questions) {
        if (!isset($quiz_data['questions']) || !is_array($quiz_data['questions'])) {
            throw new Exception("Invalid quiz data: 'questions' key missing or not an array.");
        }

        if (count($quiz_data['questions']) !== $num_questions) {
            throw new Exception("Invalid quiz data: expected $num_questions questions, got " . count($quiz_data['questions']));
        }

        foreach ($quiz_data['questions'] as $i => $question) {
            if (!isset($question['question']) || !is_string($question['question'])) {
                throw new Exception("Invalid quiz data: question at index $i is missing or not a string.");
            }
            if (!isset($question['options']) || !is_array($question['options']) || count($question['options']) !== 4) {
                throw new Exception("Invalid quiz data: question at index $i must have exactly 4 options.");
            }
            if (!isset($question['correct_index']) || !is_int($question['correct_index']) || $question['correct_index'] < 0 || $question['correct_index'] > 3) {
                throw new Exception("Invalid quiz data: question at index $i has an invalid 'correct_index'.");
            }
        }
    }
}