<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

/*
 * Example SimpleSAMLphp SAML 2.0 SP
 */
$metadata['http://ssp-sp1.local:8081'] = [
    'entityid' => 'http://ssp-sp1.local:8081',
    'name' => ['en' => 'SP1 (custom port)'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'http://ssp-sp1.local:8081/module.php/saml/sp/saml2-acs.php/ssp-hub-custom-port'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-sp1.local:8081/module.php/saml/sp/saml2-logout.php/ssp-hub-custom-port'
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'IDPList' => [
        'http://ssp-idp1.local:8085',
        'http://ssp-idp2.local:8086',
        'http://ssp-idp3.local:8087',
        'http://ssp-idp4.local:8088',
    ],
    'assertion.encryption' => true,
];

$metadata['http://ssp-sp1.local'] = [
    'entityid' => 'http://ssp-sp1.local',
    'name' => ['en' => 'SP1'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'http://ssp-sp1.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-sp1.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'IDPList' => [
        'http://ssp-idp1.local',
        'http://ssp-idp2.local',
        'http://ssp-idp3.local',
        'http://ssp-idp4.local',
    ],
    'assertion.encryption' => true,
];

$metadata['http://ssp-sp2.local:8082'] = [
    'entityid' => 'http://ssp-sp2.local:8082',
    'name' => ['en' => 'SP2 (custom port)'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'http://ssp-sp2.local:8082/module.php/saml/sp/saml2-acs.php/ssp-hub-custom-port'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-sp2.local:8082/module.php/saml/sp/saml2-logout.php/ssp-hub-custom-port'
        ],
    ],
    'IDPList' => [
        'http://ssp-idp2.local:8086',
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'assertion.encryption' => true,
];

$metadata['http://ssp-sp2.local'] = [
    'entityid' => 'http://ssp-sp2.local',
    'name' => ['en' => 'SP2'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'http://ssp-sp2.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-sp2.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'http://ssp-idp2.local',
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'assertion.encryption' => true,
];

// for test purposes, SP3 should be on the SPList entry of idp2

$metadata['http://ssp-sp3.local:8083'] = [
    'entityid' => 'http://ssp-sp3.local:8083',
    'name' => ['en' => 'SP3 (custom port)'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'http://ssp-sp3.local:8083/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-sp3.local:8083/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'http://ssp-idp1.local:8085',
        'http://ssp-idp2.local:8086',  // overruled by Idp2
        'http://ssp-idp3.local:8087'
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'assertion.encryption' => true,
];

$metadata['http://ssp-sp3.local'] = [
    'entityid' => 'http://ssp-sp3.local',
    'name' => ['en' => 'SP3'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'http://ssp-sp3.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-sp3.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'http://ssp-idp1.local',
        'http://ssp-idp2.local',  // overruled by Idp2
        'http://ssp-idp3.local'
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'assertion.encryption' => true,
];
