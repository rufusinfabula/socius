<?php
/*
 * Socius - Open Source Association Management System
 * Copyright (C) 2026 Fabio Ranfi
 */

return [
    'communications'          => 'Communications',
    'communication'           => 'Communication',
    'new_communication'       => 'New communication',
    'edit_communication'      => 'Edit communication',
    'communication_detail'    => 'Communication detail',

    // Fields
    'title_internal'          => 'Title (internal)',
    'subject'                 => 'Subject',
    'body'                    => 'Body',
    'format_text'             => 'Plain text',
    'format_markdown'         => 'Markdown',

    // Types
    'type'                    => 'Type',
    'type_general'            => 'General circular',
    'type_renewal'            => 'Renewal',
    'type_board'              => 'Board',
    'type_direct'             => 'Direct',

    // Statuses
    'status'                  => 'Status',
    'status_draft'            => 'Draft',
    'status_ready'            => 'Ready',
    'status_sent'             => 'Sent',

    // Renewal periods
    'renewal_period'          => 'Renewal period',
    'period_open'             => 'Renewal open',
    'period_first_reminder'   => 'First reminder',
    'period_second_reminder'  => 'Second reminder',
    'period_third_reminder'   => 'Third reminder',
    'period_close'            => 'Renewal close',
    'period_lapse'            => 'Lapse',
    'period_none'             => 'Outside renewal cycle',
    'period_active_desc'      => 'Active period — check suggested communications',
    'force_period_check'      => 'Force period check',

    // Recipients
    'recipients'              => 'Recipients',
    'recipients_count'        => ':count recipients',
    'recipients_with_email'   => ':count with email',
    'recipients_no_email'     => ':count members without email',
    'add_by_status'           => 'By member status',
    'add_by_category'         => 'By category',
    'add_board'               => 'Board members only',
    'add_manual'              => 'Manual selection',
    'add_recipients'          => 'Add these members',
    'add_board_btn'           => 'Add board members',
    'remove_recipient'        => 'Remove',
    'included'                => 'Included',
    'excluded'                => 'Excluded',
    'no_recipients'           => 'No recipients selected.',
    'search_member_placeholder' => 'Search member by name, surname...',

    // Placeholders
    'placeholders_available'  => 'Available placeholders',

    // Export
    'export_csv'              => 'Export CSV',
    'export_txt'              => 'Export TXT (email only)',
    'export_txt_names'        => 'Export TXT (with names)',

    // Actions
    'mark_ready'              => 'Mark as ready',
    'mark_sent'               => 'Mark as sent',
    'duplicate'               => 'Duplicate',
    'use_template'            => 'Use default template',
    'action_save'             => 'Save',
    'action_cancel'           => 'Cancel',
    'action_edit'             => 'Edit',

    // Messages
    'no_communications'       => 'No communications.',
    'created_ok'              => 'Communication created.',
    'updated_ok'              => 'Communication updated.',
    'sent_ok'                 => 'Communication marked as sent.',
    'ready_ok'                => 'Communication marked as ready.',
    'delete_ok'               => 'Communication deleted.',
    'duplicate_ok'            => 'Communication duplicated.',
    'only_draft_editable'     => 'Only drafts can be edited.',
    'only_draft_deletable'    => 'Only drafts can be deleted.',
    'not_found'               => 'Communication not found.',
    'error_save_generic'      => 'Error saving. Please try again.',
    'error_title_required'    => 'Title is required.',
    'error_subject_required'  => 'Subject is required.',
    'error_body_required'     => 'Body is required.',
    'confirm_mark_sent'       => 'Confirm marking this communication as sent?',
    'confirm_delete'          => 'Delete this communication?',

    // Recipients added/removed feedback
    'recipients_added'        => ':count recipients added.',
    'recipients_none_added'   => 'No new recipients to add.',

    // Columns
    'col_title'               => 'Title',
    'col_type'                => 'Type',
    'col_period'              => 'Period',
    'col_recipients'          => 'Recipients',
    'col_status'              => 'Status',
    'col_date'                => 'Date',
    'col_actions'             => 'Actions',

    // Sent info
    'sent_at'                 => 'Sent on',
    'created_by'              => 'Created by',
    'created_at'              => 'Created on',
];
