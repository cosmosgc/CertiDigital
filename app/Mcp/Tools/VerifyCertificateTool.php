<?php

namespace App\Mcp\Tools;

use App\Models\Certificate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Verifies a certificate by its code. Returns student, course and status. Public read-only.')]
#[IsReadOnly]
#[IsIdempotent]
class VerifyCertificateTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(
            ['code' => 'required|string|max:100'],
            ['code.required' => 'You must provide a certificate code, e.g. "CERT-123".']
        );

        $cert = Certificate::with(['student:id,full_name', 'course:id,title'])
            ->where('certificate_code', $validated['code'])
            ->first();

        if (! $cert) {
            return Response::error("Certificate not found for code [{$validated['code']}].");
        }

        return Response::structured([
            'code' => $cert->certificate_code,
            'status' => $cert->status,
            'student' => $cert->student?->full_name,
            'course' => $cert->course?->title,
            'issue_date' => $cert->issue_date?->toDateString(),
        ]);
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'code' => $schema->string()->description('Certificate code to verify.')->required(),
        ];
    }
}
