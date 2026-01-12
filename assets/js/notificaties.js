import API from './api.js';

/**
 * NotificatieManager Class - Beheert notificaties
 * Deze class toont en beheert notificaties voor gebruikers
 */
class NotificatieManager {
    constructor(api) {
        this.api = api;
        this.notificaties = [];
    }
    
    /**
     * Initialiseer notificatie manager
     */
    async init() {
        await this.updateBadge();
        this.setupEventListeners();
        
        // Update elke 30 seconden
        setInterval(() => {
            this.updateBadge();
        }, 30000);
    }
    
    /**
     * Update notificatie badge
     */
    async updateBadge() {
        try {
            const response = await this.api.getOngelezenAantal();
            if (response.success) {
                const badge = document.getElementById('notificatieBadge');
                if (badge) {
                    badge.textContent = response.aantal;
                    if (response.aantal > 0) {
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
        } catch (error) {
            console.error('Fout bij updaten badge:', error);
        }
    }
    
    /**
     * Laad notificaties
     */
    async loadNotificaties() {
        try {
            const response = await this.api.getNotificaties(false);
            if (response.success) {
                this.notificaties = response.notificaties;
                this.render();
            }
        } catch (error) {
            console.error('Fout bij laden notificaties:', error);
            this.notificaties = [];
            this.render();
        }
    }
    
    /**
     * Render notificaties
     */
    render() {
        const container = document.getElementById('notificatiesLijst');
        if (!container) return;
        
        if (this.notificaties.length === 0) {
            container.innerHTML = '<p style="padding: 1rem; text-align: center; color: var(--text-light);">Geen notificaties</p>';
            return;
        }
        
        container.innerHTML = this.notificaties.map(notificatie => {
            const datum = new Date(notificatie.aangemaakt_op);
            const tijdStr = datum.toLocaleString('nl-NL');
            
            return `
                <div class="notificatie-item ${notificatie.gelezen ? '' : 'ongelezen'}" 
                     onclick="window.notificatieManager.markeerGelezen(${notificatie.id})">
                    <p>${notificatie.bericht}</p>
                    <div class="notificatie-tijd">${tijdStr}</div>
                </div>
            `;
        }).join('');
    }
    
    /**
     * Markeer notificatie als gelezen
     */
    async markeerGelezen(id) {
        try {
            await this.api.markeerNotificatieGelezen(id);
            await this.loadNotificaties();
            await this.updateBadge();
        } catch (error) {
            console.error('Fout bij markeren als gelezen:', error);
        }
    }
    
    /**
     * Markeer alles als gelezen
     */
    async markeerAllesGelezen() {
        try {
            await this.api.markeerAllesGelezen();
            await this.loadNotificaties();
            await this.updateBadge();
        } catch (error) {
            console.error('Fout bij markeren alles als gelezen:', error);
        }
    }
    
    /**
     * Toggle notificatie panel
     */
    togglePanel() {
        const panel = document.getElementById('notificatiesPanel');
        if (panel) {
            if (panel.style.display === 'none' || !panel.style.display) {
                panel.style.display = 'flex';
                this.loadNotificaties();
            } else {
                panel.style.display = 'none';
            }
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const bell = document.getElementById('notificatieBell');
        if (bell) {
            bell.addEventListener('click', () => {
                this.togglePanel();
            });
        }
        
        const markeerAllesBtn = document.getElementById('markeerAllesGelezen');
        if (markeerAllesBtn) {
            markeerAllesBtn.addEventListener('click', () => {
                this.markeerAllesGelezen();
            });
        }
    }
}

export default NotificatieManager;

