WEBAPP TORNEO U12 2026

REQUISITI
- Hosting con PHP 8.1 o successivo
- Estensione PDO_SQLite abilitata
- Cartella data scrivibile dal processo web

INSTALLAZIONE
1. Caricare tutti i file sullo stesso spazio web, mantenendo le cartelle assets e data.
2. Impostare la variabile ambiente ADMIN_PASSWORD sul server. Se non viene impostata, la password iniziale è ChangeMe-U12-2026 e deve essere sostituita prima della pubblicazione.
3. Verificare che la cartella data sia scrivibile. Il database SQLite viene creato alla prima apertura.
4. Aprire index.php.
5. Accedere direttamente ad admin.php: la pagina non è collegata nel menu pubblico.

PAGINE PUBBLICHE IN INGLESE
- index.php: home
- schedule.php: calendario e risultati
- live.php: risultati live, refresh ogni 15 secondi
- standings.php: classifiche, classifica avulsa e differenza punti
- stats.php: riepilogo lanciatori, battitori e MVP

AREA AMMINISTRATIVA
- admin.php: stato gara, punteggi, lanciatori, battitori, MVP e note
- Save and publish salva nel database condiviso: i dati sono quindi visibili a tutti i visitatori

CLASSIFICHE E FINALI
Le classifiche considerano solo le gare di qualificazione completate e non accettano un pareggio ai fini del calcolo. Ordinamento: percentuale vittorie, percentuale nella mini-classifica tra squadre pari, differenza punti nella mini-classifica, differenza punti generale, punti segnati. Gli accoppiamenti delle finali vengono aggiornati automaticamente per le finali ancora nello stato Scheduled.

SICUREZZA
La pagina admin non è nel menu, ma il nome nascosto non è una protezione. È protetta da password e token CSRF. Usare HTTPS, una password lunga e backup regolari del file data/tournament.sqlite. Su server non Apache proteggere esplicitamente la cartella data e i file config.php e seed_games.json.
