<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\CourseCatalogResource;
use App\Mcp\Tools\ListCourseClassesTool;
use App\Mcp\Tools\ListCoursesTool;
use App\Mcp\Tools\VerifyCertificateTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('CertiDigital Public')]
#[Version('1.0.0')]
#[Instructions('Public CertiDigital server. Use for course catalog, class listing and certificate verification. No authentication required. All tools are read-only.')]
class CertiDigitalPublicServer extends Server
{
    protected array $tools = [
        ListCoursesTool::class,
        ListCourseClassesTool::class,
        VerifyCertificateTool::class,
    ];

    protected array $resources = [
        CourseCatalogResource::class,
    ];

    protected array $prompts = [
        //
    ];
}
