import API from './api.js';

/**
 * AuthManager Class - Beheert authenticatie
 * Deze class zorgt voor login, registratie en sessie beheer
 */
class AuthManager {
    constructor(api) {
        this.api = api;
        this.currentUser = null;
    }
    
    /**
     * Initialiseer auth manager
     */
    async init() {
        try {
            const response = await this.api.getCurrentUser();
            if (response.success) {
                this.currentUser = response.user;
                this.updateUI();
                return true;
            }
        } catch (error) {
            console.log('Niet ingelogd');
        }
        this.updateUI();
        return false;
    }
    
    /**
     * Log in een gebruiker
     */
    async login(email, wachtwoord) {
        try {
            const response = await this.api.login(email, wachtwoord);
            if (response.success) {
                await this.init();
                return { success: true, message: response.message };
            }
            return { success: false, message: response.message };
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Registreer een nieuwe gebruiker
     */
    async register(voornaam, achternaam, email, wachtwoord) {
        try {
            const response = await this.api.register(voornaam, achternaam, email, wachtwoord);
            return response;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Log uit de huidige gebruiker
     */
    async logout() {
        try {
            await this.api.logout();
            this.currentUser = null;
            this.updateUI();
            return { success: true };
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Update de UI op basis van login status
     */
    updateUI() {
        const navAuth = document.getElementById('navAuth');
        const navUser = document.getElementById('navUser');
        const userName = document.getElementById('userName');
        const nieuweActiviteitLink = document.getElementById('nieuweActiviteitLink');
        const notificatieBell = document.getElementById('notificatieBell');
        
        if (this.currentUser) {
            // Gebruiker is ingelogd
            if (navAuth) navAuth.style.display = 'none';
            if (navUser) {
                navUser.style.display = 'flex';
                if (userName) userName.textContent = this.currentUser.voornaam + ' ' + this.currentUser.achternaam;
            }
            if (nieuweActiviteitLink) nieuweActiviteitLink.style.display = 'block';
            if (notificatieBell) notificatieBell.style.display = 'block';
        } else {
            // Gebruiker is niet ingelogd
            if (navAuth) navAuth.style.display = 'flex';
            if (navUser) navUser.style.display = 'none';
            if (nieuweActiviteitLink) nieuweActiviteitLink.style.display = 'none';
            if (notificatieBell) notificatieBell.style.display = 'none';
        }
    }
    
    /**
     * Check of gebruiker is ingelogd
     */
    isLoggedIn() {
        return this.currentUser !== null;
    }
    
    /**
     * Haal huidige gebruiker op
     */
    getCurrentUser() {
        return this.currentUser;
    }
}

export default AuthManager;

