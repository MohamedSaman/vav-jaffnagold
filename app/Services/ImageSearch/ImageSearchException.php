<?php

namespace App\Services\ImageSearch;

use RuntimeException;
use Throwable;

/**
 * Thrown when the image search microservice cannot fulfil a request.
 *
 * Carries a customer-safe message alongside the technical detail, so callers can
 * show something sensible without leaking internal URLs or stack traces.
 */
class ImageSearchException extends RuntimeException
{
    /** Whether retrying later could plausibly succeed (service down vs. bad input). */
    public bool $retryable;

    /** Machine-readable code from the Python service, e.g. 'invalid_image'. */
    public ?string $errorCode;

    public array $context;

    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        bool $retryable = false,
        ?string $errorCode = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);

        $this->retryable = $retryable;
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public static function unreachable(string $reason, ?Throwable $previous = null): self
    {
        return new self(
            "The image search service is unreachable: {$reason}",
            503,
            $previous,
            retryable: true,
            errorCode: 'service_unreachable'
        );
    }

    public static function fromResponse(int $status, array $body): self
    {
        $errorCode = $body['error'] ?? 'unknown_error';
        $message = $body['message'] ?? 'The image search service returned an error.';

        return new self(
            $message,
            $status,
            null,
            // 5xx and 429 may succeed on a retry; 4xx means the request itself
            // is wrong and repeating it will not help.
            retryable: $status >= 500 || $status === 429,
            errorCode: $errorCode,
            context: $body['details'] ?? []
        );
    }

    /**
     * A message safe to show a customer, mapped from the technical error code.
     */
    public function userMessage(): string
    {
        return match ($this->errorCode) {
            'invalid_image' => 'That file could not be read as an image. Please upload a clear JPG, PNG or WEBP photo.',
            'payload_too_large' => 'That image is too large. Please upload a photo under 5 MB.',
            'model_not_ready' => 'Visual search is starting up. Please try again in a few seconds.',
            'service_unreachable', 'internal_error' => 'Visual search is temporarily unavailable. Please try again shortly.',
            default => 'We could not complete the visual search. Please try another photo.',
        };
    }
}
