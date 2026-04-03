<?php

function requireAuth(string $role = null): void
{
    if (!isLoggedIn()) {
        redirect('auth/login');
    }
    
    if ($role !== null && !auth()->hasRole($role)) {
        redirect('auth/unauthorized');
    }
}
