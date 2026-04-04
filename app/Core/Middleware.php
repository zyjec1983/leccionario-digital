<?php
/**
 * Location: leccionario-digital/app/Core/Middleware.php
 */

/**
 * Middleware functions for authentication and session management
 */

// ********** Auth Middleware **********
function requireAuth(string $role = null): void
{
    if (!Session::isLoggedIn()) {
        redirect('auth/login');
    }
    
    if (Session::isTimedOut()) {
        Session::flash('timeout', true);
        Session::destroy();
        redirect('auth/login');
    }
    
    Session::touch();
    
    if ($role !== null && !auth()->hasRole($role)) {
        redirect('auth/unauthorized');
    }
}

// ********** Login Middleware **********
function requireLogin(): void
{
    if (Session::isLoggedIn()) {
        if (Session::isTimedOut()) {
            Session::flash('timeout', true);
            Session::destroy();
            redirect('auth/login');
        }
        Session::touch();
        redirect('/');
    }
}
