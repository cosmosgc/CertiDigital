<?php

namespace App\Mcp\Resources;

use App\Models\Course;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('Full course catalog with classes. Public read-only.')]
#[Uri('certi://catalog/courses')]
#[MimeType('application/json')]
class CourseCatalogResource extends Resource
{
    public function handle(Request $request): Response
    {
        $courses = Course::with('classes:id,course_id,name')->orderBy('title')->get();

        $catalog = $courses->map(fn ($c) => [
            'id' => $c->id,
            'title' => $c->title,
            'workload_hours' => $c->workload_hours,
            'modality' => $c->modality,
            'classes' => $c->classes->map(fn ($cl) => ['id' => $cl->id, 'name' => $cl->name])->toArray(),
        ]);

        return Response::text($catalog->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
