<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

declare(strict_types=1);

return [
    // Login
    'login'                 => 'Accedi',
    'login_heading'         => 'Accedi al pannello',
    'email'                 => 'Indirizzo email',
    'email_placeholder'     => 'nome@esempio.it',
    'password'              => 'Password',
    'remember_me'           => 'Ricordami',
    'forgot_password'       => 'Password dimenticata?',
    'no_account'            => 'Non hai un account? Contatta l\'amministratore.',

    // Errors
    'invalid_credentials'   => 'Credenziali non valide. Controlla email e password.',
    'account_inactive'      => 'Il tuo account non è attivo. Contatta l\'amministratore.',
    'too_many_attempts'     => 'Troppi tentativi di accesso. Riprova tra :minutes minuti.',
    'csrf_invalid'          => 'Sessione scaduta. Ricarica la pagina e riprova.',

    // Logout
    'logout'                => 'Esci',
    'logged_out'            => 'Hai effettuato il logout con successo.',

    // Forgot password
    'forgot_heading'        => 'Recupera la password',
    'forgot_intro'          => 'Inserisci il tuo indirizzo email. Riceverai un link per reimpostare la password.',
    'send_reset_link'       => 'Invia link di recupero',
    'reset_link_sent'       => 'Se l\'indirizzo email è registrato, riceverai a breve un link per reimpostare la password.',
    'back_to_login'         => 'Torna al login',

    // Reset password
    'reset_heading'         => 'Nuova password',
    'reset_intro'           => 'Scegli una nuova password per il tuo account.',
    'new_password'          => 'Nuova password',
    'confirm_password'      => 'Conferma password',
    'reset_password'        => 'Reimposta password',
    'passwords_mismatch'    => 'Le due password non coincidono.',
    'password_too_short'    => 'La password deve essere di almeno 8 caratteri.',
    'reset_token_invalid'   => 'Il link di recupero non è valido o è scaduto.',
    'reset_success'         => 'Password aggiornata con successo. Accedi con le nuove credenziali.',

    // Email subjects
    'email_reset_subject'   => 'Recupero password — :app_name',
    'email_reset_body'      => "Hai richiesto il recupero della password.\n\nClicca sul link seguente per impostare una nuova password (valido per 1 ora):\n\n:url\n\nSe non hai richiesto il recupero della password, ignora questa email.\n\n— :app_name",
];
