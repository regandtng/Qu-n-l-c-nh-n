<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educhat</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Test/Public/Css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Áp dụng theme TRƯỚC khi render → không bị chớp khi chuyển trang -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <div class="container">
        <ul class="menu">
            <li><a href="/Test/index.php?controller=HomeController&action=index">
                <i class="fa-solid fa-home"></i><span class="tooltip">Trang chủ</span></a></li>
            <li><a href="/Test/index.php?controller=ScheduleController&action=index">
                <i class="fa-solid fa-calendar"></i><span class="tooltip">Lịch</span></a></li>
            <li><a href="/Test/index.php?controller=NotificationController&action=index">
                <i class="fa-solid fa-bell"></i><span class="tooltip">Thông báo</span></a></li>
            <li><a href="/Test/index.php?controller=GamesController&action=index">
                <i class="fa-solid fa-gamepad"></i><span class="tooltip">Games</span></a></li>
            <li><a href="/Test/index.php?controller=AiController&action=index">
                <i class="fa-solid fa-robot"></i><span class="tooltip">AI</span></a></li>
        </ul>
 
        <div class="content">
            <div class="infor" id="sidebar" style="display:flex; flex-direction:column;">
 
                <!-- Nút thu/mở sidebar -->
                <button class="sidebar-toggle" onclick="toggleSidebar()" id="sidebarToggle">
                    <i class="fa-solid fa-chevron-left" id="sidebarIcon"></i>
                </button>
 
                <ul class="infor-menu">
                    <li><a href="/Test/index.php?controller=HomeController&action=personal">
                        <i class="fa-solid fa-user"></i><span class="menu-text">Thông tin cá nhân</span></a></li>
                    <li onclick="comic()">
                        <i class="fa-solid fa-image"></i><span class="menu-text">Ảnh</span></li>
                    <li onclick="comic()">
                        <i class="fa-solid fa-share"></i><span class="menu-text">Chia sẻ</span></li>
                    <li><a href="/Test/index.php?controller=FriendController&action=index">
                        <i class="fa-solid fa-heart"></i><span class="menu-text">Yêu thích</span></a></li>
                    <li><a href="/Test/index.php?controller=MessengerController&action=index">
                        <i class="fa-solid fa-comment"></i><span class="menu-text">Messenger</span></a></li>
                    <li onclick="comic()">
                        <i class="fa-solid fa-cog"></i><span class="menu-text">Cài đặt</span></li>
                </ul>
 
                <ul class="logout-wrap">
                    <li class="dark-light" onclick="toggleDarkMode()" id="DarkModebtn">
                        <i class="fa-solid fa-moon" id="themeIcon"></i>
                        <span class="menu-text" id="themeLabel">Giao diện tối</span>
                    </li>
                    <li class="logout-btn">
                        <a href="/Test/index.php?controller=AuthController&action=logout">
                            <i class="fa-solid fa-sign-out-alt"></i><span class="menu-text">Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </div>
 
            <div class="main">
                <?php

                if (isset($data['Play']) && !empty($data['Play'])) {
                    $playFile = "./MVC/Views/Pages/" . $data['Play'] . ".php";
                    if (file_exists($playFile)) require_once $playFile;
                }
                
                elseif (isset($data['page']) && !empty($data['page'])) {
                    $pageFile = "./MVC/Views/Pages/" . $data['page'] . ".php";
                    if (file_exists($pageFile)) require_once $pageFile;
                }
                else { ?>
                    <div class="start">
                        <h2>How are you today?</h2>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
 
</body>
<script>
    function comic() {
        alert("Chức năng này đang được phát triển, vui lòng chờ đợi nhà phát triển hoàn thiện !!!");
    }
 
    // ── Dark / Light mode ──────────────────────────────────
    function toggleDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeBtn(isDark);
    }
 
    function updateThemeBtn(isDark) {
        const icon  = document.getElementById('themeIcon');
        const label = document.getElementById('themeLabel');
        if (!icon || !label) return;
        icon.className    = isDark ? 'fa-solid fa-sun'  : 'fa-solid fa-moon';
        label.textContent = isDark ? 'Giao diện sáng' : 'Giao diện tối';
    }
 
    // ── Sidebar thu/mở ─────────────────────────────────────
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const isCollapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar', isCollapsed ? 'collapsed' : 'open');
        updateSidebarIcon(isCollapsed);
    }
 
    function updateSidebarIcon(isCollapsed) {
        const icon = document.getElementById('sidebarIcon');
        if (!icon) return;
        icon.className = isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
    }
 
    // ── Khởi tạo khi load trang ────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        updateThemeBtn(document.documentElement.classList.contains('dark-mode'));
 
        if (localStorage.getItem('sidebar') === 'collapsed') {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.add('collapsed');
                updateSidebarIcon(true);
            }
        }
    });
</script>
</html>