<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/default.css">
    <link rel="stylesheet" href="./assets/css/login.css">
    <title>Register</title>
</head>
<body>
    <div id="loading" class="modal loading-page">
        <div class="loading-container">
            <div class="loading-bg">
                <div class="loading-indicator">
                </div>
            </div>
        </div>
    </div>

    <div class="login-container">
        <input type="text" id="email" placeholder="Email">
    
        <input type="text" id="username" placeholder="Username">

        <input type="password" id="password" placeholder="Password">

        <input type="password" id="confirmPassword" placeholder="Confirm Password">
        
        <div style="text-align: center;">
            <button class="btn btn-small" id="submit">SUBMIT</button>
        </div>

        <p style="color: #fff">Ai deja un cont? Atunci <a href="login">intra in cont</a></p>
    </div>

    <div id="loading" class="modal">
        <div class="loading-container">
            <div class="loading-bg">
                <div class="loading-indicator">
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<script type="module" src="./assets/js/register.js"></script>