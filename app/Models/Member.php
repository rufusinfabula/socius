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

namespace Socius\Models;

/**
 * Association member (socio).
 *
 * Fields (planned): id, first_name, last_name, fiscal_code, email, phone,
 *                   address, membership_number, status, joined_at, notes.
 *
 * @todo Implement membership status transitions, fee history relationship.
 */
class Member extends BaseModel
{
    // placeholder
}
