<?php
    class NotificationController extends Controller{
        public function index(){
            if(!isset($_SESSION['user'])){
                header("Location: /Test/index.php?controller=AuthController&action=index");
            }
            header('cache-control: no-cache, no-store, must-revalidate');
            header('pragma: no-cache');
            header('expires: 0');

            $this->View("Home",["page"=>"Notification"]);
        }
    }