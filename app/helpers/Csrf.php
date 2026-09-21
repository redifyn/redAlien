<?php

class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            htmlspecialchars(
                self::token(),
                ENT_QUOTES,
                'UTF-8'
            )
        );
    }

    public static function enforce(bool $jsonResponse = false): void
    {
        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';

        $submittedToken =
            $_POST['_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        $valid =
            is_string($sessionToken) &&
            is_string($submittedToken) &&
            $sessionToken !== '' &&
            hash_equals($sessionToken, $submittedToken);

        if ($valid) {
            return;
        }

        http_response_code(419);

        if ($jsonResponse) {
            header('Content-Type: application/json');

            echo json_encode([
                'success' => false,
                'message' => 'Invalid or expired security token.'
            ]);

            exit;
        }

        die('Invalid or expired security token. Please refresh the page and try again.');
    }
}