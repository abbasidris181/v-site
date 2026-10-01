<?php

namespace App\Services\Providers\DTO;

class ProviderResult
{
    public function __construct(
        public bool $isSuccessful,
        public ?string $statusMessage = null,
        public ?array $data = null,
        public ?string $errorCode = null,
        public array $rawPayload = []
    ) {}

    /**
     * Factory for successful verification.
     */
    public static function success(array $data, ?string $message = 'Verification successful.', array $raw = []): self
    {
        return new self(
            isSuccessful: true,
            statusMessage: $message,
            data: $data,
            errorCode: null,
            rawPayload: $raw ?: $data
        );
    }

    /**
     * Factory for failed verification.
     */
    public static function failure(string $message, ?string $errorCode = 'PROVIDER_ERROR', array $raw = []): self
    {
        return new self(
            isSuccessful: false,
            statusMessage: $message,
            data: null,
            errorCode: $errorCode,
            rawPayload: $raw
        );
    }

    /**
     * Convert to array for database JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_successful' => $this->isSuccessful,
            'status_message' => $this->statusMessage,
            'data' => $this->data,
            'error_code' => $this->errorCode,
            'raw_payload' => $this->rawPayload,
            'verified_at' => now()->toIso8601String(),
        ];
    }
}
