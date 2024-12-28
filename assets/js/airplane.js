import post, { checkToken, hideLoadingPage, showLoadingPage, token } from './helpers.js';

export default class Game {
    attackTable;
    attackFields = {};
    baseUrl;
    gameStarted  = false;
    gameId       = false;
    myTurn       = false;
    shipFields   = {};
    token;
    user;
    URL;
    loading;
    opponent = false;
    shipSizes = [4, 3, 2];
    nrOfShipsPerSize = 3;
    totalMaxHits = 0;
    buttonDown = false;

    /**
     * Selected ship.
     *
     * @type {HTMLDivElement}
     */
    selectedShip;

    /**
     * Is draging ship
     * 
     * @type {bool}
     */
    isDrag = false;

    /**
     * The explosion sound when a field is hit.
     */
    explosionAudio;

    /**
     * A callback to be called when closing the message modal.
     */
    callback;

    /**
     * Win animations
     */
    winIframes = [
        '<iframe src="https://giphy.com/embed/l1EtjmFngkQTlvN3a" width="100%" height="100%" style="" frameBorder="0" allowFullScreen></iframe>',
        '<iframe src="https://giphy.com/embed/Qadbv0ccmSrJL9Vlwj" width="100%" height="100%" style="" frameBorder="0" allowFullScreen></iframe>'
    ];

    /**
     * Win animations
     */
    lostIframes = [
        '<iframe src="https://giphy.com/embed/MwIvOD6KuAdMiE9P5Z" width="100%" height="100%" style="" frameBorder="0" allowFullScreen></iframe>',
        '<iframe src="https://giphy.com/embed/B4uP3h97Hi2UaqS0E3" width="100%" height="100%" style="" frameBorder="0" allowFullScreen></iframe>'
    ];

    constructor()
    {
        this.baseUrl      = window.location.origin;
        this.pathArray    = window.location.pathname;
        this.modal        = document.getElementById('modal');
        this.modalCont    = document.getElementById('modalContainer');
        this.token        = token;
        this.container    = document.getElementById('container');

        checkToken();

        this.getStarted       = this.getStarted.bind(this)
        this.hideModal        = this.hideModal.bind(this);
        this.checkMoves       = this.checkMoves.bind(this);
        this.selectShip       = this.selectShip.bind(this);
        this.startDraging     = this.startDraging.bind(this);
        this.endDraging       = this.endDraging.bind(this);
        this.dragSelectedShip = this.dragSelectedShip.bind(this);
        this.rotateShip       = this.rotateShip.bind(this);
        this.attack           = this.attack.bind(this);
        this.userExit         = this.userExit.bind(this);
        this.explosionAudio   = new Audio('assets/sounds/explosion.mp3');
        document.getElementById('startGame').removeEventListener('click', this.getStarted);
        document.getElementById('startGame').addEventListener('click', this.getStarted);
        document.addEventListener('pointerdown', this.selectShip);
        document.addEventListener('pointerdown', this.startDraging);
        document.addEventListener('pointerup', this.endDraging);
        document.addEventListener('keypress', this.rotateShip);
        document.querySelector('.rotate-ship-btn').addEventListener('pointerdown', this.rotateShip);
    }

    buildUrl(endPoint)
    {
        return `${this.baseUrl}${this.pathArray}api/${endPoint}?token=${this.token}`;
    }

    userExit()
    {
        return post(this.buildUrl('playerQuit'));
    }

    checkMoves()
    {
        post(this.URL.checkMoves).then((response) => {
            if (response.status === 'ok') {
                this.myTurn       = response.data.myTurn;
                this.gameStarted  = response.data.gameStarted;
                this.opponent     = response.data.opponent;
                let attackedField = response.data.fieldAttacked;

                if (this.gameStarted && !this.attackTable.classList.contains('show')) {
                    this.handleGameStart();
                }

                const opponentCont = document.getElementById('opponent');
                if (this.opponent && !opponentCont.classList.contains('has-opponent') ) {
                    opponentCont.innerText = `Dusmanul: ${this.opponent}`;
                    opponentCont.classList.add('has-opponent');
                    window.onbeforeunload = this.userExit;
                }

                if (attackedField && !this.shipFields[attackedField].classList.contains('attacked')) {
                    this.explosionAudio.play();
                    this.shipFields[attackedField].classList.add('attacked');
                }

                if (response.data.win) {
                    this.callback = () => {
                        window.location.reload();
                        return;
                    }
                    const randomIndex = Math.floor(Math.random() * this.winIframes.length);
                    this.displayMessage(this.winIframes[randomIndex]);
                }

                if (response.data.userWon && response.data.lost) {
                    this.callback = () => {
                        window.location.reload();
                        return;
                    }
                    const randomIndex = Math.floor(Math.random() * this.lostIframes.length);
                    this.displayMessage(this.lostIframes[randomIndex]);
                }

                if (response.data.opponentLeft) {
                    this.callback = () => {
                        window.onbeforeunload = null;
                        window.location.reload();
                        return;
                    }
                    this.displayMessage('A iesit inamicul!');
                }
            }
            setTimeout(this.checkMoves, 2000);
        });
    }

    handleGameStart() {
        this.attackTable.classList.add('show');
        document.removeEventListener('pointerdown', this.selectShip);
        document.removeEventListener('pointerdown', this.startDraging);
        document.removeEventListener('pointerup', this.endDraging);
        document.removeEventListener('keypress', this.rotateShip);
        const rotateShipBtn = document.querySelector('.rotate-ship-btn');
        rotateShipBtn.removeEventListener('pointerdown', this.rotateShip);
        rotateShipBtn.remove();
    }

    hideStartGameModal() {
        return document.getElementById('checkNewGameModal').classList.add('hide');
    }

    buildTheFields() 
    {
        this.attackTable = document.getElementById('attackTable');
        const shipTable  = document.getElementById('shipTable');
        const letters    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];

        letters.forEach((letter, y) => {
            for(let x = 1; x <= 14; x++) {
                // Create attack fields
                let field    = document.createElement('div');
                let position = `${letter}${x}`;
                field.id     = position;

                field.classList.add('field-attack');
                field.dataset.x = x;
                field.dataset.y = y + 1;
                this.attackTable.appendChild(field);
                this.attackFields[position] = field;

                // Create ship fields
                field    = document.createElement('div');
                field.dataset.x = x;
                field.dataset.y = y + 1;
                position = `${letter}${x}`;
                field.id = position;

                field.classList.add('field-ship');
                shipTable.appendChild(field);

                this.shipFields[position] = field;
            }
        });

        document.removeEventListener('click', this.attack);
        document.addEventListener('click', this.attack);
    }

    attack(e)
    {
        Game.showLoading();
        let field = e.target;
        if (!field.classList.contains('field-attack')) {
            Game.hideLoading();
            return;
        }

        if (!this.gameStarted) {
            Game.hideLoading();
            return this.displayMessage('Jocul nu a inceput inca!<br>Unde te grabesti >:(');
        }

        if (!this.myTurn) {
            Game.hideLoading();
            return this.displayMessage('Asteapta-ti randul :(');
        }

        if (field.classList.contains('attacked-hit') || field.classList.contains('attacked-not-hit')) {
            Game.hideLoading();
            return this.displayMessage('Ai atacat deja aici zmecherule!');
        }

        try
        {
            post(this.URL.attack, { field_id: field.id }).then((response) =>{
                if (response.status === 'ok') {
                    if (response.data.hit === true) {
                        field.classList.add('attacked-hit');
                    } else {
                        field.classList.add('attacked-not-hit');
                    }
                    
                    this.myTurn = false;
                }

                Game.hideLoading();
                return this.displayMessage(response.message);
            });
        } catch (error) {
            Game.hideLoading();
            return this.displayMessage(error);
        }
        Game.hideLoading();
    }

    getStarted()
    {
        showLoadingPage();
        let getStartedUrl = this.buildUrl('getStarted');
        let gameId        = document.getElementById('gameId').value;

        try
        {
            post(getStartedUrl, {
                game_id: gameId
            }).then((response) => {
                if (response.status === 'ok') {
                    const data = response.data;

                    this.user   = data.user;
                    this.gameId = data.gameId;
                    this.myTurn = data.myTurn;

                    this.URL = {
                        attack:     this.buildUrl('attack'),
                        placeShip:  this.buildUrl('placeShip'),
                        checkMoves: this.buildUrl('checkMoves'),
                    };

                    this.buildTheFields();
                    this.checkMoves();
                    this.addShips();
                    this.insertGameId();
                    this.hideStartGameModal();
                    this.shipSizes.forEach(size => this.totalMaxHits += size * this.nrOfShipsPerSize);
                    hideLoadingPage();

                    document.getElementById('startGame').removeEventListener('click', this.getStarted);
                    return;
                }

                hideLoadingPage();
                return this.displayMessage(response.message);
            });
        } catch (error) {
            return this.displayMessage(error);
        }
    }

    addShips()
    {
        this.shipSizes.forEach(size => {
            for(let i = 0; i < this.nrOfShipsPerSize; i++) {
                const shipsContainer = document.getElementById('ships');
                const ship = document.createElement('div');
                ship.classList.add(`ship-${size}`);
                ship.dataset.length = size;
                shipsContainer.appendChild(ship);
            }
        });
    }

    insertGameId()
    {
        const gameIdCont  = document.querySelector('#gameDetails #theGameId');
        gameIdCont.innerText = `Joc: ${this.gameId}`;
    }

    displayMessage(message = '')
    {
        if (!this.modal.classList.contains('show')) {
            this.modalCont.innerHTML = message;
            this.modal.classList.add('show');

            this.modal.removeEventListener('click', this.hideModal);
            this.modal.addEventListener('click', this.hideModal);
        }
    }

    hideModal(event)
    {
        if (event.currentTarget !== this.modal) {
            return;
        }
        if (this.modal.classList.contains('show')) {
            if (this.callback) {
                this.callback();
                this.callback = null;
            }
            this.modal.classList.remove('show');
        }
    }

    static showLoading()
    {
        if (!document.getElementById('loading').classList.contains('show')) {
            document.getElementById('loading').classList.add('show');
        }
    }

    static hideLoading()
    {
        if (document.getElementById('loading').classList.contains('show')) {
            document.getElementById('loading').classList.remove('show');
        }
    }

    selectShip(event) {
        const selectedShip = event.target;
        if (!selectedShip || !selectedShip.dataset?.length || selectedShip?.classList.contains('placed')) {
            return;
        }
        if (this.selectedShip || this.selectedShip?.classList.contains('placed')) {
            this.selectedShip.classList.remove('selected');
            this.selectedShip = null;
        }
        selectedShip.classList.add('selected');
        this.selectedShip = selectedShip;
    }

    rotateShip(event) {
        if (
            this.selectedShip && 
            (!event.key || (event.key && (event.key === 'r' || event.key === 'R')))
        ) {
            const shipLen = this.selectedShip.dataset.length;
            if (this.selectedShip.classList.contains('rotated')) {
                this.selectedShip.classList.remove('rotated');
                this.selectedShip.classList.remove(`ship-${shipLen}-r`);
                this.selectedShip.classList.add(`ship-${shipLen}`);
                return;
            }
            this.selectedShip.classList.remove(`ship-${shipLen}`);
            this.selectedShip.classList.add(`ship-${shipLen}-r`);
            this.selectedShip.classList.add('rotated');
        }
    }

    async startDraging(e) {
        if (!this.selectedShip) {
            return;
        }
        if (e.cancelable) {
            e.preventDefault();
        }
        this.buttonDown = true;
        document.addEventListener('touchdown', this.preventPageFromScrolling);
        document.addEventListener('touchmove', this.preventPageFromScrolling, { passive: false });
        setTimeout(() => {
            if (this.buttonDown) {
                document.removeEventListener('pointermove', this.dragSelectedShip);
                document.addEventListener('pointermove', this.dragSelectedShip);
            }
        }, 250);
    }

    preventPageFromScrolling(e) {
        if (e.cancelable) {
            e.preventDefault();
        }
    }

    endDraging(e) {
        if (!this.selectedShip) {
            return;
        }
        document.removeEventListener('pointermove', this.dragSelectedShip);
        document.removeEventListener('touchmove', this.preventPageFromScrolling, { passive: false });
        this.buttonDown = false;
        if (e.cancelable) {
            e.preventDefault();
        }
        if (this.isDrag) {
            let topFields = document.elementsFromPoint(this.selectedShip.offsetLeft, this.selectedShip.offsetTop);
            topFields.forEach(field => {
                if (field.classList.contains('field-ship')) {
                    this.isDrag = false;
                    return this.placeShip(field, this.selectedShip);
                }
            });
        }
    }

    dragSelectedShip(event) {
        if (event.cancelable) {
            event.preventDefault();
        }
        if (!this.selectedShip) {
            return;
        }
        this.isDrag = true;
        const shipWidth = this.selectedShip.offsetWidth;
        const shipHeight = this.selectedShip.offsetHeight;
        this.selectedShip.style.top = (event.clientY - (shipHeight / 2)) + "px";
        this.selectedShip.style.left = (event.clientX - (shipWidth / 2)) + "px";
    }

    waitSomeTime(miliseconds) {
        return new Promise((resolve) => {
            setTimeout(() => resolve(true), miliseconds);
        });
    }

    placeShip(field, ship) {
        Game.showLoading();

        const shipRotated = ship.classList.contains('rotated');
        let placedShip = [field.id];
        let fieldX = field.dataset.x;
        let fieldY = field.dataset.y;
        for(let y = 1; y < ship.dataset.length; y++) {
            if (shipRotated) {
                fieldX = Number(fieldX) + 1;
            } else {
                fieldY = Number(fieldY) + 1;
            }
            let nextField = document.querySelector(`.field-ship[data-x="${fieldX}"][data-y="${fieldY}"]`);
            if (!nextField) return Game.hideLoading();
            placedShip.push(nextField.id);
        }

        post(this.URL.placeShip, { shipFields: placedShip }).then((response) => {
            if(response.status === 'ok') {
                ship.style.top = null;
                ship.style.left = null;
                const fieldSize = window.innerWidth <= 400 ? 20 : 25;
                ship.style.marginTop = ((field.dataset.y - 1) * fieldSize) + 'px';
                ship.style.marginLeft = ((field.dataset.x - 1) * fieldSize) + 'px';
                ship.style.cursor = 'default';
                ship.classList.add('placed');
                ship.classList.remove('selected');
                this.selectedShip = null;
            } else {
                this.displayMessage(response.message);
            }
            return Game.hideLoading();
        });
    }
}
