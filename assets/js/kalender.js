import API from './api.js';

/**
 * Kalender Class - Beheert de kalender weergave
 * Deze class toont activiteiten in een maandelijkse kalender
 */
class Kalender {
    constructor(api) {
        this.api = api;
        this.currentDate = new Date();
        this.activiteiten = [];
    }
    
    /**
     * Initialiseer de kalender
     */
    async init() {
        await this.loadActiviteiten();
        this.render();
        this.setupEventListeners();
    }
    
    /**
     * Laad activiteiten voor de huidige maand
     */
    async loadActiviteiten() {
        try {
            const jaar = this.currentDate.getFullYear();
            const maand = this.currentDate.getMonth() + 1;
            const response = await this.api.getKalenderActiviteiten(jaar, maand);
            if (response.success) {
                this.activiteiten = response.activiteiten;
            }
        } catch (error) {
            console.error('Fout bij laden activiteiten:', error);
            this.activiteiten = [];
        }
    }
    
    /**
     * Render de kalender
     */
    async render() {
        const grid = document.getElementById('kalenderGrid');
        const monthHeader = document.getElementById('currentMonth');
        
        if (!grid || !monthHeader) return;
        
        // Update maand header
        const maandNamen = ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni',
                           'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'];
        monthHeader.textContent = `${maandNamen[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        
        // Leeg de grid
        grid.innerHTML = '';
        
        // Voeg dag headers toe
        const dagNamen = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
        dagNamen.forEach(dag => {
            const header = document.createElement('div');
            header.className = 'kalender-dag-header';
            header.textContent = dag;
            grid.appendChild(header);
        });
        
        // Bereken eerste dag van de maand en hoeveel dagen
        const eersteDag = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
        const laatsteDag = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
        const startDag = (eersteDag.getDay() + 6) % 7; // Maandag = 0
        const aantalDagen = laatsteDag.getDate();
        
        // Voeg lege cellen toe voor dagen voor de maand
        for (let i = 0; i < startDag; i++) {
            const dag = document.createElement('div');
            dag.className = 'kalender-dag andere-maand';
            grid.appendChild(dag);
        }
        
        // Voeg dagen van de maand toe
        const vandaag = new Date();
        for (let dag = 1; dag <= aantalDagen; dag++) {
            const dagElement = document.createElement('div');
            const datum = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), dag);
            
            dagElement.className = 'kalender-dag';
            
            // Check of dit vandaag is
            if (datum.toDateString() === vandaag.toDateString()) {
                dagElement.classList.add('vandaag');
            }
            
            // Voeg dag nummer toe
            const dagNummer = document.createElement('div');
            dagNummer.textContent = dag;
            dagNummer.style.fontWeight = 'bold';
            dagElement.appendChild(dagNummer);
            
            // Voeg activiteiten toe voor deze dag
            const dagActiviteiten = this.activiteiten.filter(act => {
                const actDatum = new Date(act.datum);
                return actDatum.getDate() === dag &&
                       actDatum.getMonth() === this.currentDate.getMonth() &&
                       actDatum.getFullYear() === this.currentDate.getFullYear();
            });
            
            dagActiviteiten.forEach(activiteit => {
                const activiteitElement = document.createElement('div');
                activiteitElement.className = 'kalender-activiteit';
                activiteitElement.textContent = activiteit.titel;
                activiteitElement.title = `${activiteit.titel} - ${activiteit.tijd}`;
                activiteitElement.onclick = () => {
                    window.activiteitManager.showDetail(activiteit.id);
                };
                dagElement.appendChild(activiteitElement);
            });
            
            grid.appendChild(dagElement);
        }
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');
        
        if (prevBtn) {
            prevBtn.onclick = () => {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.loadActiviteiten().then(() => this.render());
            };
        }
        
        if (nextBtn) {
            nextBtn.onclick = () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.loadActiviteiten().then(() => this.render());
            };
        }
    }
}

export default Kalender;

