/**
 * API Class - Beheert alle API calls
 * Deze class zorgt voor communicatie tussen de frontend en backend
 */
class API {
    constructor() {
        // Gebruik de volledige pathname om subdirectories te ondersteunen
        // Bijvoorbeeld: /project waar is dat feetje/ -> /project waar is dat feetje
        const pathname = window.location.pathname;
        // Verwijder index.php of trailing slash en haal base path
        let basePath = pathname.replace(/\/index\.php$/, '').replace(/\/$/, '');
        // Als basePath leeg is, gebruik dan root
        if (!basePath || basePath === '/') {
            basePath = '';
        }
        this.baseUrl = window.location.origin + basePath;
        console.log('API Base URL:', this.baseUrl); // Debug
    }
    
    /**
     * Basis fetch methode voor alle API calls
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/api/${endpoint}`;
        
        // Debug: log de URL (verwijder in productie)
        console.log('API Request:', url);
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const config = { ...defaultOptions, ...options };
        
        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }
        
        try {
            const response = await fetch(url, config);
            
            // Check of response JSON is
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server gaf geen JSON terug. Controleer of PHP correct werkt.');
            }
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Er is een fout opgetreden');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            if (error.message.includes('JSON')) {
                throw new Error('Server fout: ' + error.message);
            }
            throw error;
        }
    }
    
    // Auth endpoints
    async login(email, wachtwoord) {
        return this.request('auth.php?action=login', {
            method: 'POST',
            body: { email, wachtwoord }
        });
    }
    
    async register(voornaam, achternaam, email, wachtwoord) {
        return this.request('auth.php?action=register', {
            method: 'POST',
            body: { voornaam, achternaam, email, wachtwoord }
        });
    }
    
    async logout() {
        return this.request('auth.php?action=logout', {
            method: 'POST'
        });
    }
    
    async getCurrentUser() {
        return this.request('auth.php?action=current', {
            method: 'GET'
        });
    }
    
    // Activiteiten endpoints
    async getActiviteiten(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`activiteiten.php?${params.toString()}`, {
            method: 'GET'
        });
    }
    
    async getActiviteit(id) {
        return this.request(`activiteiten.php?id=${id}`, {
            method: 'GET'
        });
    }
    
    async createActiviteit(data) {
        return this.request('activiteiten.php', {
            method: 'POST',
            body: data
        });
    }
    
    async updateActiviteit(id, data) {
        return this.request(`activiteiten.php?id=${id}`, {
            method: 'PUT',
            body: data
        });
    }
    
    async deleteActiviteit(id) {
        return this.request(`activiteiten.php?id=${id}`, {
            method: 'DELETE'
        });
    }
    
    async getKalenderActiviteiten(jaar, maand) {
        return this.request(`activiteiten.php?action=kalender&jaar=${jaar}&maand=${maand}`, {
            method: 'GET'
        });
    }
    
    // Deelnemers endpoints
    async aanmeldenVoorActiviteit(activiteit_id) {
        return this.request(`deelnemers.php?activiteit_id=${activiteit_id}`, {
            method: 'POST'
        });
    }
    
    async afmeldenVoorActiviteit(activiteit_id) {
        return this.request(`deelnemers.php?activiteit_id=${activiteit_id}`, {
            method: 'DELETE'
        });
    }
    
    async getDeelnemers(activiteit_id) {
        return this.request(`deelnemers.php?activiteit_id=${activiteit_id}`, {
            method: 'GET'
        });
    }
    
    // Uitnodigingen endpoints
    async stuurUitnodiging(activiteit_id, email) {
        return this.request('uitnodigingen.php', {
            method: 'POST',
            body: { activiteit_id, email }
        });
    }
    
    async accepteerUitnodiging(token) {
        return this.request('uitnodigingen.php', {
            method: 'PUT',
            body: { token }
        });
    }
    
    // Notificaties endpoints
    async getNotificaties(alleenOngelezen = false) {
        return this.request(`notificaties.php?alleen_ongelezen=${alleenOngelezen}`, {
            method: 'GET'
        });
    }
    
    async getOngelezenAantal() {
        return this.request('notificaties.php?action=ongelezen', {
            method: 'GET'
        });
    }
    
    async markeerNotificatieGelezen(id) {
        return this.request('notificaties.php', {
            method: 'PUT',
            body: { id }
        });
    }
    
    async markeerAllesGelezen() {
        return this.request('notificaties.php', {
            method: 'PUT',
            body: { alles: true }
        });
    }
    
    // Weer endpoint
    async getWeer(activiteit_id) {
        return this.request(`weer.php?activiteit_id=${activiteit_id}`, {
            method: 'GET'
        });
    }
}

// Export de API class
export default API;

