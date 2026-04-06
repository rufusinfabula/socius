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
    // Page title
    // -------------------------------------------------------------------------
    'settings'               => 'Settings',

    // -------------------------------------------------------------------------
    // Tabs
    // -------------------------------------------------------------------------
    'tab_association'        => 'Association',
    'tab_social_year'        => 'Membership year',
    'tab_categories'         => 'Membership categories',
    'tab_board_roles'        => 'Board roles',
    'tab_interface'          => 'Interface',
    'tab_email'              => 'Email',
    'tab_member_number'      => 'Member number',

    // -------------------------------------------------------------------------
    // Association
    // -------------------------------------------------------------------------
    'assoc_name'             => 'Association name',
    'assoc_fiscal_code'      => 'Fiscal / Tax code',
    'assoc_vat_number'       => 'VAT number',
    'assoc_address'          => 'Registered address',
    'assoc_city'             => 'City',
    'assoc_postal_code'      => 'Postal code',
    'assoc_province'         => 'Province / State',
    'assoc_country'          => 'Country',
    'assoc_email'            => 'Official email',
    'assoc_phone'            => 'Phone',
    'assoc_website'          => 'Website',
    'assoc_logo'             => 'Logo',
    'assoc_logo_upload'      => 'Upload logo',
    'assoc_logo_current'     => 'Current logo',
    'assoc_logo_hint'        => 'PNG, JPG or SVG — max 2 MB',
    'assoc_logo_remove'      => 'No logo uploaded.',

    // -------------------------------------------------------------------------
    // Membership year
    // -------------------------------------------------------------------------
    'renewal_open'           => 'Renewal opens',
    'renewal_first_reminder' => 'First notice',
    'renewal_second_reminder'=> 'Second reminder',
    'renewal_third_reminder' => 'Third reminder',
    'renewal_close'          => 'Renewal closes',
    'renewal_lapse'          => 'Auto-lapse date',
    'renewal_approval'       => 'Communication approval',
    'renewal_approval_desc'  => 'Renewal communications require admin approval before sending',
    'renewal_date_hint'      => 'Format MM-DD (month-day), e.g. 11-15',
    'renewal_calendar_title' => 'Membership year cycle',

    // -------------------------------------------------------------------------
    // Membership categories
    // -------------------------------------------------------------------------
    'cat_new'                => 'New category',
    'cat_edit'               => 'Edit category',
    'cat_slug'               => 'Slug (lowercase letters and _ only)',
    'cat_label'              => 'Display name',
    'cat_description'        => 'Description',
    'cat_annual_fee'         => 'Annual fee (€)',
    'cat_is_free'            => 'Free (no annual fee)',
    'cat_is_exempt'          => 'Exempt from renewal',
    'cat_requires_approval'  => 'Requires approval',
    'cat_valid_from'         => 'Valid from',
    'cat_valid_until'        => 'Valid until',
    'cat_sort_order'         => 'Sort order',
    'cat_is_active'          => 'Active',
    'cat_fee_history'        => 'Annual fees',
    'cat_fee_add'            => 'Add / update fee for year',
    'cat_fee_year'           => 'Year',
    'cat_fee_amount'         => 'Fee (€)',
    'cat_fee_note'           => 'Note',
    'cat_no_fees'            => 'No fees on record.',
    'cat_toggle_active'      => 'Toggle active',
    'cat_none'               => 'No categories. Create one.',

    // -------------------------------------------------------------------------
    // Board roles
    // -------------------------------------------------------------------------
    'role_new'               => 'New role',
    'role_edit'              => 'Edit role',
    'role_slug'              => 'Slug (lowercase letters and _ only)',
    'role_label'             => 'Display name',
    'role_description'       => 'Description',
    'role_is_board_member'   => 'Board member',
    'role_can_sign'          => 'Can sign official documents',
    'role_sort_order'        => 'Sort order',
    'role_is_active'         => 'Active',
    'role_toggle_active'     => 'Toggle active',
    'role_none'              => 'No roles. Create one.',

    // -------------------------------------------------------------------------
    // Interface
    // -------------------------------------------------------------------------
    'theme'                  => 'Theme',
    'theme_uikit'            => 'UIkit — default theme',
    'theme_bootstrap'        => 'Bootstrap',
    'theme_tailwind'         => 'Tailwind CSS',
    'theme_wip_warning'      => 'Warning: the selected theme is still under development (WIP) and may not work correctly.',
    'language'               => 'Interface language',
    'date_format'            => 'Date format',
    'date_format_preview'    => 'Preview: ',
    'timezone'               => 'Timezone',

    // -------------------------------------------------------------------------
    // Email SMTP
    // -------------------------------------------------------------------------
    'smtp_host'              => 'SMTP server',
    'smtp_port'              => 'Port',
    'smtp_encryption'        => 'Encryption',
    'smtp_encryption_tls'    => 'TLS (STARTTLS)',
    'smtp_encryption_ssl'    => 'SSL',
    'smtp_encryption_none'   => 'None',
    'smtp_username'          => 'Username',
    'smtp_password'          => 'Password',
    'smtp_password_hint'     => 'Leave empty to keep the current password.',
    'smtp_from'              => 'From address',
    'smtp_from_name'         => 'From name',
    'smtp_test'              => 'Send test email',
    'smtp_test_ok'           => 'Test email sent successfully.',
    'smtp_test_fail'         => 'Send failed: :error',

    // -------------------------------------------------------------------------
    // Member number
    // -------------------------------------------------------------------------
    'number_start'              => 'Installation starting number',
    'number_start_desc'         => 'This value is used only when there are no members in the system yet. After the first member, the system always uses MAX+1 automatically.',
    'number_start_hint'         => 'Used only for the very first member created.',
    'number_start_inactive'     => 'Not applicable — members already exist. The next number is calculated automatically.',
    'number_start_save'         => 'Save starting number',
    'number_start_invalid'      => 'The starting number must be at least 1.',
    'number_start_collision'    => 'A member with number :number already exists.',
    'number_start_below_max'    => 'Saved. New members will still receive :max + 1 (this value is only used when no members exist yet).',
    'number_stat_count'         => 'Registered members',
    'number_stat_last'          => 'Last assigned',
    'number_stat_next'          => 'Next',
    'number_current_max'        => 'Last assigned number',
    'number_reset'              => 'Reset counter',
    'number_reset_desc'         => 'Set the progressive number for the next assignment.',
    'number_reset_warn'         => 'The new value must be higher than the last assigned number.',
    'number_reset_confirm'      => 'Are you sure you want to reset the counter?',

    // -------------------------------------------------------------------------
    // Common
    // -------------------------------------------------------------------------
    'saved_ok'               => 'Settings saved.',
    'save'                   => 'Save settings',
    'edit'                   => 'Edit',
    'add'                    => 'Add',
    'cancel'                 => 'Cancel',
    'active'                 => 'Active',
    'inactive'               => 'Inactive',
    'yes'                    => 'Yes',
    'no'                     => 'No',

];
