<?php

class Controller
{
    public function model(string $model)
    {
        $modelFile = APPROOT . '/models/' . $model . '.php';

        if (!file_exists($modelFile)) {
            die("Model {$model} not found.");
        }

        require_once $modelFile;

        return new $model();
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard/App Views
    |--------------------------------------------------------------------------
    |
    | Use this for authenticated pages such as:
    | dashboard, teams, chat, meetings, files, settings.
    |
    */
    public function view(string $view, array $data = []): void
    {
        $viewFile = APPROOT . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View {$view} not found.");
        }

        extract($data);

        require APPROOT . '/views/layouts/header.php';
        require APPROOT . '/views/layouts/splash.php';
        require APPROOT . '/views/layouts/navbar.php';
        require APPROOT . '/views/layouts/sidebar.php';
        require $viewFile;
        require APPROOT . '/views/layouts/footer.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication Views
    |--------------------------------------------------------------------------
    |
    | Login, registration and password-reset pages should not display
    | the main dashboard navigation.
    |
    */
    public function authView(string $view, array $data = []): void
    {
        $viewFile = APPROOT . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("Authentication view {$view} not found.");
        }

        extract($data);

        require APPROOT . '/views/layouts/auth-header.php';

        require $viewFile;

        require APPROOT . '/views/layouts/auth-footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }
}