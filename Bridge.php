<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);

    require_once "./MVC/Core/App.php";
    require_once "./MVC/Core/ConnectDB.php";
    require_once "./MVC/Core/Controller.php";