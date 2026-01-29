<?php
class AccountModel extends ConnectDB {

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAccount($username) {  // hàm 
        $sql = "SELECT * FROM accounts WHERE username = ?";
        $stmt = $this->conn->prepare($sql); 
        $stmt->bind_param("s", $username ); 
        $stmt->execute();
        return $stmt->get_result()-> fetch_assoc();  
    }
     

    public function createAccount($fullname,$username, $password, $email, $role) {
        $sql = "INSERT INTO accounts (fullname, username, password, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $fullname, $username, $password, $email, $role);
        return $stmt->execute();
    }
    //-------------------------------------------------------Phần AI làm -------------------------------------------------------//

    public function updateAccount($id, $fullname, $email, $phone, $address) {
        $sql = "UPDATE accounts SET fullname = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $fullname, $email, $phone, $address, $id);
        return $stmt->execute();
    }

    public function  updateProfileWithPassword($id, $fullname, $email, $phone, $address, $password) {
        $sql = "UPDATE accounts SET fullname = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $fullname, $email, $phone, $address, $password, $id);
        return $stmt->execute();
    }

    public function deleteAccount($id) {
        $sql = "DELETE FROM accounts WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
       // Kiểm tra email đã tồn tại chưa (dùng khi update để tránh trùng)
    public function checkEmailExists($email, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT id FROM accounts WHERE email = ? AND id != ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $email, $excludeId);
        } else {
            $sql = "SELECT id FROM accounts WHERE email = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $email);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Kiểm tra username đã tồn tại chưa
    public function checkUsernameExists($username) {
        $sql = "SELECT id FROM accounts WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}