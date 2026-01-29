<?php
class HomeController extends Controller{
    public function __construct(){
        if(SESSION_STATUS() == PHP_SESSION_NONE){
                SESSION_();
        }
    }
    public function index(){
        
        if(!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AutController&action=index");
            exit();
        }
        header('cache-control: no-cache, no-store, must-revalidate');
        header('pragma: no-cache');
        header('expires: 0');

        $this->View("Home");
    }
    
    public function personal(){
        // Thêm session check trước khi hiển thị trang personal
        if(!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AutController&action=index");
            exit();
        }
        
        header('cache-control: no-cache, no-store, must-revalidate');
        header('pragma: no-cache');
        header('expires: 0');
        
        $this->View("Home", ["page"=>"personal"]);    
    }   
}