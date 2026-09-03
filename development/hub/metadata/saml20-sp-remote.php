<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

/*
 * Example SimpleSAMLphp SAML 2.0 SP
 */
$metadata['https://ssp-sp1.local'] = [
    'entityid' => 'https://ssp-sp1.local',
    'name' => ['en' => 'SP1'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'https://ssp-sp1.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://ssp-sp1.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'IDPList' => [
        'https://ssp-idp1.local',
        'https://ssp-idp2.local',
        'https://ssp-idp3.local',
        'https://ssp-idp4.local',
    ],
    'assertion.encryption' => true,
];

$metadata['https://ssp-sp2.local'] = [
    'entityid' => 'https://ssp-sp2.local',
    'name' => ['en' => 'SP2'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'https://ssp-sp2.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://ssp-sp2.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'https://ssp-idp2.local',
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'assertion.encryption' => true,
    'ForceAuthn' => true,
];

// for test purposes, SP3 should be on the SPList entry of idp2

$metadata['https://ssp-sp3.local'] = [
    'entityid' => 'https://ssp-sp3.local',
    'name' => ['en' => 'SP3'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'https://ssp-sp3.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://ssp-sp3.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'https://ssp-idp1.local',
        'https://ssp-idp2.local',  // overruled by Idp2
        'https://ssp-idp3.local'
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPnOHgSgAeNrMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIyNzU2WhcNMjYxMDE3MTIyNzU2WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0u+mXWS8vUkKjtJcK1hd0iGW2vbTvYosgyDdqClcSzwpbWJg1A1ChuiQIf7S+5bWL2AN4zMoem/JTn7cE9octqU34ZJAyP/cesppA9G53F9gH4XdoPgnWsb8vdWooDDUk+asc7ah/XwKixQNcELPDZkOba5+pqoKGjMxfL7JQ6+P6LB+xItzvLBXU4+onbGPIF6pmZ8S74mt0J62Y6ne40BHx8FdrtBgdk5TFcDedW09rRJrTFpi3hGSUkcjqj84B+oLAb08Z0SHoELMp5Yh7Tg5QZ2c+S8I47tQjV72rNhUYhIyFuImzSg27R7aRJ6Jj6sK4zEg0Ai4VhO4RmgyzwIDAQABo1AwTjAdBgNVHQ4EFgQUgkYcMbT0o8kmxAz2O3+p1lDVj1MwHwYDVR0jBBgwFoAUgkYcMbT0o8kmxAz2O3+p1lDVj1MwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANgyTgMVRghgL8klqvZvQpfh80XDPTZotJCc8mZJZ98YkNC8jnR2RIUJpah+XrgotlKNDOK3HMNuyKGgYcqcno4PdDXKbqp4yXmywdNbbEHwPWDGqZXULw2az+UVwPUZJcJyJuwJjy3diCJT53N9G0LqXfeEsV0OPQPaB2PWgYNraBd59fckmBTc298HuvsHtxUcoXM53ms2Ck6GygGwH1vCg7qyIRRQFL4DiSlnoS8jxt3IIpZZs9FAl1ejtFBepSne9kEo7lLhAWY1TQqRrRXNHngG/L70ZkZonE9TNK/9xIHuaawqWkV6WLnkhT0DHCOw67GP97MWzceyFw+n9Vg==',
    'assertion.encryption' => true,
];
