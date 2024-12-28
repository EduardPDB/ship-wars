<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/default.css">
    <link rel="stylesheet" href="./assets/css/login.css">
    <title>Login</title>
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

        <input type="password" id="password" placeholder="Password">

        <div style="text-align: center;">
            <button id="submitBtn" class="btn btn-small">SUBMIT</button>
        </div>

        <p style="color: #fff">Nu ai cont? Atunci <a href="register">inregistreaza-te</a></p>
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

<script type="module" src="./assets/js/login.js"></script>