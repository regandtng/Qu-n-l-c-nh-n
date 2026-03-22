<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đạtđẹpzai.web</title>
    <link rel="stylesheet" href="/Test/Public/Css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href ="/Test/index.php?controller=HomeController&aaction=home">
                <i class="fa-solid fa-home"></i><span class="tooltip">Trang chủ</span></a></li>
            <li><a href ="/Test/index.php?controller=ScheduleController&action=index">
                <i class="fa-solid fa-calendar"></i><span class="tooltip">Lịch</span></a></li>
            <li><a href ="/Test/index.php?controller=NotificationController&action=index">
                <i class="fa-solid fa-bell"></i><span class="tooltip">Thông báo</span></a></li>
            <li><a href = "/Test/index.php?controller=GamesController&action=index">
                <i class="fa-solid fa-gamepad"></i><span class="tooltip">Games</span></a></li>
            <li><a href ="/Test/index.php?controller=AiController&action=index">
                <i class="fa-solid fa-robot"></i><span class="tooltip">AI</span></a></li>
        </ul>
        <div class="content">
            <div class="infor" style="display:flex; flex-direction:column;">
                <ul class="infor-menu">
                    <li><a href="/Test/index.php?controller=HomeController&action=personal">
                        <i class="fa-solid fa-user"></i>Thông tin cá nhân</a></li>
                    <li onclick="comic()"><i class="fa-solid fa-image"></i>Ảnh</li>
                    <li onclick="comic()"><i class="fa-solid fa-share"></i>Chia sẻ</li>
                    <li onclick="comic()"><i class="fa-solid fa-heart"></i>Yêu thích</li>
                    <li onclick="comic()"><i class="fa-solid fa-comment"></i>Messenger</li>
                    <li onclick="comic()"><i class="fa-solid fa-cog"></i>Cài đặt</li>
                </ul>

                <ul class="logout-wrap">
                    <li class="logout-btn">
                        <a href="/Test/index.php?controller=AutController&action=logout">
                            <i class="fa-solid fa-sign-out-alt"></i>Đăng xuất
                        </a>
                    </li>
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