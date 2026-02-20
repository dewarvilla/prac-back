<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class AuditContext
{
    public static function current(): array
    {
        $user = Auth::user();

        $req = request();

        $route = null;
        try {
            $r = $req?->route();
            $route = $r?->getName() ?: $r?->uri();
        } catch (\Throwable $e) {
            $route = null;
        }

        return [
            'actor_id'    => $user?->id,
            'actor_email' => $user?->email,

            'ip'         => $req?->ip(),
            'user_agent' => $req?->userAgent(),

            'method' => $req?->method(),
            'url'    => $req?->fullUrl(),
            'route'  => $route,

            'request_id' => $req?->header('X-Request-Id') ?? null,
        ];
    }
}