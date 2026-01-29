<?php
class HomeController extends Controller{
    public function __construct(){
        if(session_status() == PHP_SESSION_NONE){
            session_start();
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
        if(!isset($_SESSION['user'])) {
            header("Location: /Test/index.php?controller=AutController&action=index");
            exit();
        }
        
        header('cache-control: no-cache, no-store, must-revalidate');
        header('pragma: no-cache');
        header('expires: 0');
        
        $this->View("Home", ["page"=>"personal"]);    
    }
    //-----------------------------------------------------Phần AI làm -------------------------------------------------------//    

    // Cập nhật thông tin cá nhân
    public function updateProfile(){
        header('Content-Type: application/json');
        
        if(!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập hết hạn!']);
            exit();
        }
        
        $userId = $_SESSION['user']['id'];
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate dữ liệu
        if(empty($fullname) || empty($email)){
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!']);
            exit();
        }
        
        // Validate email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!']);
            exit();
        }
        
        try {
            // Load AccountModel
            $accountModel = $this->Model("AccountModel");
            
            // Kiểm tra email đã tồn tại chưa (trừ email của chính user)
            if($accountModel->checkEmailExists($email, $userId)){
                echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng bởi tài khoản khác!']);
                exit();
            }
            
            // Cập nhật vào database
            if(!empty($password)){
                // Có thay đổi mật khẩu
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $result = $accountModel->updateProfileWithPassword($userId, $fullname, $email, $phone, $address, $hashedPassword);
            } else {
                // Không thay đổi mật khẩu
                $result = $accountModel->updateAccount($userId, $fullname, $email, $phone, $address);
            }
            
            if($result){
                // Cập nhật session
                $_SESSION['user']['fullname'] = $fullname;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['user']['address'] = $address;
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
    
    // Xóa tài khoản
    public function deleteAccount(){
        header('Content-Type: application/json');
        
        if(!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập hết hạn!']);
            exit();
        }
        
        $userId = $_SESSION['user']['id'];
        
        try {
            // Load AccountModel
            $accountModel = $this->Model("AccountModel");
            
            // Xóa tài khoản
            $result = $accountModel->deleteAccount($userId);
            
            if($result){
                // Xóa session
                session_destroy();
                echo json_encode(['success' => true, 'message' => 'Xóa tài khoản thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}