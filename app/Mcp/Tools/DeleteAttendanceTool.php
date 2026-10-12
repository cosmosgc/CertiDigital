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

#[Description('Deletes a class attendance by ID. Requires admin. Cascades to presence records.')]
#[IsDestructive]
class DeleteAttendanceTool extends Tool
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
        $validated = $request->validate(['attendance_id' => 'required|integer']);

        $attendance = CourseClassAttendance::find($validated['attendance_id']);

        if (! $attendance) {
            return Response::error("Attendance [{$validated['attendance_id']}] not found.");
        }

        $info = [
            'id' => $attendance->id,
            'course_class_id' => $attendance->course_class_id,
            'date' => $attendance->attendance_date?->toDateString(),
        ];
        $attendance->delete();

        return Response::structured(['deleted' => $info]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'attendance_id' => $schema->integer()->description('Attendance ID to delete.')->required(),
        ];
    }
}
