# Portale-Studenti
Un portale accademico completo sviluppato in PHP per la gestione di corsi, compiti, voti e materiali didattici.

##   Funzionalità Principali
### Area Studente
- Dashboard Intuitiva: Visualizzazione dei compiti in scadenza e alertat in rosso se < 3 giorni.
- Gestione Compiti: Upload di elaborati massimo 10mb e monitoraggio feedback.
- Libretto Online: Visualizzazione voti, calcolo media automatica ed export in formato CSV.
- Materiali: Download di dispense e slide caricate dai docenti.
- Comunicazione: Sistema di comunicazione con i professori.

### Area Professore
- Gestione Corsi: Creazione e modifica dei corsi.
- Assegnazione Compiti: Creazione compiti con allegati e scadenze fisse.
- Valutazione: Correzione delle consegne con inserimento voti e feedback testuale.
- Statistiche: Grafici usando Chart.js sull'andamento della classe e tasso di consegna.

### Area Admin
- Controllo Totale: Gestione CRUD (Create, Read, Update, Delete) di utenti e corsi.
- Import: Caricamento di liste studenti tramite file CSV.
- Reportistica: Esportazione dei dati del sistema.

## Struttura del Progetto
portale_studenti/<br>
├── classi/                 # Classi PHP (Utente, Corso, Voto, ecc.)<br>
├── config/                # Configurazione DB, costanti, parametri globali<br>
├── inclusi/                # Header, footer, controllo sessioni, funzioni comuni<br>
├── importazioni/           # File CSV, upload compiti, dati esterni<br>
├── assets/                 # CSS, JS, immagini<br>
│   ├── css/<br>
│   ├── js/<br>
│   └── img/<br>
├── admin/                   # Area riservata admin<br>
├── professore/             # Area riservata professori<br>
├── studente/               # Area riservata studenti<br>
├── login.php               # Login<br>
├── logout.php              # Logout<br>
└──  index.php               # Pagina home<br>








