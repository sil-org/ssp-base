<?php

namespace SimpleSAML\Module\silauth\Auth\Source\captcha;

final readonly class CaptchaResult
{
    public function __construct(
        public bool    $ok,
        public ?string $reason = null,
        public array   $context = [],
    ) {}

    public static function success(): self
    {
        return new self(true);
    }

    public static function failure(string $reason, array $context = []): self
    {
        return new self(false, $reason, $context);
    }
}
