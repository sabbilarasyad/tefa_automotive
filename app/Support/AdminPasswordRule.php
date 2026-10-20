<?php

namespace App\Support;

use InvalidArgumentException;

class AdminPasswordRule
{
    public static function check(?string $password): void
    {
        if ($password === null || $password === '') {
            throw new InvalidArgumentException('ADMIN_PASSWORD tidak boleh kosong.');
        }

        // Check for default password first (case-insensitive)
        if (strcasecmp($password, 'changeme123') === 0) {
            throw new InvalidArgumentException('ADMIN_PASSWORD tidak boleh menggunakan nilai default "changeme123". Gunakan password yang kuat dan unik.');
        }

        if (strlen($password) < 12) {
            throw new InvalidArgumentException('ADMIN_PASSWORD minimal 12 karakter.');
        }
    }
}