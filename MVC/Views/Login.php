<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test dự án</title>
    <link rel="stylesheet" href="/Test/Public/Css/login.css">
</head>
<body>
    <form method="POST" action="/TEST/index.php?controller=AutController&action=login" autocomplete="off">
    <div class="container">
        <div class="login">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" autocomplete="off"/>
              </div>  
            <div class="form-group">
                <label for="login-password">Password:</label>
                <input type="password" name="password" autocomplete="new-password"/>
            </div>
            <div>
                <button type="submit">Login</button> 
                <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                    <a class="forgot-password" >Quên mật khẩu ?</a>
                    <a class="forgot-password" href="/Test/index.php?controller=AutController&action=register">Đăng ký</a>
                    
                </div>
            </div>
        </div>
    </div>
</form>
</body>
</html>