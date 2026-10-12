<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\CourseCatalogResource;
use App\Mcp\Tools\CreateAttendanceTool;
use App\Mcp\Tools\DeleteAttendanceTool;
use App\Mcp\Tools\InstructorEarningsTool;
use App\Mcp\Tools\ListAttendancesTool;
use App\Mcp\Tools\ListCourseClassesTool;
use App\Mcp\Tools\ListCoursesTool;
use App\Mcp\Tools\VerifyCertificateTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('CertiDigital Admin')]
#[Version('1.0.0')]
#[Instructions('Admin CertiDigital server. Requires Sanctum token with admin role over HTTP, or trusted local STDIO. Can list/create/delete attendances and compute instructor earnings for a month (same logic as financial reports).')]
class CertiDigitalAdminServer extends Server
{
    protected array $tools = [
        ListCoursesTool::class,
        ListCourseClassesTool::class,
        VerifyCertificateTool::class,
        ListAttendancesTool::class,
        CreateAttendanceTool::class,
        DeleteAttendanceTool::class,
        InstructorEarningsTool::class,
    ];

    protected array $resources = [
        CourseCatalogResource::class,
    ];

    protected array $prompts = [
        //
    ];
}
