<?php
    class App{
        protected $controller = "AuthController";
        protected $action = "index";

        public function __construct(){
            // Bắt đầu session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if(isset($_GET['controller'])){
                $controllerName = parse_url($_GET['controller'], PHP_URL_PATH);
                $controllerName = preg_replace('/[^a-zA-Z0-9_]/', '', $controllerName);
                if ($controllerName !== '' && strpos($controllerName, 'Controller') === false) {
                    $controllerName .= 'Controller';
                }
                if ($controllerName !== '') {
                    $this->controller = $controllerName;
                }
            }

            $controllerFile = "./MVC/Controllers/" . $this->controller . ".php";
            if (!file_exists($controllerFile)) {
                die('Controller không tồn tại: ' . htmlspecialchars($this->controller));
            }
            require_once $controllerFile;
            $this->controller = new $this->controller();

            if(isset($_GET['action'])){
                $actionName = parse_url($_GET['action'], PHP_URL_PATH);
                $actionName = preg_replace('/[^a-zA-Z0-9_]/', '', $actionName);
                $actionName = preg_replace('/\.php$/', '', $actionName);
                if ($actionName !== '') {
                    $this->action = $actionName;
                }
            }
            call_user_func([$this->controller, $this->action]);
        }

    }