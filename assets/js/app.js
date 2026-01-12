/**
 * Main App Class - Hoofdapplicatie
 * Deze class initialiseert alle modules en beheert de navigatie
 */
import API from './api.js';
import AuthManager from './auth.js';
import Kalender from './kalender.js';
import ActiviteitManager from './activiteiten.js';
import NotificatieManager from './notificaties.js';

class App {
    constructor() {
        this.api = new API();
        this.authManager = new AuthManager(this.api);
        this.kalender = new Kalender(this.api);
        this.activiteitManager = new ActiviteitManager(this.api);
        this.notificatieManager = new NotificatieManager(this.api);
        
        // Maak beschikbaar voor globale toegang
        window.activiteitManager = this.activiteitManager;
        window.notificatieManager = this.notificatieManager;
    }
    
    /**
     * Initialiseer de applicatie
     */
    async init() {
        // Check of gebruiker is ingelogd
        const isLoggedIn = await this.authManager.init();
        
        // Toon home pagina standaard
        this.showPage('home');
        
        if (isLoggedIn) {
            // Initialiseer modules voor ingelogde gebruikers
            await this.kalender.init();
            await this.activiteitManager.init();
            await this.notificatieManager.init();
        }
        
        this.setupNavigation();
        this.setupAuthModals();
        this.setupModalCloses();
        this.setupHomeButtons();
        this.setupFeatureCards();
    }
    
    /**
     * Setup navigatie
     */
    setupNavigation() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = link.getAttribute('data-page');
                
                // Update active state
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                this.showPage(page);
            });
        });
        
        // Markeer home als active bij start
        const homeLink = document.querySelector('.nav-link[data-page="home"]');
        if (homeLink) {
            homeLink.classList.add('active');
        }
        
        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                await this.authManager.logout();
                location.reload();
            });
        }
    }
    
    /**
     * Toon een specifieke pagina
     */
    showPage(pageName) {
        // Update active state in navigatie
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('data-page') === pageName) {
                link.classList.add('active');
            }
        });
        
        // Verberg alle pagina's
        const pages = document.querySelectorAll('.page');
        pages.forEach(page => {
            page.style.display = 'none';
        });
        
        // Toon gevraagde pagina
        const targetPage = document.getElementById(`page-${pageName}`);
        if (targetPage) {
            targetPage.style.display = 'block';
            
            // Check of gebruiker ingelogd is voor beveiligde pagina's
            const beveiligdePaginas = ['kalender', 'activiteiten', 'nieuwe-activiteit'];
            if (beveiligdePaginas.includes(pageName) && !this.authManager.isLoggedIn()) {
                alert('Je moet ingelogd zijn om deze pagina te bekijken. Log eerst in via de knop rechtsboven.');
                this.showPage('home');
                return;
            }
            
            // Herlaad data indien nodig
            if (pageName === 'kalender' && this.authManager.isLoggedIn()) {
                if (this.kalender) {
                    this.kalender.loadActiviteiten().then(() => this.kalender.render());
                }
            } else if (pageName === 'activiteiten' && this.authManager.isLoggedIn()) {
                if (this.activiteitManager) {
                    this.activiteitManager.loadActiviteiten();
                }
            }
        } else {
            console.warn(`Pagina '${pageName}' niet gevonden`);
        }
    }
    
    /**
     * Setup auth modals
     */
    setupAuthModals() {
        // Login modal
        const loginBtn = document.getElementById('loginBtn');
        const loginModal = document.getElementById('loginModal');
        const loginForm = document.getElementById('loginForm');
        
        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                if (loginModal) loginModal.style.display = 'block';
            });
        }
        
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('loginEmail').value.trim();
                const wachtwoord = document.getElementById('loginPassword').value;
                const messageDiv = document.getElementById('loginMessage');
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                
                // Clear previous messages
                messageDiv.innerHTML = '';
                
                // Basic validation
                if (!email || !wachtwoord) {
                    messageDiv.innerHTML = '<div class="message message-error">Vul alle velden in</div>';
                    return;
                }
                
                // Disable button during request
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Inloggen...';
                }
                
                try {
                    const result = await this.authManager.login(email, wachtwoord);
                    
                    if (result.success) {
                        messageDiv.innerHTML = '<div class="message message-success">' + result.message + '</div>';
                        setTimeout(() => {
                            if (loginModal) loginModal.style.display = 'none';
                            location.reload();
                        }, 1000);
                    } else {
                        messageDiv.innerHTML = '<div class="message message-error">' + result.message + '</div>';
                    }
                } catch (error) {
                    messageDiv.innerHTML = '<div class="message message-error">Er is een fout opgetreden. Probeer het opnieuw.</div>';
                } finally {
                    // Re-enable button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Inloggen';
                    }
                }
            });
        }
        
        // Register modal
        const registerBtn = document.getElementById('registerBtn');
        const registerModal = document.getElementById('registerModal');
        const registerForm = document.getElementById('registerForm');
        
        if (registerBtn) {
            registerBtn.addEventListener('click', () => {
                if (registerModal) registerModal.style.display = 'block';
            });
        }
        
        if (registerForm) {
            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const voornaam = document.getElementById('registerVoornaam').value.trim();
                const achternaam = document.getElementById('registerAchternaam').value.trim();
                const email = document.getElementById('registerEmail').value.trim();
                const wachtwoord = document.getElementById('registerPassword').value;
                const messageDiv = document.getElementById('registerMessage');
                const submitBtn = registerForm.querySelector('button[type="submit"]');
                
                // Clear previous messages
                messageDiv.innerHTML = '';
                
                // Basic validation
                if (!voornaam || !achternaam || !email || !wachtwoord) {
                    messageDiv.innerHTML = '<div class="message message-error">Vul alle velden in</div>';
                    return;
                }
                
                if (wachtwoord.length < 6) {
                    messageDiv.innerHTML = '<div class="message message-error">Wachtwoord moet minimaal 6 tekens lang zijn</div>';
                    return;
                }
                
                // Disable button during request
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Registreren...';
                }
                
                try {
                    const result = await this.authManager.register(voornaam, achternaam, email, wachtwoord);
                    
                    if (result.success) {
                        messageDiv.innerHTML = '<div class="message message-success">' + result.message + '</div>';
                        setTimeout(async () => {
                            if (registerModal) registerModal.style.display = 'none';
                            // Auto login na registratie
                            await this.authManager.login(email, wachtwoord);
                            location.reload();
                        }, 1000);
                    } else {
                        messageDiv.innerHTML = '<div class="message message-error">' + result.message + '</div>';
                    }
                } catch (error) {
                    messageDiv.innerHTML = '<div class="message message-error">Er is een fout opgetreden. Probeer het opnieuw.</div>';
                } finally {
                    // Re-enable button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Registreren';
                    }
                }
            });
        }
    }
    
    /**
     * Setup feature cards klikbaar maken
     */
    setupFeatureCards() {
        // Gebruik event delegation voor betere performance
        const featuresContainer = document.querySelector('.features');
        if (featuresContainer) {
            featuresContainer.addEventListener('click', (e) => {
                // Zoek de dichtstbijzijnde feature-card met data-page
                const card = e.target.closest('.feature-card[data-page]');
                if (card) {
                    e.preventDefault();
                    e.stopPropagation();
                    const page = card.getAttribute('data-page');
                    if (page) {
                        console.log('Navigeren naar:', page);
                        this.showPage(page);
                    }
                }
            });
        }
    }
    
    /**
     * Setup modal closes
     */
    setupModalCloses() {
        // Close buttons
        const closes = document.querySelectorAll('.close');
        closes.forEach(close => {
            close.addEventListener('click', () => {
                const modal = close.closest('.modal');
                if (modal) modal.style.display = 'none';
            });
        });
        
        // Close on outside click
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.style.display = 'none';
            }
        });
    }
    
    /**
     * Setup home page buttons
     */
    setupHomeButtons() {
        const homeLoginBtn = document.getElementById('homeLoginBtn');
        const homeRegisterBtn = document.getElementById('homeRegisterBtn');
        
        if (homeLoginBtn) {
            homeLoginBtn.addEventListener('click', () => {
                const loginModal = document.getElementById('loginModal');
                if (loginModal) loginModal.style.display = 'block';
            });
        }
        
        if (homeRegisterBtn) {
            homeRegisterBtn.addEventListener('click', () => {
                const registerModal = document.getElementById('registerModal');
                if (registerModal) registerModal.style.display = 'block';
            });
        }
    }
}

// Initialiseer app wanneer DOM geladen is
document.addEventListener('DOMContentLoaded', () => {
    const app = new App();
    app.init();
});

