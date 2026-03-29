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
 * System user (staff / admin account).
 * Distinct from Member, which represents an association member.
 *
 * Fields (planned): id, email, password_hash, role, active, created_at, updated_at.
 *
 * @todo Implement authentication helpers, role checks, password reset.
 */
class User extends BaseModel
{
    // placeholder
}
