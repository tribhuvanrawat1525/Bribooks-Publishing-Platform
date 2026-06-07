<?php

namespace App\Helpers;

class BookHelper
{
    public static function canEdit(string $status): bool
    {
        return in_array(
            $status,
            [
                'draft',
                'rejected'
            ]
        );
    }
}