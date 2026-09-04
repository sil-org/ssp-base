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

// Used for Hub ForceAuthn test w/ exactly one IDPList
$metadata['https://ssp-sp4.local'] = [
    'entityid' => 'https://ssp-sp4.local',
    'name' => ['en' => 'SP4'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'https://ssp-sp4.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://ssp-sp4.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'https://ssp-idp2.local',
    ],
    'certData' => 'MIIDNzCCAh8CFBWZrDBYf37B59FYeN0ypw8p35rRMA0GCSqGSIb3DQEBCwUAMFgxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJUWDEPMA0GA1UEBwwGRGFsbGFzMRMwEQYDVQQKDApTSUwgR2xvYmFsMRYwFAYDVQQDDA1zc3Atc3A0LmxvY2FsMB4XDTI2MDkwMzIyNTc1OVoXDTM2MDgzMTIyNTc1OVowWDELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAlRYMQ8wDQYDVQQHDAZEYWxsYXMxEzARBgNVBAoMClNJTCBHbG9iYWwxFjAUBgNVBAMMDXNzcC1zcDQubG9jYWwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCxM+nBnJrRPFZ79z7jdxbNB2vOCgFuJYvXDBOY2BFvZAALJKNuy4uU0ypfZNLip7Jo1fvOohHOXOS1GxVlVr0pgCWj7QHv+nQOkQ1KFIKkyoda0ErVAVyz1rzKQp7mB0x0gKn5QXeyIPo1eZ15+DRPWhvn7n1qIAB9Y0VhNGmzu4AaycknPX8osB9JnkUMlk7l8/gZ0JfX33Y58IS+5gkfmySf56ffdu65l4eZ7hoFPOIddsU/okGFp54veQesaJFb8eKL9iQWxUDgjADsiX1OHpcYBt84gabAj3XwPdfTgWz/pvC+rE2Q8tiLAFR31bGjUTvij1JXoL38Kf7/qyCNAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAHB/TK3ZQAG1ED6CNrUu+XwpFOItX1FLSlkdmuRp8Y0U6WrOF5XuTI0UfbdVvWjHGHXuouOr/cBZISJCpPAklsfSaW+JBfRwG3xQ7uevqwNiX36ZYUg2NNiSPxC7cjcvhkX45NhE2mIYISUHfg5pfRoiEmUIFhzmDWCfX90q3ay8RNt4lCEIHPwQL0jW70NKM+ty8IBLpA6X2DZfUY/AApp1S9+g7R9DZTz1LNgv0IJkjvRvKz2D5PMP4xAnD0QggdGQUfrGZHrb8FzEM0SflQ0MLtpvko5XZHaXKDhke6oJSj0r/L8KWBw8zWyh0qUwECr1aLpjgwDMqZi7xt+P6WM=',
    'assertion.encryption' => true,
    'ForceAuthn' => true,
];

// Used for Hub ForceAuthn tests w/ 2+ IDPList
$metadata['https://ssp-sp5.local'] = [
    'entityid' => 'https://ssp-sp5.local',
    'name' => ['en' => 'SP5'],
    'AssertionConsumerService' => [
        [
            'index' => 1,
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'Location' => 'https://ssp-sp5.local/module.php/saml/sp/saml2-acs.php/ssp-hub'
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'https://ssp-sp5.local/module.php/saml/sp/saml2-logout.php/ssp-hub'
        ],
    ],
    'IDPList' => [
        'https://ssp-idp1.local',
        'https://ssp-idp2.local',
        'https://ssp-idp3.local'
    ],
    'certData' => 'MIIDNzCCAh8CFHY562EF5TN6dpDfJd3uRMYm+LL3MA0GCSqGSIb3DQEBCwUAMFgxCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJUWDEPMA0GA1UEBwwGRGFsbGFzMRMwEQYDVQQKDApTSUwgR2xvYmFsMRYwFAYDVQQDDA1zc3Atc3A1LmxvY2FsMB4XDTI2MDkwMzIyNTg1MloXDTM2MDgzMTIyNTg1MlowWDELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAlRYMQ8wDQYDVQQHDAZEYWxsYXMxEzARBgNVBAoMClNJTCBHbG9iYWwxFjAUBgNVBAMMDXNzcC1zcDUubG9jYWwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDUez/2TfrkSGh+YoRV7x1FjPTfTjDrFEMG/RNWAm1hRZ6AjQmdUslWxYNjUJ7I95q5VTw1GRe7wQfyf63ct7nJAskKllf6i3D366kDs5Jcw6uhuBFfGtyRw97iwi+mRjSVGBGMkWgZrbrrMLRosXffJ8uVqu7hlke1upZq+EDAsNu9kHxNtvAeAugJLrre381BYT8SP9clKyr1vkTGInyTlZSdOQOTikfB1gNPuxsRrTJmakRbOBXipZRjBdcfA4jXPmV62hY1jJcxz9yAGTGRJsLoj6HWv+oLRetlYOTgM7ir3+PldPyNnUUIaPJ93EvCREX9wNHWVE4VxVsDbUtLAgMBAAEwDQYJKoZIhvcNAQELBQADggEBAMzmmzwvwmJwRiKNPnjS2ET0SwCVwQRWvgk2zdfH6Xb5LyuaOm25QTVAMBRHfuPYr8DpwIhZzMUFKgyTmps/5yZjHL8cj87j/8SxwL8MYgMtwuh1iltURe14+u4BOfswPu801i1F6luqeUeHMbtczTB9XtOEv1yXPfI97EgDYRziF1qefX4X55saDwyLKNN8KsjH7iJEtJDgy/LYbiSFV4xI1xazc5ZhI34B1pMAKp6J11iypscS6cmpiVgswnJUzd1Fslhe3cRA+ZFrIFr+L1prtbF/gHmGQWeNpZ6PfPR5ARw4tOzlat9VqTfdj3gn93uQoI25ZyIG7V1UoZnMegk=',
    'assertion.encryption' => true,
    'ForceAuthn' => true,
];
