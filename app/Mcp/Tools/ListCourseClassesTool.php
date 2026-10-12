<?php

namespace App\Mcp\Tools;

use App\Models\CourseClass;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lists course classes (turmas), optionally filtered by course_id. Public read-only.')]
#[IsReadOnly]
#[IsIdempotent]
class ListCourseClassesTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'course_id' => 'nullable|integer|exists:courses,id',
        ]);

        $query = CourseClass::with(['course:id,title', 'instructor:id,full_name'])
            ->orderBy('name');

        if (! empty($validated['course_id'])) {
            $query->where('course_id', $validated['course_id']);
        }

        $classes = $query->get(['id', 'course_id', 'instructor_id', 'name'])->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'course_id' => $c->course_id,
            'course_title' => $c->course?->title,
            'instructor' => $c->instructor?->full_name,
        ]);

        return Response::structured([
            'count' => $classes->count(),
            'classes' => $classes->toArray(),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->description('Optional course ID to filter classes.'),
        ];
    }
}
