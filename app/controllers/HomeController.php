<?php

class HomeController extends Controller
    {
        public function __construct()
        {
            Auth::requireLogin();
        }

        public function index(): void
        {
            $data = [
                'title' => 'Dashboard'
            ];

            $this->view('dashboard/index', $data);
        }
    }