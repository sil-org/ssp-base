<?php

namespace SimpleSAML\Module\silauth\Auth\Source\captcha;

use ReCaptcha\ReCaptcha;
use RuntimeException;
use SimpleSAML\Module\silauth\Auth\Source\http\Request;

class Captcha
{
    private ?string $secret;

    public function __construct(?string $secret = null)
    {
        $this->secret = $secret;
    }

    public function validate(Request $request): CaptchaResult
    {
        if (empty($this->secret)) {
            throw new RuntimeException('No captcha secret available.', 1487342411);
        }

        $captchaResponse = $request->getCaptchaResponse();
        if (empty($captchaResponse)) {
            return CaptchaResult::failure('captcha_response_empty');
        }

        $ipAddress = $request->getMostLikelyIpAddress();

        $recaptcha = new ReCaptcha($this->secret);
        $rcResponse = $recaptcha->verify($captchaResponse, $ipAddress);

        if ($rcResponse->isSuccess()) {
            return CaptchaResult::success();
        }

        return CaptchaResult::failure(
            'captcha_verification_failed',
            [
                'ip' => $ipAddress,
                'errorCodes' => $rcResponse->getErrorCodes(),
            ]
        );
    }
}
