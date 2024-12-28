import Game from './airplane.js';
import post, { baseUrl, buildUrl, checkToken, hideLoadingPage } from './helpers.js';

checkToken(false);

window.onload = async function () {
    const response = await post(buildUrl('profile'));
    if (response.status !== 'ok') {
        window.location = baseUrl;
        return;
    }
    const user       = response.data;
    const container  = document.getElementById('profile-data');
    const userInputs = document.createElement('div');
    userInputs.innerHTML = `
        <label for="email">Email</label>
        <div>
            <input id="email" value="${user.email}">
        </div>

        <label for="name">Name</label>
        <div>
            <input id="name" value="${user.name}">
        </div>

        <div>
            <button class="btn btn-small">Submit</button>
        </div>
    `;
    userInputs.classList.add('user-inputs');
    container.appendChild(userInputs);
    document.querySelector('button').addEventListener('click', updateUserData);
    hideLoadingPage();
}

function updateUserData() {
    const nameInput  = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const data = {name: nameInput.value, email: emailInput.value};
    Game.showLoading();
    post(buildUrl('updateProfile'), data).then((response) => {
        Game.hideLoading();
        alert(response.message);
    });
}


