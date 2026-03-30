<?php
// One-time opcache reset — DELETE THIS FILE IMMEDIATELY AFTER USE
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'Opcache reset OK. Delete this file now.';
} else {
    echo 'Opcache not enabled or not available.';
}
