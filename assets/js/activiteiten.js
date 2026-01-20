import API from './api.js';

/**
 * ActiviteitManager Class - Beheert activiteiten
 * Deze class zorgt voor het tonen, filteren en beheren van activiteiten
 */
class ActiviteitManager {
    constructor(api) {
        this.api = api;
        this.activiteiten = [];
        this.filters = {};
    }
    
    /**
     * Initialiseer de activiteit manager
     */
    async init() {
        await this.loadActiviteiten();
        this.setupEventListeners();
    }
    
    /**
     * Laad activiteiten met filters
     */
    async loadActiviteiten() {
        try {
            const response = await this.api.getActiviteiten(this.filters);
            if (response.success) {
                this.activiteiten = response.activiteiten;
                this.render();
            }
        } catch (error) {
            console.error('Fout bij laden activiteiten:', error);
            this.activiteiten = [];
            this.render();
        }
    }
    
    /**
     * Render activiteiten lijst
     */
    render() {
        const container = document.getElementById('activiteitenLijst');
        if (!container) return;
        
        if (this.activiteiten.length === 0) {
            container.innerHTML = '<p>Geen activiteiten gevonden.</p>';
            return;
        }
        
        container.innerHTML = this.activiteiten.map(activiteit => {
            const datum = new Date(activiteit.datum);
            const datumStr = datum.toLocaleDateString('nl-NL', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            return `
                <div class="activiteit-card" onclick="window.activiteitManager.showDetail(${activiteit.id})">
                    <div class="activiteit-header">
                        <div>
                            <h3 class="activiteit-titel">${activiteit.titel}</h3>
                            <div class="activiteit-meta">
                                <span>📅 ${datumStr}</span>
                                <span>🕐 ${activiteit.tijd}</span>
                                <span>📍 ${activiteit.locatie}</span>
                                <span>👤 ${activiteit.voornaam} ${activiteit.achternaam}</span>
                            </div>
                        </div>
                    </div>
                    ${activiteit.beschrijving ? `<p>${activiteit.beschrijving}</p>` : ''}
                    <div class="activiteit-badges">
                        <span class="badge badge-${activiteit.soort}">${activiteit.soort}</span>
                        <span class="badge badge-${activiteit.status}">${activiteit.status}</span>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    /**
     * Toon detail van een activiteit
     */
    async showDetail(id) {
        try {
            const response = await this.api.getActiviteit(id);
            if (response.success) {
                const activiteit = response.activiteit;
                this.renderDetail(activiteit);
            }
        } catch (error) {
            console.error('Fout bij laden activiteit:', error);
        }
    }
    
    /**
     * Render activiteit detail
     */
    async renderDetail(activiteit) {
        const modal = document.getElementById('activiteitModal');
        const container = document.getElementById('activiteitDetail');
        
        if (!modal || !container) return;
        
        const datum = new Date(activiteit.datum);
        const datumStr = datum.toLocaleDateString('nl-NL', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Haal weer info op als het een buitenactiviteit is
        let weerHTML = '';
        if (activiteit.soort === 'buiten') {
            try {
                console.log('Weer ophalen voor activiteit:', activiteit.id);
                const weerResponse = await this.api.getWeer(activiteit.id);
                console.log('Weer response:', weerResponse);
                
                if (weerResponse.success && weerResponse.weer) {
                    const weer = weerResponse.weer;
                    // Controleer of alle data aanwezig is
                    if (weer.temperatuur !== null && weer.temperatuur !== undefined) {
                        weerHTML = `
                            <div class="weer-info" style="background: #f0f8ff; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                                <h4>🌤️ Weersinformatie voor ${activiteit.locatie}</h4>
                                ${weer.icon ? `<img src="http://openweathermap.org/img/wn/${weer.icon}@2x.png" alt="Weer" style="float: right;">` : ''}
                                ${weer.beschrijving ? `<p><strong>${weer.beschrijving}</strong></p>` : ''}
                                <div class="weer-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
                                    ${weer.temperatuur !== null ? `<div>🌡️ <strong>Temperatuur:</strong> ${Math.round(weer.temperatuur)}°C</div>` : ''}
                                    ${weer.wind !== null ? `<div>💨 <strong>Wind:</strong> ${weer.wind} m/s</div>` : ''}
                                    ${weer.vochtigheid !== null ? `<div>💧 <strong>Vochtigheid:</strong> ${weer.vochtigheid}%</div>` : ''}
                                </div>
                            </div>
                        `;
                    } else {
                        weerHTML = '<p class="message message-warning">⚠️ Weerdata ontbreekt. Probeer het later opnieuw.</p>';
                    }
                } else {
                    weerHTML = `<p class="message message-warning">⚠️ ${weerResponse.message || 'Kon weerinformatie niet laden.'}</p>`;
                }
            } catch (error) {
                console.error('Weer API error:', error);
                weerHTML = `<p class="message message-error">❌ Kon weerinformatie niet laden: ${error.message || 'Onbekende fout'}. Controleer of de API key geldig is.</p>`;
            }
        }
        
        // Haal deelnemers op
        let deelnemersHTML = '';
        try {
            const deelnemersResponse = await this.api.getDeelnemers(activiteit.id);
            if (deelnemersResponse.success) {
                const deelnemers = deelnemersResponse.deelnemers;
                if (deelnemers.length > 0) {
                    deelnemersHTML = `
                        <div class="deelnemers">
                            <h4>Deelnemers (${deelnemers.length})</h4>
                            <ul>
                                ${deelnemers.map(d => `<li>${d.voornaam} ${d.achternaam}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Fout bij laden deelnemers:', error);
        }
        
        container.innerHTML = `
            <h2>${activiteit.titel}</h2>
            <div class="activiteit-meta">
                <p><strong>Datum:</strong> ${datumStr}</p>
                <p><strong>Tijd:</strong> ${activiteit.tijd}</p>
                <p><strong>Locatie:</strong> ${activiteit.locatie}</p>
                <p><strong>Soort:</strong> ${activiteit.soort}</p>
                <p><strong>Status:</strong> ${activiteit.status}</p>
            </div>
            ${activiteit.beschrijving ? `<p><strong>Beschrijving:</strong><br>${activiteit.beschrijving}</p>` : ''}
            ${activiteit.opmerkingen ? `<p><strong>Opmerkingen:</strong><br>${activiteit.opmerkingen}</p>` : ''}
            ${weerHTML}
            ${deelnemersHTML}
            <div class="activiteit-actions" style="margin-top: 1rem;">
                <button class="btn btn-primary" onclick="window.activiteitManager.aanmelden(${activiteit.id})">Aanmelden</button>
                <button class="btn btn-secondary" onclick="window.activiteitManager.uitnodigen(${activiteit.id})">Gast Uitnodigen</button>
            </div>
        `;
        
        modal.style.display = 'block';
    }
    
    /**
     * Aanmelden voor activiteit
     */
    async aanmelden(activiteit_id) {
        try {
            const response = await this.api.aanmeldenVoorActiviteit(activiteit_id);
            if (response.success) {
                alert('Je bent aangemeld voor deze activiteit!');
                await this.showDetail(activiteit_id);
            } else {
                alert(response.message || 'Aanmelden mislukt');
            }
        } catch (error) {
            alert('Er is een fout opgetreden: ' + error.message);
        }
    }
    
    /**
     * Uitnodigen van gast
     */
    async uitnodigen(activiteit_id) {
        const email = prompt('Voer het emailadres in van de persoon die je wilt uitnodigen:');
        if (!email) return;
        
        try {
            const response = await this.api.stuurUitnodiging(activiteit_id, email);
            if (response.success) {
                alert('Uitnodiging verstuurd!');
            } else {
                alert(response.message || 'Uitnodiging mislukt');
            }
        } catch (error) {
            alert('Er is een fout opgetreden: ' + error.message);
        }
    }
    
    /**
     * Maak nieuwe activiteit aan
     */
    async createActiviteit(formData) {
        try {
            const response = await this.api.createActiviteit(formData);
            if (response.success) {
                alert('Activiteit succesvol aangemaakt!');
                document.getElementById('activiteitForm').reset();
                await this.loadActiviteiten();
                return true;
            } else {
                alert(response.message || 'Aanmaken mislukt');
                return false;
            }
        } catch (error) {
            alert('Er is een fout opgetreden: ' + error.message);
            return false;
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Zoek input
        const zoekInput = document.getElementById('zoekInput');
        if (zoekInput) {
            zoekInput.addEventListener('input', (e) => {
                this.filters.zoekterm = e.target.value || undefined;
                this.loadActiviteiten();
            });
        }
        
        // Filters
        const soortFilter = document.getElementById('soortFilter');
        if (soortFilter) {
            soortFilter.addEventListener('change', (e) => {
                this.filters.soort = e.target.value || undefined;
                this.loadActiviteiten();
            });
        }
        
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filters.status = e.target.value || undefined;
                this.loadActiviteiten();
            });
        }
        
        // Activiteit form
        const form = document.getElementById('activiteitForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = {
                    titel: document.getElementById('titel').value,
                    beschrijving: document.getElementById('beschrijving').value,
                    datum: document.getElementById('datum').value,
                    tijd: document.getElementById('tijd').value,
                    locatie: document.getElementById('locatie').value,
                    soort: document.getElementById('soort').value,
                    status: document.getElementById('status').value,
                    opmerkingen: document.getElementById('opmerkingen').value
                };
                
                await this.createActiviteit(formData);
            });
        }
    }
}

export default ActiviteitManager;

