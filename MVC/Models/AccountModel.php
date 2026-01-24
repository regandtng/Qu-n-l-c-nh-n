<?php
class AccountModel extends ConnectDB {

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAccount($username) {
        $sql = "SELECT * FROM accounts WHERE username = ?";
        $stmt = $this->conn->prepare($sql); 
        $stmt->bind_param("s", $username ); // dòng này có tác dụng là 
        $stmt->execute();
        return $stmt->get_result()-> fetch_assoc();  
    }
     

    public function createAccount($fullname,$username, $password, $email) {
        $sql = "INSERT INTO accounts (fullname, username, password, email) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $fullname, $username, $password, $email);
        return $stmt->execute();
    }
}