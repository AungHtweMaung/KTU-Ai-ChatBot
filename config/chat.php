<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active AI provider
    |--------------------------------------------------------------------------
    | Which AiProvider implementation to bind. Supported: "openai", "gemini", "groq".
    | Change via CHAT_PROVIDER in .env — no code changes required.
    */
    'provider' => env('CHAT_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Provider credentials & models
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        ],
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversation history sliding window
    |--------------------------------------------------------------------------
    | How many previous turns (user + assistant messages combined) to include
    | in the prompt sent to the AI. Older messages are never transmitted.
    */
    'history_window' => (int) env('CHAT_HISTORY_WINDOW', 20),

    /*
    |--------------------------------------------------------------------------
    | HTTP timeouts (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('CHAT_HTTP_TIMEOUT', 30),
    'connect_timeout' => (int) env('CHAT_HTTP_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Maximum length of a single user message (characters)
    |--------------------------------------------------------------------------
    */
    'max_message_length' => (int) env('CHAT_MAX_MESSAGE_LENGTH', 2000),

    /*
    |--------------------------------------------------------------------------
    | Supported intents
    |--------------------------------------------------------------------------
    | Whitelist used to validate the AI's structured intent response. Anything
    | outside this list is coerced to 'clarification_required'.
    */
    'intents' => [
        'teacher_search',
        'teacher_profile',
        'subject_search',
        'major_information',
        'department_information',
        'registration_fee',
        'registration_schedule',
        'announcement_search',
        'event_search',
        'faq_search',
        'greeting',
        'general_chat',
        'clarification_required',
    ],
];
