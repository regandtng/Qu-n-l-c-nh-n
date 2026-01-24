<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test dự án</title>
    <link rel="stylesheet" href="/TEST/Public/Css/register.css">
</head>
<body>
    <form method="POST" action="/TEST/index.php?controller=AutController&action=register">
    <div class="container">
     <div class="register" >
            <div class="form-group">
                <label>Fullname:</label>
                <input type="text" name="fullname" />
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="email" />
            </div>
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" />
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" />
            </div>
            <div class="form-group">
                <button type="submit" >Register</button>  
            </div>
            <div>
                <a class="forgot-password" href="/TEST/index.php?controller=AutController&action=index">Back</a>
            </div>
        </div>
    </div>
    </form>
</body>
</html>