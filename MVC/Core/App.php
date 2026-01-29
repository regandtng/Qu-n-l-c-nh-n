<?php
    class App{
        protected $controller = "AutController";
        protected $action = "index";

        public function __construct(){
            // Bắt đầu session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if(isset($_GET['controller'])){
                $this->controller = $_GET['controller'];
            }

            require_once "./MVC/Controllers/" . $this->controller . ".php";
            $this->controller = new $this->controller();

            if(isset($_GET['action'])){
                $this->action = $_GET['action'];
            }
            call_user_func([$this->controller, $this->action]);
        }

    }