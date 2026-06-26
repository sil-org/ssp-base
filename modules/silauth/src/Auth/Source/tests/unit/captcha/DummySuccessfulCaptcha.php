<?php

namespace SimpleSAML\Module\silauth\Auth\Source\tests\unit\captcha;

use SimpleSAML\Module\silauth\Auth\Source\captcha\Captcha;
use SimpleSAML\Module\silauth\Auth\Source\captcha\CaptchaResult;
use SimpleSAML\Module\silauth\Auth\Source\http\Request;

class DummySuccessfulCaptcha extends Captcha
{
    public function validate(Request $request): CaptchaResult
    {
        return CaptchaResult::success();
    }
}
