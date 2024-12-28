<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/default.css">
    <link rel="stylesheet" href="./assets/css/index.css">
    <title>Airplane</title>
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

    <div id="container">
        <div id="gameDetails">
            <div id="opponent"></div>
            <div id="theGameId"></div>
        </div>

        <div id="attackTable" class="table"></div>

        <div id="shipTable" class="table">
            <div id="ships">
                <div class="rotate-ship-btn"><img src="./assets/images/rotate.png" alt=""></div>
            </div>
        </div>
    </div>

    <div id="checkNewGameModal" class="modal">
        <div>
            <div class="login-container">
                <label for="gameId">Joaca cu un prieten:</label>

                <input type="text" id="gameId" placeholder="Game #ID">

                <br>

                <button id="startGame" class="btn btn-small">START</button>
            </div>

            <div class="buttons-form">
                <a href="stats">Check Stats</a>
                <a href="profile">Profile</a>
                <a href="" id="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div id="modal" class="modal">
        <div id="modalContainer" class="modal-container"></div>
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

<script type="module" src="./assets/js/index.js"></script>