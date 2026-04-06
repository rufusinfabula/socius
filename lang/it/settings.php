<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This file is part of Socius and is licensed under the GNU GPL v3.
 */

declare(strict_types=1);

return [

    // -------------------------------------------------------------------------
    // Titolo pagina
    // -------------------------------------------------------------------------
    'settings'               => 'Impostazioni',

    // -------------------------------------------------------------------------
    // Tab
    // -------------------------------------------------------------------------
    'tab_association'        => 'Associazione',
    'tab_social_year'        => 'Anno sociale',
    'tab_categories'         => 'Categorie soci',
    'tab_board_roles'        => 'Ruoli direttivo',
    'tab_interface'          => 'Interfaccia',
    'tab_email'              => 'Email',
    'tab_member_number'      => 'Numero socio',

    // -------------------------------------------------------------------------
    // Associazione
    // -------------------------------------------------------------------------
    'assoc_name'             => 'Nome associazione',
    'assoc_fiscal_code'      => 'Codice fiscale',
    'assoc_vat_number'       => 'Partita IVA',
    'assoc_address'          => 'Sede legale',
    'assoc_city'             => 'Città',
    'assoc_postal_code'      => 'CAP',
    'assoc_province'         => 'Provincia',
    'assoc_country'          => 'Paese',
    'assoc_email'            => 'Email ufficiale',
    'assoc_phone'            => 'Telefono',
    'assoc_website'          => 'Sito web',
    'assoc_logo'             => 'Logo',
    'assoc_logo_upload'      => 'Carica logo',
    'assoc_logo_current'     => 'Logo attuale',
    'assoc_logo_hint'        => 'PNG, JPG o SVG — max 2 MB',
    'assoc_logo_remove'      => 'Nessun logo caricato.',

    // -------------------------------------------------------------------------
    // Anno sociale
    // -------------------------------------------------------------------------
    'renewal_open'           => 'Apertura rinnovi',
    'renewal_first_reminder' => 'Prima comunicazione',
    'renewal_second_reminder'=> 'Secondo sollecito',
    'renewal_third_reminder' => 'Terzo sollecito',
    'renewal_close'          => 'Chiusura rinnovi',
    'renewal_lapse'          => 'Decadenza automatica',
    'renewal_approval'       => 'Approvazione comunicazioni',
    'renewal_approval_desc'  => 'Le comunicazioni di rinnovo richiedono approvazione prima dell\'invio',
    'renewal_date_hint'      => 'Formato MM-GG (mese-giorno), es. 11-15',
    'renewal_calendar_title' => 'Ciclo dell\'anno sociale',

    // -------------------------------------------------------------------------
    // Categorie soci
    // -------------------------------------------------------------------------
    'cat_new'                => 'Nuova categoria',
    'cat_edit'               => 'Modifica categoria',
    'cat_slug'               => 'Slug (solo lettere minuscole e _)',
    'cat_label'              => 'Nome visualizzato',
    'cat_description'        => 'Descrizione',
    'cat_annual_fee'         => 'Quota annuale (€)',
    'cat_is_free'            => 'Gratuita (nessuna quota)',
    'cat_is_exempt'          => 'Esente da rinnovo',
    'cat_requires_approval'  => 'Richiede approvazione',
    'cat_valid_from'         => 'Valida dal',
    'cat_valid_until'        => 'Valida fino al',
    'cat_sort_order'         => 'Ordine',
    'cat_is_active'          => 'Attiva',
    'cat_fee_history'        => 'Quote annuali',
    'cat_fee_add'            => 'Aggiungi/modifica quota per anno',
    'cat_fee_year'           => 'Anno',
    'cat_fee_amount'         => 'Quota (€)',
    'cat_fee_note'           => 'Nota',
    'cat_no_fees'            => 'Nessuna quota registrata.',
    'cat_toggle_active'      => 'Attiva/Disattiva',
    'cat_none'               => 'Nessuna categoria. Creane una.',

    // -------------------------------------------------------------------------
    // Ruoli direttivo
    // -------------------------------------------------------------------------
    'role_new'               => 'Nuovo ruolo',
    'role_edit'              => 'Modifica ruolo',
    'role_slug'              => 'Slug (solo lettere minuscole e _)',
    'role_label'             => 'Nome visualizzato',
    'role_description'       => 'Descrizione',
    'role_is_board_member'   => 'Membro del direttivo',
    'role_can_sign'          => 'Può firmare atti ufficiali',
    'role_sort_order'        => 'Ordine',
    'role_is_active'         => 'Attivo',
    'role_toggle_active'     => 'Attiva/Disattiva',
    'role_none'              => 'Nessun ruolo. Creane uno.',

    // -------------------------------------------------------------------------
    // Interfaccia
    // -------------------------------------------------------------------------
    'theme'                  => 'Tema grafico',
    'theme_uikit'            => 'UIkit — tema predefinito',
    'theme_bootstrap'        => 'Bootstrap',
    'theme_tailwind'         => 'Tailwind CSS',
    'theme_wip_warning'      => 'Attenzione: il tema selezionato è ancora in sviluppo (WIP) e potrebbe non funzionare correttamente.',
    'language'               => 'Lingua interfaccia',
    'date_format'            => 'Formato data',
    'date_format_preview'    => 'Anteprima: ',
    'timezone'               => 'Fuso orario',

    // -------------------------------------------------------------------------
    // Email SMTP
    // -------------------------------------------------------------------------
    'smtp_host'              => 'Server SMTP',
    'smtp_port'              => 'Porta',
    'smtp_encryption'        => 'Cifratura',
    'smtp_encryption_tls'    => 'TLS (STARTTLS)',
    'smtp_encryption_ssl'    => 'SSL',
    'smtp_encryption_none'   => 'Nessuna',
    'smtp_username'          => 'Utente',
    'smtp_password'          => 'Password',
    'smtp_password_hint'     => 'Lascia vuoto per mantenere la password attuale.',
    'smtp_from'              => 'Indirizzo mittente',
    'smtp_from_name'         => 'Nome mittente',
    'smtp_test'              => 'Invia email di test',
    'smtp_test_ok'           => 'Email di test inviata con successo.',
    'smtp_test_fail'         => 'Invio fallito: :error',

    // -------------------------------------------------------------------------
    // Numero socio
    // -------------------------------------------------------------------------
    'number_start'              => 'Numero iniziale installazione',
    'number_start_desc'         => 'Questo valore viene usato solo quando non esistono ancora soci nel sistema. Dopo il primo socio il sistema usa sempre MAX+1 automaticamente.',
    'number_start_hint'         => 'Usato solo per il primissimo socio creato.',
    'number_start_inactive'     => 'Non applicabile — esistono già soci nel sistema. Il prossimo numero viene calcolato automaticamente.',
    'number_start_save'         => 'Salva numero iniziale',
    'number_start_invalid'      => 'Il numero iniziale deve essere almeno 1.',
    'number_start_collision'    => 'Esiste già un socio con il numero :number.',
    'number_start_below_max'    => 'Salvato. Il sistema assegnerà comunque :max + 1 ai nuovi soci (questo valore viene usato solo se non esistono ancora soci).',
    'number_stat_count'         => 'Soci registrati',
    'number_stat_last'          => 'Ultimo assegnato',
    'number_stat_next'          => 'Prossimo',
    'number_current_max'        => 'Ultimo numero assegnato',
    'number_reset'              => 'Reimposta contatore',
    'number_reset_desc'         => 'Imposta il numero progressivo da cui partirà la prossima assegnazione.',
    'number_reset_warn'         => 'Il nuovo valore deve essere superiore all\'ultimo numero assegnato.',
    'number_reset_confirm'      => 'Sei sicuro di voler reimpostare il contatore?',

    // -------------------------------------------------------------------------
    // Comuni
    // -------------------------------------------------------------------------
    'saved_ok'               => 'Impostazioni salvate.',
    'save'                   => 'Salva impostazioni',
    'edit'                   => 'Modifica',
    'add'                    => 'Aggiungi',
    'cancel'                 => 'Annulla',
    'active'                 => 'Attivo',
    'inactive'               => 'Non attivo',
    'yes'                    => 'Sì',
    'no'                     => 'No',

];
