<?php
require_once __DIR__ . '/../Helpers/functions.php';

class SupabaseClient {
    protected $url;
    protected $anon_key;
    protected $access_token = null;

    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->anon_key = SUPABASE_ANON_KEY;
    }

    public function setAccessToken($token) {
        $this->access_token = $token;
    }

    protected function headers($include_auth = true) {
        $headers = [
            'apikey' => $this->anon_key,
            'Content-Type' => 'application/json',
        ];
        if ($include_auth && $this->access_token) {
            $headers['Authorization'] = 'Bearer ' . $this->access_token;
        } else {
            $headers['Authorization'] = 'Bearer ' . $this->anon_key;
        }
        return $headers;
    }

    public function sign_up($email, $password) {
        return $this->post('/auth/v1/signup', [
            'email' => $email,
            'password' => $password
        ], false);
    }

    public function sign_in($email, $password) {
        return $this->post('/auth/v1/token?grant_type=password', [
            'email' => $email,
            'password' => $password
        ], false);
    }

    public function sign_out() {
        return $this->post('/auth/v1/logout', []);
    }

    public function insert($table, $row) {
        return $this->post('/rest/v1/' . $table, $row);
    }

    public function select($table, $filters = [], $order_by = null) {
        $query = build_query_string($filters, $order_by);
        return $this->get('/rest/v1/' . $table . $query);
    }

    protected function post($path, $body, $include_auth = true) {
        $response = curl_request(
            'POST',
            $this->url . $path,
            $this->headers($include_auth),
            json_encode($body)
        );
        return $this->parse_response($response);
    }

    protected function get($path) {
        $response = curl_request(
            'GET',
            $this->url . $path,
            $this->headers()
        );
        return $this->parse_response($response);
    }

    protected function parse_response($response) {
        $decoded = json_decode($response['body'], true);
        if ($response['http_code'] >= 200 && $response['http_code'] < 300) {
            return $decoded;
        } else {
            throw new Exception("Error: " . $response['http_code'] . " - " . json_encode($decoded));
        }
    }

    function refresh($refresh_token) {
    return $this->post('/auth/v1/token?grant_type=refresh_token', [
        'refresh_token' => $refresh_token
    ], false);
}
}