<?php

namespace App\Mcp\Tools;

use App\Models\Course;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lists all courses with id, title, workload and modality. Public read-only.')]
#[IsReadOnly]
#[IsIdempotent]
class ListCoursesTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $courses = Course::orderBy('title')->get(['id', 'title', 'workload_hours', 'modality']);

        return Response::structured([
            'count' => $courses->count(),
            'courses' => $courses->toArray(),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
