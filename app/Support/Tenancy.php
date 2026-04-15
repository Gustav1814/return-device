<?php

namespace App\Support;

class Tenancy
{
    public static function serverName(): ?string
    {
        return $_SERVER['SERVER_NAME'] ?? null;
    }

    public static function currDomainSuffix(): ?string
    {
        $suffix = env('CURR_DOMAIN');
        if (!is_string($suffix)) return null;
        $suffix = trim($suffix);
        return $suffix === '' ? null : $suffix;
    }

    public static function allowDomain(): ?string
    {
        $allow = env('ALLOW_DOMAIN');
        if (!is_string($allow)) return null;
        $allow = trim($allow);
        return $allow === '' ? null : $allow;
    }

    public static function isAllowedHost(?string $serverName): bool
    {
        if (!$serverName) return false;
        $allow = self::allowDomain();
        if (!$allow) return false;

        return strcasecmp($serverName, $allow) === 0;
    }

    public static function subdomainFromServerName(?string $serverName): ?string
    {
        if (!$serverName) return null;

        $suffix = self::currDomainSuffix();
        if (!$suffix) return null;

        $pos = stripos($serverName, $suffix);
        if ($pos === false) return null;

        $sub = substr($serverName, 0, $pos);
        $sub = rtrim($sub, '.');

        return $sub === '' ? null : $sub;
    }
}

