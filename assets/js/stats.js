import post, { buildUrl, checkToken, formatDate, hideLoadingPage } from "./helpers.js";

checkToken(false);

window.onload = function () {
    post(buildUrl('getStats')).then((response) => {
        if (response.status === 'ok') {
            insertRows(response.data.games);
            insertTotalGames(response.data.stats);
        }
    }).finally(hideLoadingPage);
}

function insertRows(games) {
    const tbody = document.querySelector('.custom-table tbody');
    games.forEach(game => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${game.id}</td>
            <td>${formatDate(game.date)}</td>
            <td>${game.opponent ? game.opponent : 'Fara inamic'}</td>
            <td>${game.win !== '0' ? 'Da' : 'Nu'}</td>
        `;
        tbody.appendChild(row);
    });
}

function insertTotalGames(stats) {
    document.getElementById('totalGames').innerText = stats.totalGames;
    document.getElementById('totalWins').innerText = stats.totalWins;
}

