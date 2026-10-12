<?php

namespace App\Mcp\Tools;

use App\Models\CourseClassAttendance;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Creates a class attendance (aula) for a course class. Requires admin.')]
#[IsDestructive(false)]
class CreateAttendanceTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return true;
        }

        return method_exists($user, 'hasRole') ? $user->hasRole('admin') : true;
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(
            [
                'course_class_id' => 'required|integer|exists:course_classes,id',
                'attendance_date' => 'required|date_format:Y-m-d',
                'duration_hours' => 'nullable|numeric|min:0.5|max:24',
                'name' => 'nullable|string|max:150',
            ],
            [
                'course_class_id.required' => 'Provide course_class_id.',
                'attendance_date.date_format' => 'Date must be YYYY-MM-DD.',
            ]
        );

        $attendance = CourseClassAttendance::create([
            'course_class_id' => $validated['course_class_id'],
            'attendance_date' => $validated['attendance_date'],
            'duration_hours' => $validated['duration_hours'] ?? 2,
            'name' => $validated['name'] ?? null,
        ]);

        return Response::structured([
            'id' => $attendance->id,
            'course_class_id' => $attendance->course_class_id,
            'date' => $attendance->attendance_date->toDateString(),
            'duration_hours' => (float) $attendance->duration_hours,
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_class_id' => $schema->integer()->description('Course class ID.')->required(),
            'attendance_date' => $schema->string()->description('Date YYYY-MM-DD.')->required(),
            'duration_hours' => $schema->number()->description('Duration in hours, default 2.'),
            'name' => $schema->string()->description('Optional attendance name.'),
        ];
    }
}
