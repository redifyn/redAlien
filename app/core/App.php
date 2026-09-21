<?php

class App
{
    protected $controller = 'HomeController';
    protected string $method = 'index';
    protected array $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        if (!empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerFile = APPROOT . '/controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        require_once APPROOT . '/controllers/' . $this->controller . '.php';

        $controllerClass = $this->controller;
        $this->controller = new $controllerClass();

        if (!empty($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array(
            [$this->controller, $this->method],
            $this->params
        );
    }

    private function parseUrl(): array
    {
        if (!isset($_GET['url'])) {
            return [];
        }

        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return $url === '' ? [] : explode('/', $url);
    }
}