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
    // Section headings
    // -------------------------------------------------------------------------
    'members'              => 'Members',
    'member_list'          => 'Member list',
    'new_member'           => 'New member',
    'edit_member'          => 'Edit member',
    'member_profile'       => 'Member profile',
    'add_member'           => 'Add member',

    // -------------------------------------------------------------------------
    // Table columns
    // -------------------------------------------------------------------------
    'membership_number'    => 'Card No.',
    'name'                 => 'First name',
    'surname'              => 'Last name',
    'full_name'            => 'Full name',
    'email'                => 'Email',
    'status'               => 'Status',
    'category'             => 'Category',
    'joined_on'            => 'Member since',
    'actions'              => 'Actions',

    // -------------------------------------------------------------------------
    // Form fields — Registry
    // -------------------------------------------------------------------------
    'birth_date'           => 'Date of birth',
    'birth_place'          => 'Place of birth',
    'fiscal_code'          => 'Fiscal / Tax code',

    // -------------------------------------------------------------------------
    // Form fields — Sex & Gender
    // -------------------------------------------------------------------------
    'sex'                  => 'Sex',
    'sex_m'                => 'M — Male',
    'sex_f'                => 'F — Female',
    'gender'               => 'Gender',
    'gender_man'           => 'Man',
    'gender_woman'         => 'Woman',
    'gender_nonbinary'     => 'Non-binary',
    'gender_fluid'         => 'Gender fluid',
    'gender_not_specified' => 'Prefer not to say',
    'gender_other'         => 'Other — specify',
    'gender_gdpr_note'     => 'Gender is optional and treated as sensitive data (GDPR).',

    // -------------------------------------------------------------------------
    // Form fields — Membership
    // -------------------------------------------------------------------------
    'status_active'        => 'Active',
    'status_in_renewal'    => 'In renewal',
    'status_not_renewed'   => 'Not renewed',
    'status_lapsed'        => 'Lapsed',
    'status_suspended'     => 'Suspended',
    'status_resigned'      => 'Resigned',
    'status_deceased'      => 'Deceased',
    'resigned_on'          => 'Resignation date',
    'notes'                => 'Notes (admin only)',

    // -------------------------------------------------------------------------
    // Form fields — Contacts
    // -------------------------------------------------------------------------
    'phone1'               => 'Phone 1',
    'phone2'               => 'Phone 2',
    'address'              => 'Address',
    'postal_code'          => 'Postal code',
    'city'                 => 'City',
    'province'             => 'Province / State',
    'country'              => 'Country',

    // -------------------------------------------------------------------------
    // Form fields — Fiscal code
    // -------------------------------------------------------------------------
    'cf_calculate'         => 'Calculate',
    'cf_calculate_note'    => 'Automatic tax code calculation is under development.',

    // -------------------------------------------------------------------------
    // Box headings
    // -------------------------------------------------------------------------
    'box_registry'         => 'Registry',
    'box_member'           => 'Member',
    'box_contacts'         => 'Contacts',

    // -------------------------------------------------------------------------
    // Filters and search
    // -------------------------------------------------------------------------
    'filter_all_statuses'  => 'All statuses',
    'filter_all_categories'=> 'All categories',
    'search_placeholder'   => 'Search by name, surname, email or number...',
    'search'               => 'Search',
    'filter'               => 'Filter',
    'reset_filters'        => 'Reset filters',

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------
    'showing'              => 'Showing :from–:to of :total members',
    'no_members'           => 'No members found.',

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------
    'view'                 => 'View',
    'edit'                 => 'Edit',
    'save'                 => 'Save',
    'cancel'               => 'Cancel',
    'back_to_list'         => 'Back to list',
    'back_to_profile'      => 'Back to profile',

    // -------------------------------------------------------------------------
    // Confirmation messages
    // -------------------------------------------------------------------------
    'created_ok'           => 'Member created successfully (No. :number).',
    'updated_ok'           => 'Member data updated successfully.',
    'deleted_ok'           => 'Member deleted (No. :number).',
    'not_found'            => 'Member not found.',
    'forbidden'            => 'Access denied.',

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------
    'name_required'        => 'First name is required.',
    'surname_required'     => 'Last name is required.',
    'email_required'       => 'Email is required.',
    'email_invalid'        => 'The email address is not valid.',
    'email_duplicate'      => 'This email address is already registered.',
    'fiscal_code_duplicate'=> 'This fiscal code is already registered.',
    'joined_on_required'   => 'Membership start date is required.',

    // -------------------------------------------------------------------------
    // Memberships history
    // -------------------------------------------------------------------------
    'memberships_history'  => 'Membership history',
    'membership_year'      => 'Year',
    'membership_fee'       => 'Fee',
    'membership_status'    => 'Status',
    'membership_valid'     => 'Validity',
    'no_memberships'       => 'No memberships on record.',

    // -------------------------------------------------------------------------
    // Payments
    // -------------------------------------------------------------------------
    'payments_linked'      => 'Linked payments',
    'no_payments'          => 'No payments on record.',
    'payment_amount'       => 'Amount',
    'payment_date'         => 'Date',
    'payment_gateway'      => 'Method',
    'payment_status'       => 'Status',

    // -------------------------------------------------------------------------
    // Membership categories
    // -------------------------------------------------------------------------
    'categories'                 => 'Membership categories',
    'category_name'              => 'Category name',
    'category_fee'               => 'Annual fee',
    'category_free'              => 'Free',
    'category_exempt'            => 'Exempt from renewal',
    'category_requires_approval' => 'Requires approval',
    'category_valid_from'        => 'Valid from',
    'category_valid_until'       => 'Valid until',
    'category_description'       => 'Description',
    'category_active'            => 'Active',
    'fee_history'                => 'Fee history',
    'fee_year'                   => 'Year',
    'fee_amount'                 => 'Fee',
    'fee_note'                   => 'Note',
    'fee_approved_by'            => 'Approved by',
    'fee_confirm_previous'       => 'Confirm previous year fee',
    'fee_set_new'                => 'Set new fee',

    // -------------------------------------------------------------------------
    // Emergency delete
    // -------------------------------------------------------------------------
    'emergency_delete'           => 'Emergency deletion',
    'emergency_delete_warning'   => 'Irreversible operation',
    'emergency_delete_desc'      => 'This operation permanently deletes the member and all their memberships. Linked payments are kept. This action cannot be undone.',
    'delete_confirm_heading'     => 'Confirm member deletion',
    'memberships_to_delete'      => 'Memberships that will be deleted',
    'payments_kept'              => 'Payments that will be KEPT',
    'free_number_label'          => 'Membership number handling',
    'free_number_yes'            => 'Release number (:number) — it can be reassigned',
    'free_number_no'             => 'Reserve number (:number) — it will not be reassigned',
    'delete_type_confirm'        => 'To confirm, type DELETE in the field below',
    'delete_confirm_placeholder' => 'Type DELETE',
    'delete_execute'             => 'Proceed with deletion',
    'delete_wrong_confirm'       => 'The confirmation word is incorrect. Type DELETE (uppercase).',
    'delete_forbidden'           => 'Only the super administrator can perform this operation.',
    'delete_csrf_invalid'        => 'Invalid security token. Reload the page.',

];
