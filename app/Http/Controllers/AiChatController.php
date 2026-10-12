<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response as McpResponse;
use Laravel\Mcp\ResponseFactory as McpResponseFactory;

class AiChatController extends Controller
{
    /**
     * MCP tool classes visible to everyone (public read-only).
     *
     * @var array<int, class-string>
     */
    private const PUBLIC_TOOLS = [
        \App\Mcp\Tools\ListCoursesTool::class,
        \App\Mcp\Tools\ListCourseClassesTool::class,
        \App\Mcp\Tools\VerifyCertificateTool::class,
    ];

    /**
     * Extra MCP tools for admins only.
     *
     * @var array<int, class-string>
     */
    private const ADMIN_TOOLS = [
        \App\Mcp\Tools\ListAttendancesTool::class,
        \App\Mcp\Tools\CreateAttendanceTool::class,
        \App\Mcp\Tools\DeleteAttendanceTool::class,
        \App\Mcp\Tools\InstructorEarningsTool::class,
    ];

    public function index(Request $request)
    {
        $isAdmin = $request->user()?->hasRole('admin') ?? false;

        return view('ai.chat', [
            'isAdmin' => $isAdmin,
            'model' => config('ai.model'),
            'tools' => array_map(fn ($c) => app($c)->toArray(), $this->toolClasses($request)),
            'hasKey' => (bool) config('ai.api_key'),
        ]);
    }

    public function tools(Request $request)
    {
        return response()->json([
            'tools' => array_map(fn ($c) => app($c)->toArray(), $this->toolClasses($request)),
            'is_admin' => (bool) ($request->user()?->hasRole('admin') ?? false),
        ]);
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'messages' => 'required|array|min:1|max:50',
            'messages.*.role' => 'required|string|in:user,assistant,system',
            'messages.*.content' => 'required|string|max:8000',
        ]);

        $baseUrl = rtrim((string) config('ai.base_url'), '/');
        $apiKey = (string) config('ai.api_key');
        $model = (string) config('ai.model', 'gpt-4o-mini');

        if ($baseUrl === '' || $apiKey === '') {
            return response()->json([
                'message' => 'AI key not configured. Set AI_BASE_URL / AI_API_KEY / AI_MODEL in .env (OpenAI-compatible).',
            ], 422);
        }

        $toolClasses = $this->toolClasses($request);
        $openAiTools = $this->toOpenAiTools($toolClasses);
        // name => class map for execution
        $nameToClass = [];
        foreach ($toolClasses as $class) {
            $nameToClass[app($class)->toArray()['name']] = $class;
        }

        $isAdmin = (bool) ($request->user()?->hasRole('admin') ?? false);
        $messages = array_merge([
            [
                'role' => 'system',
                'content' => 'You are the CertiDigital assistant. Answer in the user language (default Portuguese). '
                    .'Use the available tools to query real data (courses, classes, attendances, certificates, instructor earnings). '
                    .($isAdmin ? 'The user is admin: you may also create/delete attendances and report earnings.'
                        : 'The user is NOT admin: only read-only tools are available. Never invent hours or payments.'),
            ],
        ], $validated['messages']);

        $usedTools = [];
        $maxRounds = max(1, (int) config('ai.max_tool_rounds', 5));

        for ($round = 0; $round < $maxRounds; $round++) {
            $response = Http::timeout((int) config('ai.timeout', 60))
                ->withToken($apiKey)
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => (float) config('ai.temperature', 0.2),
                    'messages' => $messages,
                    'tools' => $openAiTools,
                    'tool_choice' => 'auto',
                ]);

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'LLM request failed.',
                    'status' => $response->status(),
                    'detail' => substr((string) $response->body(), 0, 2000),
                ], 502);
            }

            $choice = $response->json('choices.0.message', []);
            $toolCalls = $choice['tool_calls'] ?? [];

            if (empty($toolCalls)) {
                return response()->json([
                    'reply' => $choice['content'] ?? '',
                    'used_tools' => $usedTools,
                    'model' => $response->json('model', $model),
                ]);
            }

            // Append assistant tool-call message, then execute each tool.
            $messages[] = [
                'role' => 'assistant',
                'content' => $choice['content'] ?? null,
                'tool_calls' => $toolCalls,
            ];

            foreach ($toolCalls as $call) {
                $toolName = $call['function']['name'] ?? '';
                $rawArgs = $call['function']['arguments'] ?? '{}';
                $args = json_decode(is_string($rawArgs) ? $rawArgs : json_encode($rawArgs), true) ?? [];
                $callId = $call['id'] ?? uniqid('call_');

                $result = $this->executeTool($nameToClass, $toolName, $args);
                $usedTools[] = ['tool' => $toolName, 'args' => $args];

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $callId,
                    'content' => $result,
                ];
            }
        }

        return response()->json([
            'reply' => 'Tool round limit reached. Please refine the question.',
            'used_tools' => $usedTools,
        ], 200);
    }

    /**
     * @return array<int, class-string>
     */
    private function toolClasses(Request $request): array
    {
        $isAdmin = (bool) ($request->user()?->hasRole('admin') ?? false);

        return $isAdmin
            ? array_merge(self::PUBLIC_TOOLS, self::ADMIN_TOOLS)
            : self::PUBLIC_TOOLS;
    }

    /**
     * @param  array<int, class-string>  $classes
     */
    private function toOpenAiTools(array $classes): array
    {
        return array_map(function ($class) {
            $meta = app($class)->toArray();

            return [
                'type' => 'function',
                'function' => [
                    'name' => $meta['name'],
                    'description' => substr((string) ($meta['description'] ?? $meta['name']), 0, 1000),
                    'parameters' => $meta['inputSchema'] ?? ['type' => 'object', 'properties' => new \stdClass],
                ],
            ];
        }, $classes);
    }

    /**
     * @param  array<string, class-string>  $nameToClass
     */
    private function executeTool(array $nameToClass, string $toolName, array $args): string
    {
        if (! isset($nameToClass[$toolName])) {
            return "Tool [{$toolName}] is not available.";
        }

        try {
            $tool = app($nameToClass[$toolName]);
            $result = $tool->handle(new McpRequest($args));

            if ($result instanceof McpResponseFactory) {
                $structured = $result->getStructuredContent();
                if (is_array($structured)) {
                    return substr(json_encode($structured, JSON_UNESCAPED_UNICODE), 0, 4000);
                }
                $texts = [];
                foreach ($result->responses() as $r) {
                    $texts[] = $this->responseText($r);
                }

                return substr(implode("\n", $texts), 0, 4000);
            }

            return substr($this->responseText($result), 0, 4000);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return 'Validation: '.implode(' ', array_map(
                fn ($m) => is_array($m) ? implode(' ', $m) : (string) $m,
                $e->errors()
            ));
        } catch (\Throwable $e) {
            report($e);

            return 'Tool error: '.substr($e->getMessage(), 0, 500);
        }
    }

    private function responseText(McpResponse $response): string
    {
        $arr = $response->content()->toArray();

        return (string) ($arr['text'] ?? json_encode($arr, JSON_UNESCAPED_UNICODE));
    }
}
