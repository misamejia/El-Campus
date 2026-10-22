<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver de IA
    |--------------------------------------------------------------------------
    | 'auto'   -> usa Gemini si hay GEMINI_API_KEY, si no el driver fake.
    | 'gemini' -> fuerza Gemini (falla si no hay API key).
    | 'fake'   -> heurísticas locales, sin red. Ideal para tests y demos.
    */
    'driver' => env('AI_DRIVER', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Modelos
    |--------------------------------------------------------------------------
    */
    'text_model' => env('AI_TEXT_MODEL', 'gemini-3.8-flash'),
    'embedding_model' => env('AI_EMBEDDING_MODEL', 'gemini-embedding-001'),

    // Dimensión de los embeddings del driver fake (los reales usan la suya).
    'embedding_dimensions' => (int) env('AI_EMBEDDING_DIMENSIONS', 256),

    // Número de noticias relacionadas a mostrar.
    'relacionadas' => (int) env('AI_RELACIONADAS', 3),

    // Límite de caracteres de contenido enviados al modelo (controla coste).
    'max_caracteres' => (int) env('AI_MAX_CARACTERES', 8000),
];
