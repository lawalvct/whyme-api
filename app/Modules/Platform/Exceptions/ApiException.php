<?php

namespace App\Modules\Platform\Exceptions;

use RuntimeException;

/**
 * An error with a stable code that clients translate (docs/01 §5.6).
 * Rendered as { code, message, fields, retryable } with `data` added when given.
 */
class ApiException extends RuntimeException
{
    /**
     * @param  array<string, list<string>>  $fields
     * @param  array<string, mixed>|null  $data  e.g. the current server record on a 409 conflict
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $errorCode,
        public readonly int $status = 400,
        public readonly bool $retryable = false,
        public readonly array $fields = [],
        public readonly ?array $data = null,
        public readonly array $headers = [],
    ) {
        parent::__construct($errorCode);
    }

    /**
     * A field-level validation failure with a specific code (e.g. otp_invalid).
     */
    public static function field(string $code, string $field, int $status = 422): self
    {
        return new self($code, $status, fields: [$field => [__("errors.$code")]]);
    }
}
