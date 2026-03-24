<?php
class AuthController extends Controller {
    private $account;
    public function index() {
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
        if($_SERVER['REQUEST_METHOD']=='POST') {

            $Username = $_POST['username']??'';
            $Password = $_POST['password']??'';
        
            $user = $this->account->getAccount($Username);

            if($user && password_verify($Password, $user['password']) && ($user['role'] == 'user' || $user['role'] == '')) {
                $_SESSION['user'] = $user;
                echo "<script>window.location.replace('/Test/index.php?controller=HomeController&action=index');</script>";
                exit();
            } else if($user && password_verify($Password, $user['password']) && ($user['role'] == 'admin')){
                $_SESSION['user']= $user;
                echo "<script>window.location.replace('/Test/index.php?controller=AdminController&action=index');</script>";
                exit();
            } else {
                $error = "Sai tài khoản hoặc mật khẩu!";
            }
        }
        $this->View("Login", ['error'=>$error]); 
    }
    
public function register() { 
        if($_SERVER['REQUEST_METHOD'] == 'GET') { 
            $this->View("Register"); return; } if($_SERVER['REQUEST_METHOD'] == 'POST') { 
                $Fullname = $_POST['fullname']; $Email = $_POST['email']; 
                $Username = $_POST['username']; $Password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
                $role = 'user'; $result = $this->account->createAccount($Fullname, $Username, $Password, $Email, $role); 
                    if($result){ 
                        $success = "Đăng ký thành công!";
                        } else { 
                            $error = "Đăng ký thất bại!"; } 

            
       }
                 $this->View("Login", ['error' => $error ?? '', 'success' => $success ?? '']);
    }
    
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        header("Cache-Control: no-cache, no-store, must-revalidate"); 
        header("Pragma: no-cache"); 
        header("Expires: 0"); 

        header("Location: /Test/index.php?controller=AuthController&action=index");
        exit();
    }
}