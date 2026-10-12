<?php

use App\Mcp\Servers\CertiDigitalAdminServer;
use App\Mcp\Servers\CertiDigitalPublicServer;
use Laravel\Mcp\Facades\Mcp;

// Public: Streamable HTTP, no auth (read-only tools + catalog resource).
Mcp::web('/mcp/public', CertiDigitalPublicServer::class);

// Admin: Streamable HTTP, Sanctum + Spatie admin role (mixed read/write + earnings).
// NOTE: uses role:admin instead of admin.only because admin.only requires
// user_id on POST bodies, which breaks MCP JSON-RPC POST calls.
Mcp::web('/mcp/admin', CertiDigitalAdminServer::class)
    ->middleware(['auth:sanctum', 'role:admin']);

// Local STDIO transports (Claude Desktop, Inspector, IDEs).
// Run via: php artisan mcp:start public  /  php artisan mcp:start admin
Mcp::local('public', CertiDigitalPublicServer::class);
Mcp::local('admin', CertiDigitalAdminServer::class);
