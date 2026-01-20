<?php
session_start();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waar is dat feestje? - Activiteiten Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>🎉 Waar is dat feestje?</h1>
            </div>
            <div class="nav-menu" id="navMenu">
                <a href="#" class="nav-link" data-page="home">Home</a>
                <a href="#" class="nav-link" data-page="kalender">Kalender</a>
                <a href="#" class="nav-link" data-page="activiteiten">Activiteiten</a>
                <a href="#" class="nav-link" data-page="nieuwe-activiteit" id="nieuweActiviteitLink" style="display:none;">Nieuwe Activiteit</a>
                <div class="nav-user" id="navUser" style="display:none;">
                    <span id="userName"></span>
                    <button class="btn btn-small" id="logoutBtn">Uitloggen</button>
                </div>
                <div class="nav-auth" id="navAuth">
                    <button class="btn btn-primary" id="loginBtn">Inloggen</button>
                    <button class="btn btn-secondary" id="registerBtn">Registreren</button>
                </div>
            </div>
            <div class="notificatie-bell" id="notificatieBell" style="display:none;">
                🔔 <span class="badge" id="notificatieBadge">0</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container" id="mainContent">
        <!-- Home Page -->
        <div class="page" id="page-home" style="display:block;">
            <div class="hero">
                <h2>Welkom bij Waar is dat feestje?</h2>
                <p>Plan, beheer en deel activiteiten met je team</p>
                <div style="margin-top: 2rem;">
                    <button class="btn btn-primary" id="homeLoginBtn" style="margin-right: 1rem;">Inloggen</button>
                    <button class="btn btn-secondary" id="homeRegisterBtn">Registreren</button>
                </div>
            </div>
            
            <div class="features">
                <div class="feature-card" data-page="kalender" style="cursor: pointer;">
                    <h3>📅 Kalender</h3>
                    <p>Bekijk alle activiteiten in een overzichtelijke kalender. Plan je activiteiten en zie wat er allemaal gebeurt.</p>
                    <div class="feature-link">Bekijk kalender →</div>
                </div>
                <div class="feature-card" data-page="activiteiten" style="cursor: pointer;">
                    <h3>🌤️ Weer Info</h3>
                    <p>Actuele weersinformatie voor buitenactiviteiten. Altijd op de hoogte van het weer voor je evenement.</p>
                    <div class="feature-link">Bekijk activiteiten →</div>
                </div>
                <div class="feature-card" data-page="activiteiten" style="cursor: pointer;">
                    <h3>👥 Uitnodigingen</h3>
                    <p>Nodig gasten uit en beheer deelnemers. Eenvoudig mensen uitnodigen die nog geen account hebben.</p>
                    <div class="feature-link">Bekijk activiteiten →</div>
                </div>
                <div class="feature-card" style="cursor: pointer;" onclick="if(window.notificatieManager) window.notificatieManager.togglePanel();">
                    <h3>🔔 Notificaties</h3>
                    <p>Blijf op de hoogte van wijzigingen. Ontvang meldingen bij nieuwe activiteiten en wijzigingen.</p>
                    <div class="feature-link">Bekijk notificaties →</div>
                </div>
            </div>
            
            <div style="margin-top: 3rem; text-align: center; padding: 2rem; background: var(--card-bg); border-radius: 0.5rem; box-shadow: var(--shadow);">
                <h3>Hoe werkt het?</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 2rem;">
                    <div>
                        <h4>1️⃣ Registreer</h4>
                        <p>Maak een gratis account aan</p>
                    </div>
                    <div>
                        <h4>2️⃣ Maak Activiteiten</h4>
                        <p>Plan binnen- of buitenactiviteiten</p>
                    </div>
                    <div>
                        <h4>3️⃣ Nodig Uit</h4>
                        <p>Deel activiteiten met je team</p>
                    </div>
                    <div>
                        <h4>4️⃣ Geniet!</h4>
                        <p>Beheer alles op één plek</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kalender Page -->
        <div class="page" id="page-kalender" style="display:none;">
            <h2>Kalender</h2>
            <div class="kalender-controls">
                <button class="btn" id="prevMonth">← Vorige</button>
                <h3 id="currentMonth"></h3>
                <button class="btn" id="nextMonth">Volgende →</button>
            </div>
            <div class="kalender-grid" id="kalenderGrid"></div>
        </div>

        <!-- Activiteiten Page -->
        <div class="page" id="page-activiteiten" style="display:none;">
            <h2>Activiteiten</h2>
            <div class="filters">
                <input type="text" id="zoekInput" placeholder="Zoeken...">
                <select id="soortFilter">
                    <option value="">Alle soorten</option>
                    <option value="binnen">Binnen</option>
                    <option value="buiten">Buiten</option>
                </select>
                <select id="statusFilter">
                    <option value="">Alle statussen</option>
                    <option value="gepland">Gepland</option>
                    <option value="geannuleerd">Geannuleerd</option>
                    <option value="voltooid">Voltooid</option>
                </select>
            </div>
            <div class="activiteiten-lijst" id="activiteitenLijst"></div>
        </div>

        <!-- Nieuwe Activiteit Page -->
        <div class="page" id="page-nieuwe-activiteit" style="display:none;">
            <h2>Nieuwe Activiteit Aanmaken</h2>
            <form id="activiteitForm" class="form">
                <div class="form-group">
                    <label>Titel *</label>
                    <input type="text" id="titel" required>
                </div>
                <div class="form-group">
                    <label>Beschrijving</label>
                    <textarea id="beschrijving" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Datum *</label>
                        <input type="date" id="datum" required>
                    </div>
                    <div class="form-group">
                        <label>Tijd *</label>
                        <input type="time" id="tijd" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Locatie *</label>
                    <input type="text" id="locatie" required>
                </div>
                <div class="form-group">
                    <label>Soort *</label>
                    <select id="soort" required>
                        <option value="">Selecteer...</option>
                        <option value="binnen">Binnen</option>
                        <option value="buiten">Buiten</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="status">
                        <option value="gepland">Gepland</option>
                        <option value="geannuleerd">Geannuleerd</option>
                        <option value="voltooid">Voltooid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Opmerkingen</label>
                    <textarea id="opmerkingen" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Activiteit Aanmaken</button>
            </form>
        </div>

        <!-- Notificaties Panel -->
        <div class="notificaties-panel" id="notificatiesPanel" style="display:none;">
            <div class="panel-header">
                <h3>Notificaties</h3>
                <button class="btn btn-small" id="markeerAllesGelezen">Alles als gelezen</button>
            </div>
            <div class="notificaties-lijst" id="notificatiesLijst"></div>
        </div>
    </main>

    <!-- Login Modal -->
    <div class="modal" id="loginModal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeLogin">&times;</span>
            <h2>Inloggen</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="loginEmail" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label>Wachtwoord</label>
                    <input type="password" id="loginPassword" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary">Inloggen</button>
            </form>
            <div id="loginMessage"></div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeRegister">&times;</span>
            <h2>Registreren</h2>
            <form id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>Voornaam</label>
                        <input type="text" id="registerVoornaam" required>
                    </div>
                    <div class="form-group">
                        <label>Achternaam</label>
                        <input type="text" id="registerAchternaam" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="registerEmail" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label>Wachtwoord (minimaal 6 tekens)</label>
                    <input type="password" id="registerPassword" autocomplete="new-password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary">Registreren</button>
            </form>
            <div id="registerMessage"></div>
        </div>
    </div>

    <!-- Activiteit Detail Modal -->
    <div class="modal" id="activiteitModal" style="display:none;">
        <div class="modal-content modal-large">
            <span class="close" id="closeActiviteit">&times;</span>
            <div id="activiteitDetail"></div>
        </div>
    </div>

    <script type="module" src="assets/js/app.js"></script>
</body>
</html>

