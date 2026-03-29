# Socius

**Socius** è un sistema web open source installabile per la gestione di associazioni.
Permette di gestire soci, quote, eventi, comunicazioni e pagamenti da un'unica interfaccia.

## Requisiti

- PHP 8.1+
- MySQL 8.0+ / MariaDB 10.6+
- Composer
- Web server: Apache (mod_rewrite) o Nginx
- Estensioni PHP: `pdo`, `pdo_mysql`, `mbstring`, `json`, `openssl`

## Installazione

```bash
# 1. Clona il repository nella document root del tuo server
git clone https://github.com/rufusinfabula/socius.git .

# 2. Installa le dipendenze
composer install --no-dev --optimize-autoloader

# 3. Copia il file di configurazione
cp .env.example .env

# 4. Modifica .env con i tuoi parametri (DB, SMTP, pagamenti…)

# 5. Avvia il wizard di installazione via browser
# https://tuo-dominio.org/install
```

## Struttura del progetto

```
socius/
├── app/
│   ├── Core/           # Kernel, router, container DI, request/response
│   ├── Controllers/    # HTTP controllers
│   ├── Models/         # Modelli dati (PDO)
│   ├── Services/       # Logica di business
│   ├── Jobs/           # Task asincroni / code
│   └── Views/          # Template PHP
├── config/             # File di configurazione
├── install/            # Wizard di installazione web
├── lang/
│   ├── it/             # Italiano
│   └── en/             # English
├── public/             # Document root (index.php, .htaccess)
├── storage/
│   ├── cache/
│   ├── exports/
│   ├── logs/
│   └── uploads/
└── tests/
```

## Licenza

[GNU General Public License v3.0](LICENSE)
