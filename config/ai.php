<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External LLM (OpenAI-compatible)
    |--------------------------------------------------------------------------
    | Works with OpenAI, Groq, DeepSeek, Ollama, etc. Anything speaking
    | POST {base_url}/chat/completions with chat messages + tools.
    */
    'base_url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),
    'api_key' => env('AI_API_KEY', ''),
    'model' => env('AI_MODEL', 'gpt-4o-mini'),
    'temperature' => (float) env('AI_TEMPERATURE', 0.2),
    'max_tool_rounds' => (int) env('AI_MAX_TOOL_ROUNDS', 5),
    'timeout' => (int) env('AI_TIMEOUT', 60),
];
