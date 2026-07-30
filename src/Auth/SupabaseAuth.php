<?php
require_once __DIR__ . '/../Api/SupabaseClient.php';
require_once __DIR__ . '/../Helpers/functions.php';

class SupabaseAuth {

    static function sign_up($email, $password) {
        $supabase = new SupabaseClient();
        return $supabase->sign_up($email, $password);
    }

    static function sign_in($email, $password) {
        $supabase = new SupabaseClient();
        $response = $supabase->sign_in($email, $password);
        self::store_session($response);
        return $response;
    }

    static function sign_out() {
        if (isset($_SESSION['access_token'])) {
            $supabase = new SupabaseClient();
            $supabase->setAccessToken($_SESSION['access_token']);
            try {
                $supabase->sign_out();
            } catch (Exception $e) {
                error_log("Sign out API call failed: " . $e->getMessage());
            }
        }

        unset($_SESSION['access_token']);
        unset($_SESSION['refresh_token']);
        unset($_SESSION['expires_at']);
        unset($_SESSION['user']);
    }

    static function is_logged_in() {
        return isset($_SESSION['access_token']) && isset($_SESSION['user']);
    }

    static function require_auth() {
        if (!self::is_logged_in()) {
            header('Location: /login.php');
            exit;
        }

        if (self::token_needs_refresh()) {
            $refreshed = self::try_refresh();
            if (!$refreshed) {
                self::sign_out();
                $_SESSION['flash_error'] = 'Session expired, please log in again';
                header('Location: /login.php');
                exit;
            }
        }
    }

    static function token_needs_refresh() {
        $expires_at = $_SESSION['expires_at'] ?? 0;
        $buffer = 60;
        return time() >= ($expires_at - $buffer);
    }

    static function try_refresh() {
        if (!isset($_SESSION['refresh_token'])) {
            return false;
        }

        $supabase = new SupabaseClient();
        try {
            $response = $supabase->refresh($_SESSION['refresh_token']);
        } catch (Exception $e) {
            error_log("Token refresh failed: " . $e->getMessage());
            return false;
        }

        if (!isset($response['access_token'])) {
            return false;
        }

        self::store_session($response);
        return true;
    }

    static function store_session($response) {
        $_SESSION['access_token'] = $response['access_token'];
        $_SESSION['refresh_token'] = $response['refresh_token'];
        $_SESSION['expires_at'] = time() + $response['expires_in'];

        if (isset($response['user'])) {
            $_SESSION['user'] = [
                'id' => $response['user']['id'],
                'email' => $response['user']['email']
            ];
        }
    }

    static function current_user_id() {
        return $_SESSION['user']['id'] ?? null;
    }

    static function current_access_token() {
        return $_SESSION['access_token'] ?? null;
    }
}