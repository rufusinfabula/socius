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
    // Intestazioni sezioni
    // -------------------------------------------------------------------------
    'memberships'          => 'Tessere',
    'membership'           => 'Tessera',
    'new_membership'       => 'Nuova tessera',
    'edit_membership'      => 'Modifica tessera',
    'membership_detail'    => 'Dettaglio tessera',

    // -------------------------------------------------------------------------
    // Campi
    // -------------------------------------------------------------------------
    'social_year'          => 'Anno sociale',
    'membership_number'    => 'N. Tessera',
    'next_available'            => 'prossimo disponibile',
    'membership_number_hint'    => 'Lascia invariato per usare il numero proposto.',
    'search_member_placeholder' => 'Cerca per nome, cognome o numero socio (es. M00001)…',
    'fee'                  => 'Quota',
    'paid_on'              => 'Pagato il',
    'payment_method'       => 'Metodo pagamento',
    'payment_reference'    => 'Riferimento pagamento',
    'no_payment'           => 'Nessun pagamento — solo tessera',

    // -------------------------------------------------------------------------
    // Metodi di pagamento
    // -------------------------------------------------------------------------
    'method_cash'          => 'Contanti',
    'method_bank'          => 'Bonifico',
    'method_paypal'        => 'PayPal',
    'method_satispay'      => 'Satispay',
    'method_waived'        => 'Quota condonata',
    'method_other'         => 'Altro',

    // -------------------------------------------------------------------------
    // Status tessera
    // -------------------------------------------------------------------------
    'status_pending'       => 'In attesa',
    'status_paid'          => 'Pagata',
    'status_waived'        => 'Condonata',
    'status_cancelled'     => 'Annullata',

    // -------------------------------------------------------------------------
    // Status socio
    // -------------------------------------------------------------------------
    'member_status_active'      => 'Attivo',
    'member_status_in_renewal'  => 'In rinnovo',
    'member_status_not_renewed' => 'Non rinnovato',
    'member_status_lapsed'      => 'Decaduto',
    'member_status_suspended'   => 'Sospeso',
    'member_status_resigned'    => 'Dimesso',
    'member_status_deceased'    => 'Deceduto',

    // -------------------------------------------------------------------------
    // Messaggi
    // -------------------------------------------------------------------------
    'no_memberships'       => 'Nessuna tessera registrata.',
    'created_ok'           => 'Tessera creata con successo.',
    'updated_ok'           => 'Tessera aggiornata.',
    'not_found'            => 'Tessera non trovata.',

    // -------------------------------------------------------------------------
    // Storico tessere nel profilo socio
    // -------------------------------------------------------------------------
    'member_history'       => 'Storico tessere',
    'new_for_member'       => 'Nuova tessera per questo socio',

    // -------------------------------------------------------------------------
    // Form — box intestazioni
    // -------------------------------------------------------------------------
    'box_membership'       => 'Tessera',
    'box_payment'          => 'Pagamento',
    'box_member'           => 'Socio',
    'select_member'        => '— Seleziona socio —',
    'select_year'          => '— Seleziona anno —',
    'select_category'      => '— Seleziona categoria —',
    'select_method'        => '— Seleziona metodo —',

    // -------------------------------------------------------------------------
    // Filtri lista
    // -------------------------------------------------------------------------
    'filter_all_years'     => 'Tutti gli anni',
    'filter_all_statuses'  => 'Tutti gli stati',
    'filter_all_categories'=> 'Tutte le categorie',
    'filter'               => 'Filtra',
    'reset_filters'        => 'Azzera filtri',

    // -------------------------------------------------------------------------
    // Colonne tabella
    // -------------------------------------------------------------------------
    'col_member_number'    => 'N. Socio',
    'col_full_name'        => 'Cognome Nome',
    'col_category'         => 'Categoria',
    'col_year'             => 'Anno',
    'col_tessera'          => 'N. Tessera',
    'col_fee'              => 'Quota',
    'col_status'           => 'Stato',
    'col_paid_on'          => 'Pagato il',
    'col_actions'          => 'Azioni',

    // -------------------------------------------------------------------------
    // Azioni
    // -------------------------------------------------------------------------
    'action_detail'        => 'Dettaglio',
    'action_edit'          => 'Modifica',
    'action_back_list'     => 'Torna alla lista',
    'action_back_member'   => 'Torna al profilo',
    'action_save'          => 'Salva',
    'action_cancel'        => 'Annulla',

    // -------------------------------------------------------------------------
    // Paginazione
    // -------------------------------------------------------------------------
    'showing'              => 'Visualizzate :from–:to di :total tessere',

    // -------------------------------------------------------------------------
    // Validazione
    // -------------------------------------------------------------------------
    'error_member_required'    => 'Seleziona un socio.',
    'error_year_required'      => 'Anno non valido.',
    'error_category_required'  => 'Seleziona una categoria.',
    'error_fee_invalid'        => 'Quota non valida.',
    'error_paid_on_required'   => 'Inserire la data di pagamento.',
    'error_duplicate'          => 'Esiste già una tessera per questo socio per l\'anno selezionato.',
    'error_save_generic'       => 'Errore durante il salvataggio. Riprova.',
    'error_number_reserved'    => 'Questo numero tessera è riservato.',
    'error_number_taken'       => 'Questo numero tessera è già assegnato.',

    // -------------------------------------------------------------------------
    // Zona pericolosa
    // -------------------------------------------------------------------------
    'dangerous_zone'                   => 'Zona pericolosa',
    'dangerous_zone_desc'              => 'Le operazioni in questa sezione sono irreversibili e registrate nel log di audit.',
    'dangerous_reserve_number'         => 'Ritira il numero tessera',
    'dangerous_reserve_desc'           => 'Il numero tessera non verrà mai liberato né riassegnato, anche se il socio decade.',
    'dangerous_reserve_confirm_label'  => 'Digita il numero tessera per confermare',
    'dangerous_change_number'          => 'Cambia numero tessera',
    'dangerous_change_number_desc'     => 'Sostituisce il numero tessera corrente con uno nuovo. Il vecchio numero viene liberato.',
    'dangerous_new_number_label'       => 'Nuovo numero tessera',
    'dangerous_change_status'          => 'Forza cambio status tessera',
    'dangerous_change_status_desc'     => 'Cambia lo status della tessera bypassando il flusso normale.',
    'dangerous_change_fee'             => 'Correggi quota pagata',
    'dangerous_change_fee_desc'        => "Modifica l'importo registrato. Usare solo per correggere errori.",
    'dangerous_force_member_status'    => 'Forza status socio',
    'dangerous_force_member_status_desc' => 'Cambia lo status del socio bypassando il ciclo automatico.',
    'dangerous_motivation'             => 'Motivazione (obbligatoria)',
    'dangerous_motivation_placeholder' => 'Descrivi il motivo di questa operazione...',
    'dangerous_motivation_min'         => 'La motivazione deve essere di almeno 10 caratteri.',
    'dangerous_confirm_mismatch'       => 'Il numero inserito non corrisponde al numero tessera.',
    'dangerous_ok'                     => 'Operazione eseguita e registrata nel log di audit.',
    'dangerous_forbidden'              => 'Solo il super amministratore può eseguire questa operazione.',
    'dangerous_execute'                => 'Esegui operazione',

    // Anni disponibili
    'year_current'                     => 'Anno corrente',
    'year_future'                      => 'Anno futuro',
    'year_past'                        => 'Anno passato',
    'duplicate_year'                   => 'Esiste già una tessera per questo socio per l\'anno :year.',

];
