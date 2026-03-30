<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 *
 * This file is part of Socius and is licensed under the GNU GPL v3.
 */

declare(strict_types=1);

return [
    // Section headings
    'members'              => 'Soci',
    'member_list'          => 'Elenco soci',
    'new_member'           => 'Nuovo socio',
    'edit_member'          => 'Modifica socio',
    'member_profile'       => 'Profilo socio',
    'add_member'           => 'Aggiungi socio',

    // Table columns
    'membership_number'    => 'N. Tessera',
    'name'                 => 'Nome',
    'surname'              => 'Cognome',
    'full_name'            => 'Nome e cognome',
    'email'                => 'Email',
    'phone'                => 'Telefono',
    'status'               => 'Stato',
    'category'             => 'Categoria',
    'joined_on'            => 'Iscritto dal',
    'actions'              => 'Azioni',

    // Form fields
    'birth_date'           => 'Data di nascita',
    'birth_place'          => 'Luogo di nascita',
    'fiscal_code'          => 'Codice fiscale',
    'address'              => 'Indirizzo',
    'postal_code'          => 'CAP',
    'city'                 => 'Città',
    'province'             => 'Provincia',
    'country'              => 'Paese',
    'notes'                => 'Note (solo admin)',
    'resigned_on'          => 'Data recesso',

    // Status values
    'status_active'        => 'Attivo',
    'status_suspended'     => 'Sospeso',
    'status_expired'       => 'Scaduto',
    'status_resigned'      => 'Dimesso',
    'status_deceased'      => 'Deceduto',

    // Filters
    'filter_all_statuses'  => 'Tutti gli stati',
    'filter_all_categories'=> 'Tutte le categorie',
    'search_placeholder'   => 'Cerca per nome, cognome, email o numero...',
    'search'               => 'Cerca',
    'filter'               => 'Filtra',
    'reset_filters'        => 'Azzera filtri',

    // Pagination
    'showing'              => 'Visualizzati :from–:to di :total soci',
    'no_members'           => 'Nessun socio trovato.',

    // Actions
    'view'                 => 'Vedi',
    'edit'                 => 'Modifica',
    'save'                 => 'Salva',
    'cancel'               => 'Annulla',
    'back_to_list'         => 'Torna all\'elenco',
    'back_to_profile'      => 'Torna al profilo',

    // Messages
    'created_ok'           => 'Socio creato con successo (N. :number).',
    'updated_ok'           => 'Dati aggiornati con successo.',
    'deleted_ok'           => 'Socio cancellato (N. :number).',
    'not_found'            => 'Socio non trovato.',
    'forbidden'            => 'Accesso non autorizzato.',

    // Validation
    'name_required'        => 'Il nome è obbligatorio.',
    'surname_required'     => 'Il cognome è obbligatorio.',
    'email_required'       => 'L\'email è obbligatoria.',
    'email_invalid'        => 'L\'indirizzo email non è valido.',
    'email_duplicate'      => 'Questa email è già registrata.',
    'fiscal_code_duplicate'=> 'Questo codice fiscale è già registrato.',
    'joined_on_required'   => 'La data di iscrizione è obbligatoria.',

    // Memberships history
    'memberships_history'  => 'Storico tessere',
    'membership_year'      => 'Anno',
    'membership_fee'       => 'Quota',
    'membership_status'    => 'Stato tessera',
    'membership_valid'     => 'Validità',
    'no_memberships'       => 'Nessuna tessera registrata.',

    // Payments
    'payments_linked'      => 'Pagamenti collegati',
    'no_payments'          => 'Nessun pagamento registrato.',
    'payment_amount'       => 'Importo',
    'payment_date'         => 'Data',
    'payment_gateway'      => 'Metodo',
    'payment_status'       => 'Stato',

    // Sex / Gender
    'sex'                  => 'Sesso',
    'sex_m'                => 'M — Maschio',
    'sex_f'                => 'F — Femmina',
    'gender'               => 'Genere',
    'gender_man'           => 'Uomo',
    'gender_woman'         => 'Donna',
    'gender_nonbinary'     => 'Non binario',
    'gender_fluid'         => 'Fluido',
    'gender_not_specified' => 'Preferisco non specificare',
    'gender_other'         => 'Altro — specifica',
    'gender_gdpr_note'     => 'Il campo genere è facoltativo e trattato come dato sensibile (GDPR).',

    // Fiscal code helper
    'cf_calculate'         => 'Calcola',
    'cf_calculate_note'    => 'Il calcolo automatico del codice fiscale è in sviluppo.',

    // Box headings
    'box_registry'         => 'Anagrafica',
    'box_member'           => 'Socio',
    'box_contacts'         => 'Contatti',

    // Status values (new set)
    'status_attivo'        => 'Attivo',
    'status_in_rinnovo'    => 'In rinnovo',
    'status_non_rinnovato' => 'Non rinnovato',
    'status_decaduto'      => 'Decaduto',
    'status_onorario'      => 'Onorario',
    'status_sospeso'       => 'Sospeso',

    // Contact fields
    'mobile'               => 'Cellulare',

    // Emergency delete
    'emergency_delete'          => 'Cancellazione di emergenza',
    'emergency_delete_warning'  => 'Operazione irreversibile',
    'emergency_delete_desc'     => 'Questa operazione cancella permanentemente il socio e tutte le sue tessere. I pagamenti collegati vengono mantenuti. Questa azione non può essere annullata.',
    'delete_confirm_heading'    => 'Conferma cancellazione socio',
    'memberships_to_delete'     => 'Tessere che verranno cancellate',
    'payments_kept'             => 'Pagamenti che verranno MANTENUTI',
    'free_number_label'         => 'Gestione numero tessera',
    'free_number_yes'           => 'Libera il numero (:number) — potrà essere riassegnato',
    'free_number_no'            => 'Mantieni il numero (:number) come riservato — non verrà riassegnato',
    'delete_type_confirm'       => 'Per confermare, digita DELETE nel campo sottostante',
    'delete_confirm_placeholder'=> 'Digita DELETE',
    'delete_execute'            => 'Procedi con la cancellazione',
    'delete_wrong_confirm'      => 'La parola di conferma non è corretta. Digita DELETE (maiuscolo).',
    'delete_forbidden'          => 'Solo il super amministratore può eseguire questa operazione.',
    'delete_csrf_invalid'       => 'Token di sicurezza non valido. Ricarica la pagina.',
];
