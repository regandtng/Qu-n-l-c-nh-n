<?php
class AutController extends Controller {
    private $account;
    public function index() {
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user'])) {

        echo "<script>window.location.replace('/Test/index.php?controller=HomeController&action=index');</script>";
        exit();
    }
        $this->View("Login");
    }
    public function __construct(){
        $this->account = $this->Model("AccountModel");
    }

    public function login() {
       if($_SERVER['REQUEST_METHOD']=='POST'){

        $Username = $_POST['username']??'';
        $Password = $_POST['password']??'';

        $user = $this->account->getAccount($Username);

        if($user && password_verify($Password, $user['password'])) {
       
            session_start();
            $_SESSION['user'] = $user;
             echo "<script>clearLoginForm();</script>";
           echo "<script>window.location.replace('/Test/index.php?controller=HomeController&action=index');</script>";
            exit();
        } else {
            echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng!');</script>";
        }
    $this->View("Login");
    }
    }
    public function register() {
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->View("Register");
            return;
        }

      if($_SERVER['REQUEST_METHOD'] == 'POST') {

        $Fullname = $_POST['fullname'];
        $Email = $_POST['email'];
        $Username = $_POST['username'];
        $Password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $result = $this->account->createAccount($Fullname, $Username, $Password, $Email);
        }

        if($result){
            echo "<script>alert('Đăng ký thành công!');</script>";
        } else {
            echo "<script>alert('Đăng ký thất bại!');</script>";
        }
        $this->View("Register");
    }
  public function logout() {
    session_start();
    session_unset();
    session_destroy();

    header("Cache-Control: no-cache, no-store, must-revalidate"); 
    header("Pragma: no-cache"); 
    header("Expires: 0"); 


    header("Location: /Test/index.php?controller=AutController&action=index");
    exit();
}
   
}       