<?php
    class Controller{
        public function __construct() {
            header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
        public function View($view, $data = []) {
            extract($data);
            require_once "./MVC/Views/" . $view . ".php";
        }
        public function Model($model){
            require_once "./MVC/Models/" . $model . ".php";
            require_once "./MVC/Core/ConnectDB.php";
            $db = new ConnectDB();
            return new $model($db->getConnection());
        }

    } 

?>      