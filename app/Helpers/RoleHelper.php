<?php

namespace App\Helpers;

class RoleHelper
{
    public static function isReviewer(): bool
    {
        return auth()->user()->role === 'reviewer';
    }

    public static function isAdmin(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function isAuthor(): bool
    {
        return auth()->user()->role === 'author';
    }
}