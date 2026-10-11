<?php

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\mfa\Auth\Process\Mfa;

class MfaTest extends TestCase
{
    public function testMaskEmail()
    {
        $this->assertEquals("j**n@e******.c**", Mfa::maskEmail("john@example.com"));
        $this->assertEquals("j***_s***h@e******.c**", Mfa::maskEmail("john_smith@example.com"));
        $this->assertEquals("t**t@t***.e******.c**", Mfa::maskEmail("test@test.example.com"));
        $this->assertEquals("t@e.c*", Mfa::maskEmail("t@e.cc"));

        // just to be sure it doesn't throw an exception...
        $this->assertEquals("t**t@e******..c**", Mfa::maskEmail("test@example..com"));
        $this->assertEquals("@", Mfa::maskEmail("@"));
    }
}
