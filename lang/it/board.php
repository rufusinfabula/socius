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
    'board'                  => 'Direttivo',
    'board_composition'      => 'Composizione del direttivo',
    'board_history'          => 'Storico direttivi',
    'board_roles_catalog'    => 'Catalogo ruoli',
    'board_memberships'      => 'Appartenenze al direttivo',

    // -------------------------------------------------------------------------
    // Ruoli
    // -------------------------------------------------------------------------
    'role_name'              => 'Nome ruolo (slug)',
    'role_label'             => 'Etichetta visibile',
    'role_description'       => 'Descrizione',
    'role_is_board_member'   => 'Membro del direttivo',
    'role_can_sign'          => 'Può firmare atti ufficiali',
    'role_is_active'         => 'Attivo',
    'role_sort_order'        => 'Ordinamento',
    'add_role'               => 'Aggiungi ruolo',
    'edit_role'              => 'Modifica ruolo',

    // -------------------------------------------------------------------------
    // Appartenenze
    // -------------------------------------------------------------------------
    'membership_member'      => 'Socio',
    'membership_role'        => 'Ruolo',
    'membership_elected_on'  => 'Data nomina',
    'membership_expires_on'  => 'Scadenza mandato',
    'membership_resigned_on' => 'Data dimissioni',
    'membership_assembly'    => 'Assemblea deliberante',
    'membership_notes'       => 'Note',
    'add_membership'         => 'Aggiungi al direttivo',
    'edit_membership'        => 'Modifica incarico',
    'end_membership'         => 'Termina incarico',

    // -------------------------------------------------------------------------
    // Stato incarico
    // -------------------------------------------------------------------------
    'status_current'         => 'In carica',
    'status_expired'         => 'Scaduto',
    'status_resigned'        => 'Dimesso',

    // -------------------------------------------------------------------------
    // Filtri
    // -------------------------------------------------------------------------
    'filter_current_only'    => 'Solo incarichi in corso',
    'filter_all_roles'       => 'Tutti i ruoli',
    'filter_board_members'   => 'Solo direttivo',
    'filter_technical'       => 'Solo ruoli tecnici',

    // -------------------------------------------------------------------------
    // Azioni
    // -------------------------------------------------------------------------
    'view_board'             => 'Vedi direttivo',
    'manage_roles'           => 'Gestisci ruoli',
    'manage_memberships'     => 'Gestisci incarichi',

    // -------------------------------------------------------------------------
    // Messaggi
    // -------------------------------------------------------------------------
    'membership_added'       => 'Incarico aggiunto con successo.',
    'membership_updated'     => 'Incarico aggiornato con successo.',
    'membership_ended'       => 'Incarico terminato.',
    'no_current_board'       => 'Nessun membro del direttivo registrato.',
    'role_created'           => 'Ruolo creato con successo.',
    'role_updated'           => 'Ruolo aggiornato con successo.',

];
