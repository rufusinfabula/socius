<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

return [
    'communications'          => 'Comunicazioni',
    'communication'           => 'Comunicazione',
    'new_communication'       => 'Nuova comunicazione',
    'edit_communication'      => 'Modifica comunicazione',
    'communication_detail'    => 'Dettaglio comunicazione',

    // Fields
    'title_internal'          => 'Titolo (interno)',
    'subject'                 => 'Oggetto',
    'body'                    => 'Testo',
    'format_text'             => 'Testo semplice',
    'format_markdown'         => 'Markdown',

    // Types
    'type'                    => 'Tipo',
    'type_general'            => 'Circolare generale',
    'type_renewal'            => 'Rinnovo',
    'type_board'              => 'Direttivo',
    'type_direct'             => 'Diretta',

    // Statuses
    'status'                  => 'Status',
    'status_draft'            => 'Bozza',
    'status_ready'            => 'Pronta',
    'status_sent'             => 'Inviata',

    // Renewal periods
    'renewal_period'          => 'Periodo rinnovo',
    'period_open'             => 'Apertura rinnovi',
    'period_first_reminder'   => 'Primo sollecito',
    'period_second_reminder'  => 'Secondo sollecito',
    'period_third_reminder'   => 'Terzo sollecito',
    'period_close'            => 'Chiusura rinnovi',
    'period_lapse'            => 'Decadenza',
    'period_none'             => 'Fuori dal ciclo di rinnovo',
    'period_active_desc'      => 'Periodo attivo — controlla le comunicazioni suggerite',
    'force_period_check'      => 'Forza controllo periodo',

    // Recipients
    'recipients'              => 'Destinatari',
    'recipients_count'        => ':count destinatari',
    'recipients_with_email'   => ':count con email',
    'recipients_no_email'     => ':count soci senza email',
    'add_by_status'           => 'Per status socio',
    'add_by_category'         => 'Per categoria',
    'add_board'               => 'Solo direttivo',
    'add_manual'              => 'Selezione manuale',
    'add_recipients'          => 'Aggiungi questi soci',
    'add_board_btn'           => 'Aggiungi direttivo',
    'remove_recipient'        => 'Rimuovi',
    'included'                => 'Incluso',
    'excluded'                => 'Escluso',
    'no_recipients'           => 'Nessun destinatario selezionato.',
    'search_member_placeholder' => 'Cerca socio per nome, cognome...',

    // Placeholders
    'placeholders_available'  => 'Placeholder disponibili',

    // Export
    'export_csv'              => 'Esporta CSV',
    'export_txt'              => 'Esporta TXT (solo email)',
    'export_txt_names'        => 'Esporta TXT (con nomi)',

    // Actions
    'mark_ready'              => 'Segna come pronta',
    'mark_sent'               => 'Segna come inviata',
    'duplicate'               => 'Duplica',
    'use_template'            => 'Usa template predefinito',
    'action_save'             => 'Salva',
    'action_cancel'           => 'Annulla',
    'action_edit'             => 'Modifica',

    // Messages
    'no_communications'       => 'Nessuna comunicazione.',
    'created_ok'              => 'Comunicazione creata.',
    'updated_ok'              => 'Comunicazione aggiornata.',
    'sent_ok'                 => 'Comunicazione segnata come inviata.',
    'ready_ok'                => 'Comunicazione segnata come pronta.',
    'delete_ok'               => 'Comunicazione eliminata.',
    'duplicate_ok'            => 'Comunicazione duplicata.',
    'only_draft_editable'     => 'Solo le bozze possono essere modificate.',
    'only_draft_deletable'    => 'Solo le bozze possono essere eliminate.',
    'not_found'               => 'Comunicazione non trovata.',
    'error_save_generic'      => 'Errore durante il salvataggio. Riprova.',
    'error_title_required'    => 'Il titolo è obbligatorio.',
    'error_subject_required'  => "L'oggetto è obbligatorio.",
    'error_body_required'     => 'Il testo è obbligatorio.',
    'confirm_mark_sent'       => 'Confermi di voler segnare questa comunicazione come inviata?',
    'confirm_delete'          => 'Eliminare questa comunicazione?',

    // Recipients added/removed feedback
    'recipients_added'        => ':count destinatari aggiunti.',
    'recipients_none_added'   => 'Nessun nuovo destinatario da aggiungere.',

    // Columns
    'col_title'               => 'Titolo',
    'col_type'                => 'Tipo',
    'col_period'              => 'Periodo',
    'col_recipients'          => 'Destinatari',
    'col_status'              => 'Status',
    'col_date'                => 'Data',
    'col_actions'             => 'Azioni',

    // Sent info
    'sent_at'                 => 'Inviata il',
    'created_by'              => 'Creata da',
    'created_at'              => 'Creata il',
];
