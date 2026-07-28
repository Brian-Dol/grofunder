<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EagerLoadUserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Eager-load roles and permissions for authenticated user
        // This prevents N+1 queries and ensures role/permission data is available
        if (Auth::check()) {
            try {
                $user = Auth::user();
                
                // Eager load roles and their permissions
                $user->load('roles.permissions', 'permissions');
                
                // Optional: Reset permission cache to ensure fresh data
                // This is useful after role/permission changes
                if ($request->has('refresh_permissions')) {
                    \Spatie\Permission\PermissionRegistrar::forgetCachedPermissions();
                }
            } catch (\Exception $e) {
                // Log the error but don't block the request
                \Log::error('Failed to eager load user permissions', [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
                ]);
                
                // Continue anyway - the permission checks will handle null gracefully
            }
        }

        return $next($request);
    }
}
