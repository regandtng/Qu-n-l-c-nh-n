<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/TEST/Public/Css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href ="/Test/index.php?controller=HomeController&aaction=home">
                <i class="fa-solid fa-home" title="Trang chủ"></i></a></li>
            <li onclick="comic()"><a href ="#">
                <i class="fa-solid fa-calendar" title="Lịch khóa biểu"></i></a></li>
            <li onclick="comic()"><a href ="#">
                <i class="fa-solid fa-bell" title="Thông báo"></i></a></li>
            <li onclick="comic()"><a href = "#">
                <i class="fa-solid fa-gamepad" title="Games"></i></a></li>
            <li onclick="comic()"><a href = "#">
                <i class="fa-solid fa-robot" title="Ai"></i></a></li>
        </ul>
        <div class="content">
            <div class="infor">
                <ul class="infor-menu">
                    <li><a href="/Test/index.php?controller=HomeController&action=personal"> <i class="fa-solid fa-user"></i>Thông tin cá nhân</a></li>
                    <li onclick="comic()"><i class="fa-solid fa-image"></i>Ảnh</li>
                    <li onclick="comic()"><i class="fa-solid fa-share"></i>Chia sẻ</li>
                    <li onclick="comic()"><i class="fa-solid fa-heart"></i>Yêu thích</li>
                    <li onclick="comic()"><i class="fa-solid fa-comment"></i>Messenger</li>
                    <li onclick="comic()"><i class="fa-solid fa-cog"></i>Cài đặt</li>
                    <li style="background-color: #d85252"><a href="/Test/index.php?controller=AutController&action=logout">
                        <i class="fa-solid fa-sign-out-alt"></i>Đăng xuất</a></li>
                </ul>
            </div>
            <div class="main">
                <?php
                if(isset($data['page']) && !empty($data['page'])){
                    require_once "./MVC/Views/Pages/" .$data['page'] .".php";
                }else{
                    ?>
                    <div class= "start">
                        <h2>How are you today?</h2>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

</body>
<script>
    function comic(){
        alert("Chức năng này đang được phát triển, vui lòng chờ đợi nhà phát triển hoàn thiện !!!");
    }
</script>
</html>