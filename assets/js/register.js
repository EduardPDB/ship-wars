import Game from "./airplane.js";
import post, { baseUrl, hideLoadingPage, pathArray, token } from "./helpers.js";

if (token) {
    window.location = `${baseUrl}${pathArray}`;
}

hideLoadingPage();

document.getElementById('submit').removeEventListener('click', register);
document.getElementById('submit').addEventListener('click', register);

function register()
{
    const url  = 'api/register';
    const data = {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        confirmPassword: document.getElementById('confirmPassword').value,
        name: document.getElementById('username').value
    };

    Game.showLoading();
    post(url, data).then((response) => {
        if (response.status === 'ok') {
            localStorage.setItem('token', response.data.token);
            window.location = baseUrl;
        } else {
            alert(response.message);
        }
        Game.hideLoading();
    })
}