import Game from "./airplane.js";
import { logout } from "./helpers.js";

document.getElementById('logout-btn')?.addEventListener('pointerdown', logout);
new Game();
