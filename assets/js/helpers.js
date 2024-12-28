import Game from "./airplane.js";

async function post(url, data)
{
    return fetch(url,
        {
            method: 'post',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        }
    ).then(response => {
        if (!response.ok) {
            throw new Error('A fost o eroare, va rog reincercati');
        }
        return response.json();
    }).catch(error => {
        return {
            status: 'error',
            message: `Error: ${error}`,
            data: [],
        };
    });
}
const pathArray = '/airplane/';
const baseUrl   = window.location.origin;
const token     = localStorage.getItem('token');

function checkToken(hideLoading) {
    if (!token) {
        window.location = `${baseUrl}${pathArray}login`;
        return;
    }
    if (token) {
        post(buildUrl('checkToken')).then(response => {
            if (response.status !== 'ok') {
                localStorage.removeItem('token');
                window.location = `${baseUrl}${pathArray}login`;
            } else {
                const token = response.data.token;
                localStorage.setItem('token', token);
                if (hideLoading !== false) hideLoadingPage();
            }
        })
    }
}

function buildUrl(endPoint)
{
    return `${baseUrl}${pathArray}api/${endPoint}?token=${token}`;
}

function logout(event) {
    event.preventDefault();
    Game.showLoading();
    post(buildUrl('logout')).then((response) => {
        if (response.status === 'ok') {
            localStorage.removeItem('token');
            window.location = `${baseUrl}${pathArray}login`;
        }
    })
    .catch((error) => console.log(error))
    .finally(Game.hideLoading);
}

function padZero(number) {
    return number < 10 ? `0${number}` : number;
}

function formatDate(stringDate) {
    const date = new Date(stringDate);
    return `${padZero(date.getDay())}/${padZero(date.getMonth())}/${padZero(date.getFullYear())} ${padZero(date.getHours())}:${padZero(date.getMinutes())}`;
}

const loadingPage = document.querySelector('.loading-page');

function hideLoadingPage() {
    loadingPage.classList.add('d-none');
}

function showLoadingPage() {
    loadingPage.classList.remove('d-none')
}

export default post;
export {
    pathArray,
    baseUrl,
    token,
    loadingPage,
    checkToken,
    logout,
    buildUrl,
    hideLoadingPage,
    showLoadingPage,
    padZero,
    formatDate,
}
