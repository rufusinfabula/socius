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

return [
    // Login
    'login'                 => 'Log in',
    'login_heading'         => 'Sign in to the panel',
    'email'                 => 'Email address',
    'email_placeholder'     => 'name@example.com',
    'password'              => 'Password',
    'remember_me'           => 'Remember me',
    'forgot_password'       => 'Forgot your password?',
    'no_account'            => 'No account? Contact the administrator.',

    // Errors
    'invalid_credentials'   => 'Invalid credentials. Check your email and password.',
    'account_inactive'      => 'Your account is not active. Contact the administrator.',
    'too_many_attempts'     => 'Too many login attempts. Please try again in :minutes minutes.',
    'csrf_invalid'          => 'Session expired. Reload the page and try again.',

    // Logout
    'logout'                => 'Log out',
    'logged_out'            => 'You have been logged out successfully.',

    // Forgot password
    'forgot_heading'        => 'Recover your password',
    'forgot_intro'          => 'Enter your email address and we\'ll send you a link to reset your password.',
    'send_reset_link'       => 'Send recovery link',
    'reset_link_sent'       => 'If that email address is registered, you will receive a password reset link shortly.',
    'back_to_login'         => 'Back to login',

    // Reset password
    'reset_heading'         => 'New password',
    'reset_intro'           => 'Choose a new password for your account.',
    'new_password'          => 'New password',
    'confirm_password'      => 'Confirm password',
    'reset_password'        => 'Reset password',
    'passwords_mismatch'    => 'The two passwords do not match.',
    'password_too_short'    => 'Password must be at least 8 characters long.',
    'reset_token_invalid'   => 'The recovery link is invalid or has expired.',
    'reset_success'         => 'Password updated successfully. Sign in with your new credentials.',

    // Email subjects
    'email_reset_subject'   => 'Password recovery — :app_name',
    'email_reset_body'      => "You requested a password reset.\n\nClick the link below to set a new password (valid for 1 hour):\n\n:url\n\nIf you did not request a password reset, ignore this email.\n\n— :app_name",
];
