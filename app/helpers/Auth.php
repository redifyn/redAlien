<?php

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user_id'])
            ? (int) $_SESSION['user_id']
            : null;
    }

    public static function userName(): string
    {
        return $_SESSION['full_name'] ?? 'User';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::error('Please sign in to continue.');

            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: ' . BASE_URL . '/home/index');
            exit;
        }
    }

    public static function logout(): void
    {
        // Remove all session variables
        $_SESSION = [];

        // Remove session cookie if one exists
        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
    }
}