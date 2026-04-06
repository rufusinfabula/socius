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
    'memberships'          => 'Memberships',
    'membership'           => 'Membership',
    'new_membership'       => 'New membership',
    'edit_membership'      => 'Edit membership',
    'membership_detail'    => 'Membership detail',

    // -------------------------------------------------------------------------
    // Fields
    // -------------------------------------------------------------------------
    'social_year'          => 'Social year',
    'membership_number'    => 'Card No.',
    'fee'                  => 'Fee',
    'paid_on'              => 'Paid on',
    'payment_method'       => 'Payment method',
    'payment_reference'    => 'Payment reference',
    'no_payment'           => 'No payment — register card only',

    // -------------------------------------------------------------------------
    // Payment methods
    // -------------------------------------------------------------------------
    'method_cash'          => 'Cash',
    'method_bank'          => 'Bank transfer',
    'method_paypal'        => 'PayPal',
    'method_satispay'      => 'Satispay',
    'method_waived'        => 'Fee waived',
    'method_other'         => 'Other',

    // -------------------------------------------------------------------------
    // Membership status
    // -------------------------------------------------------------------------
    'status_pending'       => 'Pending',
    'status_paid'          => 'Paid',
    'status_waived'        => 'Waived',
    'status_cancelled'     => 'Cancelled',

    // -------------------------------------------------------------------------
    // Member status
    // -------------------------------------------------------------------------
    'member_status_active'      => 'Active',
    'member_status_in_renewal'  => 'In renewal',
    'member_status_not_renewed' => 'Not renewed',
    'member_status_lapsed'      => 'Lapsed',
    'member_status_suspended'   => 'Suspended',
    'member_status_resigned'    => 'Resigned',
    'member_status_deceased'    => 'Deceased',

    // -------------------------------------------------------------------------
    // Messages
    // -------------------------------------------------------------------------
    'no_memberships'       => 'No memberships on record.',
    'created_ok'           => 'Membership created successfully.',
    'updated_ok'           => 'Membership updated.',
    'not_found'            => 'Membership not found.',

    // -------------------------------------------------------------------------
    // Membership history in member profile
    // -------------------------------------------------------------------------
    'member_history'       => 'Membership history',
    'new_for_member'       => 'New membership for this member',

    // -------------------------------------------------------------------------
    // Form — box headings
    // -------------------------------------------------------------------------
    'box_membership'       => 'Membership',
    'box_payment'          => 'Payment',
    'box_member'           => 'Member',
    'select_member'        => '— Select member —',
    'select_year'          => '— Select year —',
    'select_category'      => '— Select category —',
    'select_method'        => '— Select method —',

    // -------------------------------------------------------------------------
    // List filters
    // -------------------------------------------------------------------------
    'filter_all_years'     => 'All years',
    'filter_all_statuses'  => 'All statuses',
    'filter_all_categories'=> 'All categories',
    'filter'               => 'Filter',
    'reset_filters'        => 'Reset filters',

    // -------------------------------------------------------------------------
    // Table columns
    // -------------------------------------------------------------------------
    'col_member_number'    => 'Member No.',
    'col_full_name'        => 'Last, First name',
    'col_category'         => 'Category',
    'col_year'             => 'Year',
    'col_tessera'          => 'Card No.',
    'col_fee'              => 'Fee',
    'col_status'           => 'Status',
    'col_paid_on'          => 'Paid on',
    'col_actions'          => 'Actions',

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------
    'action_detail'        => 'Detail',
    'action_edit'          => 'Edit',
    'action_back_list'     => 'Back to list',
    'action_back_member'   => 'Back to profile',
    'action_save'          => 'Save',
    'action_cancel'        => 'Cancel',

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------
    'showing'              => 'Showing :from–:to of :total memberships',

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------
    'error_member_required'    => 'Please select a member.',
    'error_year_required'      => 'Invalid year.',
    'error_category_required'  => 'Please select a category.',
    'error_fee_invalid'        => 'Invalid fee amount.',
    'error_paid_on_required'   => 'Please enter a payment date.',
    'error_duplicate'          => 'A membership already exists for this member for the selected year.',
    'error_save_generic'       => 'Error while saving. Please try again.',
    'error_number_reserved'    => 'This membership number is reserved.',
    'error_number_taken'       => 'This membership number is already assigned.',

    // -------------------------------------------------------------------------
    // Dangerous zone
    // -------------------------------------------------------------------------
    'dangerous_zone'                   => 'Danger zone',
    'dangerous_zone_desc'              => 'Operations in this section are irreversible and recorded in the audit log.',
    'dangerous_reserve_number'         => 'Reserve membership number',
    'dangerous_reserve_desc'           => 'The membership number will never be released or reassigned, even if the member lapses.',
    'dangerous_reserve_confirm_label'  => 'Type the membership number to confirm',
    'dangerous_change_number'          => 'Change membership number',
    'dangerous_change_number_desc'     => 'Replaces the current membership number with a new one. The old number is released.',
    'dangerous_new_number_label'       => 'New membership number',
    'dangerous_change_status'          => 'Force membership status change',
    'dangerous_change_status_desc'     => 'Changes the membership status bypassing normal workflow.',
    'dangerous_change_fee'             => 'Correct paid fee',
    'dangerous_change_fee_desc'        => 'Modifies the recorded amount. Use only to correct errors.',
    'dangerous_force_member_status'    => 'Force member status',
    'dangerous_force_member_status_desc' => 'Changes the member status bypassing the automatic renewal cycle.',
    'dangerous_motivation'             => 'Motivation (required)',
    'dangerous_motivation_placeholder' => 'Describe the reason for this operation...',
    'dangerous_motivation_min'         => 'Motivation must be at least 10 characters.',
    'dangerous_confirm_mismatch'       => 'The number entered does not match the membership number.',
    'dangerous_ok'                     => 'Operation executed and recorded in the audit log.',
    'dangerous_forbidden'              => 'Only the super administrator can perform this operation.',
    'dangerous_execute'                => 'Execute operation',

];
