<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/default.css">
    <title>Stats</title>
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

    <div id="loading" class="modal">
        <div class="loading-container">
            <div class="loading-bg">
                <div class="loading-indicator">
                </div>
            </div>
        </div>
    </div>

    <div class="buttons-navigation">
        <a href="/">Play</a>
        <a href="profile">Profile</a>
    </div>

    <table class="custom-table" border="1">
        <thead>
            <tr>
                <th>Id</th>
                <th>Data</th>
                <th>Inamic</th>
                <th>Castigator</th>
            </tr>
        </thead>

        <tbody>

        </tbody>
    </table>

    <table class="custom-table" border="1">
        <thead>
            <tr>
                <th>Total Meciuri</th>
                <th>Total Castiguri</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td id="totalGames"></td>
                <td id="totalWins"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>

<script type="module" src="./assets/js/stats.js"></script>