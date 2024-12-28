import Game from "./airplane.js";
import post, { baseUrl, hideLoadingPage, pathArray , token } from "./helpers.js";

if (token) {
    window.location = `${baseUrl}${pathArray}`;
}

hideLoadingPage();

document.getElementById('submitBtn').removeEventListener('click', login);
document.getElementById('submitBtn').addEventListener('click', login);

function login()
{
    const url  = `api/login`;
    const data = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value
    };

    Game.showLoading();
    post(url, data).then((response) => {
        if (response.status === 'ok') {
            localStorage.setItem('token', response.data.token);
            window.location = `${baseUrl}${pathArray}`;
        } else {
            alert(response.message);
        }
        Game.hideLoading();
    })
}