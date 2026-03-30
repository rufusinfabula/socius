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

## URL Configuration

Socius supporta due modalità di routing, selezionabili in base all'hosting disponibile.

### Modalità 1 — Query string (default, funziona ovunque)

Nessuna configurazione server richiesta. Funziona su qualsiasi hosting condiviso.

```
https://tuo-dominio.org/index.php?route=login
https://tuo-dominio.org/index.php?route=soci
https://tuo-dominio.org/index.php?route=soci/123
https://tuo-dominio.org/index.php?route=soci/123/modifica
```

Nessuna modifica a `.htaccess` o alla configurazione Nginx è necessaria.

---

### Modalità 2 — URL pulite (opzionale)

Richiede configurazione del server web. Attiva il blocco `mod_rewrite` in
`public/.htaccess` (Apache) oppure aggiungi il blocco `location` in Nginx (vedi sotto).

```
https://tuo-dominio.org/login
https://tuo-dominio.org/soci
https://tuo-dominio.org/soci/123
https://tuo-dominio.org/soci/123/modifica
```

#### Apache

Il file `public/.htaccess` contiene già il blocco necessario, racchiuso in
`<IfModule mod_rewrite.c>`. Assicurati che:

- `mod_rewrite` sia abilitato (`a2enmod rewrite`)
- La direttiva `AllowOverride All` (o almeno `AllowOverride FileInfo`) sia attiva
  per la tua document root in `httpd.conf` / `000-default.conf`

```apache
<Directory /var/www/html/public>
    AllowOverride All
</Directory>
```

#### Nginx

Aggiungi questo blocco `location` al tuo virtualhost (la document root deve
puntare a `public/`):

```nginx
server {
    listen 80;
    server_name tuo-dominio.org;
    root /var/www/socius/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(env|log|sh|sql|bak|cfg|ini) {
        deny all;
    }
}
```

---

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
