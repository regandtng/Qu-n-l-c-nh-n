<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin</title>
    <link rel="stylesheet" href="/Test/Public/Css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <ul class="menu">

            <li onclick="comic()"><i class="fa-solid fa-home" title="Trang chủ"></i></li>
            <li onclick="comic()"><i class="fa-solid fa-calendar" title="Lịch khóa biểu"></i></li>
            <li onclick="comic()"><i class="fa-solid fa-bell" title="Thông báo"></i></li>
            <li onclick="comic()"><i class="fa-solid fa-gamepad" title="Games"></i></li>
            <li onclick="comic()"><i class="fa-solid fa-robot" title="Ai"></i></li>
        </ul>
        <div class= "content">
            <div class = "infor">
                <ul class = "infor-menu">
                    <li onclick="comic()"><i class="fa-solid fa-user"></i>Thông tin cá nhân</li>
                    <li onclick="comic()"><i class="fa-solid fa-image"></i>Quản lý tài khoản</li>
                    <li onclick="comic()"><i class="fa-solid fa-share"></i>Quản lý Ảnh </li>
                    <li onclick="comic()"><i class="fa-solid fa-comment"></i>Messenger</li>
                    <li onclick="comic()"><i class="fa-solid fa-cog"></i>Cài đặt</li>
                    <li style="background-color: #d85252"><a href="/Test/index.php?controller=AutController&action=logout">
                        <i class="fa-solid fa-sign-out-alt"></i>Đăng xuất</a></li>
                </ul>
            </div>
            <div class ="main">
                    <h2>Giao diện admin</h2>
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