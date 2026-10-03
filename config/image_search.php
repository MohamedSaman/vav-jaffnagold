<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature toggle
    |--------------------------------------------------------------------------
    |
    | The master switch. When false, the visual search route returns 404, the
    | nav button is hidden and the product observer stops queueing sync jobs —
    | so the storefront behaves exactly as it did before this feature existed.
    | Flip this off if the Python service has to be taken down.
    |
    */

    'enabled' => env('IMAGE_SEARCH_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Python microservice
    |--------------------------------------------------------------------------
    |
    | Base URL of the FastAPI service. Keep it on localhost (or a private
    | network) — it is not designed to face the public internet. The API key
    | must match API_KEY in ai-image-search/.env.
    |
    */

    'base_url' => rtrim(env('IMAGE_SEARCH_URL', 'http://127.0.0.1:8001'), '/'),

    'api_key' => env('IMAGE_SEARCH_API_KEY', ''),

    'timeouts' => [
        // A search is user-facing: fail fast rather than hanging the page.
        'search'  => (float) env('IMAGE_SEARCH_TIMEOUT', 15),
        // Indexing one product includes a CLIP forward pass, so allow more.
        'index'   => (float) env('IMAGE_SEARCH_INDEX_TIMEOUT', 30),
        // Rebuild returns 202 immediately; this only covers the handshake.
        'rebuild' => (float) env('IMAGE_SEARCH_REBUILD_TIMEOUT', 20),
        'health'  => (float) env('IMAGE_SEARCH_HEALTH_TIMEOUT', 5),
        'connect' => (float) env('IMAGE_SEARCH_CONNECT_TIMEOUT', 5),
    ],

    // Transport-level retries. Application errors (4xx) are never retried.
    'retries' => [
        'times' => (int) env('IMAGE_SEARCH_RETRY_TIMES', 2),
        'sleep' => (int) env('IMAGE_SEARCH_RETRY_SLEEP', 200), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload rules
    |--------------------------------------------------------------------------
    |
    | Enforced in Laravel before the file ever reaches Python, and again inside
    | the Python service. Keep max_kilobytes in step with MAX_UPLOAD_BYTES there.
    |
    */

    'upload' => [
        'max_kilobytes' => (int) env('IMAGE_SEARCH_MAX_KB', 5120), // 5 MB
        'mimes'         => ['jpeg', 'jpg', 'png', 'webp', 'gif', 'bmp'],
        'mime_types'    => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/bmp',
            'image/x-ms-bmp',
        ],
        'min_dimension' => 32,
        'max_dimension' => 8000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Search behaviour
    |--------------------------------------------------------------------------
    */

    'search' => [
        'top_k' => (int) env('IMAGE_SEARCH_TOP_K', 20),

        // Cosine similarity floor. Null defers to the Python service default
        // (0.55). Raise for stricter matches, lower to always show something.
        'min_similarity' => env('IMAGE_SEARCH_MIN_SIMILARITY') !== null
            ? (float) env('IMAGE_SEARCH_MIN_SIMILARITY')
            : null,

        // boost | partition | strict — see the Python .env.example for details.
        'strategy' => env('IMAGE_SEARCH_STRATEGY', 'boost'),

        // Rate limit per IP on the public search endpoints. CLIP inference is
        // CPU-bound, so this protects the box from a scripted flood.
        'rate_limit' => [
            'attempts' => (int) env('IMAGE_SEARCH_RATE_ATTEMPTS', 20),
            'decay_minutes' => (int) env('IMAGE_SEARCH_RATE_DECAY', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Index synchronisation
    |--------------------------------------------------------------------------
    |
    | Product create/update/delete queues a job that notifies the Python
    | service. Queueing matters: if the service is down, admin product saves
    | must still succeed. The job retries with a backoff and then gives up —
    | the nightly rebuild is the safety net that repairs any drift.
    |
    */

    'sync' => [
        'enabled'    => env('IMAGE_SEARCH_SYNC_ENABLED', true),
        'queue'      => env('IMAGE_SEARCH_QUEUE', 'default'),
        'connection' => env('IMAGE_SEARCH_QUEUE_CONNECTION'), // null = default
        'tries'      => (int) env('IMAGE_SEARCH_SYNC_TRIES', 3),
        'backoff'    => [30, 120, 600], // seconds between attempts

        // Only sync products the storefront shows, matching the Python service's
        // INDEX_WEBSITE_LIVE_ONLY setting.
        'website_live_only' => env('IMAGE_SEARCH_WEBSITE_LIVE_ONLY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Product image URLs
    |--------------------------------------------------------------------------
    |
    | The Python service normally reads product images straight off disk, which
    | is fastest. Set 'send_absolute_urls' to true when Laravel and Python do
    | not share a filesystem (separate hosts, or images on a CDN); Python will
    | then fetch each image over HTTP instead.
    |
    */

    'images' => [
        'send_absolute_urls' => env('IMAGE_SEARCH_SEND_URLS', false),
        'placeholders' => ['images/product.jpg', 'images/products/product.jpg'],
    ],

];
