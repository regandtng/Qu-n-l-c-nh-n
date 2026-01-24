<?php
 class ConnectDB {
     protected $conn;

    public function __construct(){
        $this->conn = new mysqli("localhost", "root", "", "test_db");

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        mysqli_set_charset($this->conn, "utf8");
    }
     public function getConnection(){
        return $this->conn;
    }
     public function checklogin($username, $password){
         $sql = "SELECT * FROM accounts WHERE username = ? AND password = ?";
         $stmt = $this->conn->prepare($sql);
         $stmt->bind_param("ss", $username, $password);
         $stmt->execute();
         $result = $stmt->get_result();
         return $result->num_rows > 0;
     }
     public function execute($sql){
         return $this->conn->query($sql);
     }
 }
?>