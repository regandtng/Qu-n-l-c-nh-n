<?php
    class GamesController extends Controller {
 
        public function index() {
            if (!isset($_SESSION['user'])) {
                header("Location: /Test/index.php?controller=AuthController&action=index");
                exit();
            }
            header('cache-control: no-cache, no-store, must-revalidate');
            header('pragma: no-cache');
            header('expires: 0');
 
            $this->View("Home", ["page" => "Games"]);
        }
 
        public function caro() {
            require_once "./MVC/Views/Play/Caro.php";
            exit();
        }
    }