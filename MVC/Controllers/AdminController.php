<?php
    class AdminController extends Controller{

        public function __construct(){
            if(session_status() == PHP_SESSION_NONE){
                session_start();
            }
        }
        
        public function index(){
            if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
                header("Location: /Test/index.php?controller=AutController&action=index");
                exit();
            }
            header('cache-control: no-cache, no-store, must-revalidate');
            header('pragma: no-cache');
            header('expires: 0');

            $this->View("Admin");
        }
    }