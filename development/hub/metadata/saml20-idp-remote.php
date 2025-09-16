<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

/*
 * IdP 1
 */
$metadata['http://ssp-idp1.local:8085'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp1.local:8085',
    'name' => [
        'en' => 'IDP 1:8085',
    ],
    'IDPNamespace' => 'IDP-1-custom-port',
    'logoCaption' => 'IDP-1:8085 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+1+8085',

    'description' => 'Local IDP for testing SSP Hub (custom port)',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp1.local:8085/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp1.local:8085/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPlZYTAQSIbHMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIzMTQ1WhcNMjYxMDE3MTIzMTQ1WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArssOaeKbdOQFpN6bBolwSJ/6QFBXA73Sotg60anx9v6aYdUTmi+b7SVtvOmHDgsD5X8pN/6Z11QCZfTYg2nW3ZevGZsj8W/R6C8lRLHzWUr7e7DXKfj8GKZptHlUs68kn0ndNVt9r/+irJe9KBdZ+4kAihykomNdeZg06bvkklxVcvpkOfLTQzEqJAmISPPIeOXes6hXORdqLuRNTuIKarcZ9rstLnpgAs2TE4XDOrSuUg3XFnM05eDpFQpUb0RXWcD16mLCPWw+CPrGoCfoftD5ZGfll+W2wZ7d0kQ4TbCpNyxQH35q65RPVyVNPgSNSsFFkmdcqP9DsFqjJ8YC6wIDAQABo1AwTjAdBgNVHQ4EFgQUD6oyJKOPPhvLQpDCC3027QcuQwUwHwYDVR0jBBgwFoAUD6oyJKOPPhvLQpDCC3027QcuQwUwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEAA6tCLHJQGfXGdFerQ3J0wUu8YDSLb0WJqPtGdIuyeiywR5ooJf8G/jjYMPgZArepLQSSi6t8/cjEdkYWejGnjMG323drQ9M1sKMUhOJF4po9R3t7IyvGAL3fSqjXA8JXH5MuGuGtChWxaqhduA0dBJhFAtAXQ61IuIQF7vSFxhTwCvJnaWdWD49sG5OqjCfgIQdY/mw70e45rLnR/bpfoigL67sTJxy+Kx2ogbvMR6lITByOEQFMt7BYpMtXrwvKUM7k9NOo1jREmJacC8PTx//jRhCWwzUj1RsfIri24BuITrawwqMsYl8DZiiwMpjUf9m4NPaf4E7+QRpzo+MCcg==',
];
$metadata['http://ssp-idp1.local'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp1.local',
    'name' => [
        'en' => 'IDP 1',
    ],
    'IDPNamespace' => 'IDP-1',
    'logoCaption' => 'IDP-1 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+1',

    'description' => 'Local IDP for testing SSP Hub (default port)',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp1.local/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp1.local/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJAPlZYTAQSIbHMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE3MTIzMTQ1WhcNMjYxMDE3MTIzMTQ1WjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArssOaeKbdOQFpN6bBolwSJ/6QFBXA73Sotg60anx9v6aYdUTmi+b7SVtvOmHDgsD5X8pN/6Z11QCZfTYg2nW3ZevGZsj8W/R6C8lRLHzWUr7e7DXKfj8GKZptHlUs68kn0ndNVt9r/+irJe9KBdZ+4kAihykomNdeZg06bvkklxVcvpkOfLTQzEqJAmISPPIeOXes6hXORdqLuRNTuIKarcZ9rstLnpgAs2TE4XDOrSuUg3XFnM05eDpFQpUb0RXWcD16mLCPWw+CPrGoCfoftD5ZGfll+W2wZ7d0kQ4TbCpNyxQH35q65RPVyVNPgSNSsFFkmdcqP9DsFqjJ8YC6wIDAQABo1AwTjAdBgNVHQ4EFgQUD6oyJKOPPhvLQpDCC3027QcuQwUwHwYDVR0jBBgwFoAUD6oyJKOPPhvLQpDCC3027QcuQwUwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEAA6tCLHJQGfXGdFerQ3J0wUu8YDSLb0WJqPtGdIuyeiywR5ooJf8G/jjYMPgZArepLQSSi6t8/cjEdkYWejGnjMG323drQ9M1sKMUhOJF4po9R3t7IyvGAL3fSqjXA8JXH5MuGuGtChWxaqhduA0dBJhFAtAXQ61IuIQF7vSFxhTwCvJnaWdWD49sG5OqjCfgIQdY/mw70e45rLnR/bpfoigL67sTJxy+Kx2ogbvMR6lITByOEQFMt7BYpMtXrwvKUM7k9NOo1jREmJacC8PTx//jRhCWwzUj1RsfIri24BuITrawwqMsYl8DZiiwMpjUf9m4NPaf4E7+QRpzo+MCcg==',
];

/*
 * IdP 2
 */
$metadata['http://ssp-idp2.local:8086'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp2.local:8086',
    'name' => [
        'en' => 'IDP 2:8086',
    ],
    'IDPNamespace' => 'IDP-2-custom-port',
    'logoCaption' => 'IDP-2:8086 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+2+8086',

    'description' => 'Local IDP2 for testing SSP Hub (custom port)',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp2.local:8086/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp2.local:8086/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJALBaUrvz1X5DMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE4MTQwMDUxWhcNMjYxMDE4MTQwMDUxWjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx5mZNwjEnakJho+5etuFyx+2g9rs96iLX/LDC24aBAsdNxTNuIc1jJ7pxBxGrepEND4LkietLNBlOr1q50nq2+ddTrCfmoJB+9BqBOxcm9qWeqWbp8/arUjaxPzK3DfZrxJxIVFjzqFF7gI91y9yvEW/fqLRMhvnH1ns+N1ne59zr1y6h9mmHfBffGr1YXAfyEAuV1ich4AfTfjqhdwFwxhFLLCVnxA0bDbNw/0eGCSiA13N7a013xTurLeJu0AQaZYssMqvc/17UphH4gWDMEZAwy0EfRSBOsDOYCxeNxVajnWX1834VDpBDfpnZj996Gh8tzRQxQgT9/plHKhGiwIDAQABo1AwTjAdBgNVHQ4EFgQUApxlUQg26GrG3eH8lEG3SkqbH/swHwYDVR0jBBgwFoAUApxlUQg26GrG3eH8lEG3SkqbH/swDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANhbm8WgIqBDlF7DIRVUbq04TEA9nOJG8wdjJYdoKrPX9f/E9slkFuD2StcK99RTcowa8Z2OmW7tksa+onyH611Lq21QXh4aHzQUAm2HbsmPQRZnkByeYoCJ/1tuEho+x+VGanaUICSBVWYiebAQVKHR6miFypRElibNBizm2nqp6Q9B87V8COzyDVngR1DlWDduxYaNOBgvht3Rk9Y2pVHqym42dIfN+pprcsB1PGBkY/BngIuS/aqTENbmoC737vcb06e8uzBsbCpHtqUBjPpL2psQZVJ2Y84JmHafC3B7nFQrjdZBbc9eMHfPo240Rh+pDLwxdxPqRAZdeLaUkCQ==',

    // limit which Sps can use this IdP
    'SPList' => ['http://ssp-sp1.local:8081', 'http://ssp-sp2.local:8082'],
];
$metadata['http://ssp-idp2.local'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp2.local',
    'name' => [
        'en' => 'IDP 2',
    ],
    'IDPNamespace' => 'IDP-2',
    'logoCaption' => 'IDP-2 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+2',

    'description' => 'Local IDP2 for testing SSP Hub (normal port)',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp2.local/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp2.local/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJALBaUrvz1X5DMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE4MTQwMDUxWhcNMjYxMDE4MTQwMDUxWjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx5mZNwjEnakJho+5etuFyx+2g9rs96iLX/LDC24aBAsdNxTNuIc1jJ7pxBxGrepEND4LkietLNBlOr1q50nq2+ddTrCfmoJB+9BqBOxcm9qWeqWbp8/arUjaxPzK3DfZrxJxIVFjzqFF7gI91y9yvEW/fqLRMhvnH1ns+N1ne59zr1y6h9mmHfBffGr1YXAfyEAuV1ich4AfTfjqhdwFwxhFLLCVnxA0bDbNw/0eGCSiA13N7a013xTurLeJu0AQaZYssMqvc/17UphH4gWDMEZAwy0EfRSBOsDOYCxeNxVajnWX1834VDpBDfpnZj996Gh8tzRQxQgT9/plHKhGiwIDAQABo1AwTjAdBgNVHQ4EFgQUApxlUQg26GrG3eH8lEG3SkqbH/swHwYDVR0jBBgwFoAUApxlUQg26GrG3eH8lEG3SkqbH/swDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANhbm8WgIqBDlF7DIRVUbq04TEA9nOJG8wdjJYdoKrPX9f/E9slkFuD2StcK99RTcowa8Z2OmW7tksa+onyH611Lq21QXh4aHzQUAm2HbsmPQRZnkByeYoCJ/1tuEho+x+VGanaUICSBVWYiebAQVKHR6miFypRElibNBizm2nqp6Q9B87V8COzyDVngR1DlWDduxYaNOBgvht3Rk9Y2pVHqym42dIfN+pprcsB1PGBkY/BngIuS/aqTENbmoC737vcb06e8uzBsbCpHtqUBjPpL2psQZVJ2Y84JmHafC3B7nFQrjdZBbc9eMHfPo240Rh+pDLwxdxPqRAZdeLaUkCQ==',

    // limit which Sps can use this IdP
    'SPList' => ['http://ssp-sp1.local', 'http://ssp-sp2.local'],
];

/*
 * IdP 3
 */
$metadata['http://ssp-idp3.local:8087'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp3.local:8087',
    'name' => [
        'en' => 'IDP 3:8087',
    ],
    'IDPNamespace' => 'IDP-3-custom-port',
    'logoCaption' => 'IDP-3:8087 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+3+8087',

    'description' => 'Local IDP3 for testing SSP Hub (custom port)',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp3.local:8087/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp3.local:8087/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJALBaUrvz1X5DMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE4MTQwMDUxWhcNMjYxMDE4MTQwMDUxWjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx5mZNwjEnakJho+5etuFyx+2g9rs96iLX/LDC24aBAsdNxTNuIc1jJ7pxBxGrepEND4LkietLNBlOr1q50nq2+ddTrCfmoJB+9BqBOxcm9qWeqWbp8/arUjaxPzK3DfZrxJxIVFjzqFF7gI91y9yvEW/fqLRMhvnH1ns+N1ne59zr1y6h9mmHfBffGr1YXAfyEAuV1ich4AfTfjqhdwFwxhFLLCVnxA0bDbNw/0eGCSiA13N7a013xTurLeJu0AQaZYssMqvc/17UphH4gWDMEZAwy0EfRSBOsDOYCxeNxVajnWX1834VDpBDfpnZj996Gh8tzRQxQgT9/plHKhGiwIDAQABo1AwTjAdBgNVHQ4EFgQUApxlUQg26GrG3eH8lEG3SkqbH/swHwYDVR0jBBgwFoAUApxlUQg26GrG3eH8lEG3SkqbH/swDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANhbm8WgIqBDlF7DIRVUbq04TEA9nOJG8wdjJYdoKrPX9f/E9slkFuD2StcK99RTcowa8Z2OmW7tksa+onyH611Lq21QXh4aHzQUAm2HbsmPQRZnkByeYoCJ/1tuEho+x+VGanaUICSBVWYiebAQVKHR6miFypRElibNBizm2nqp6Q9B87V8COzyDVngR1DlWDduxYaNOBgvht3Rk9Y2pVHqym42dIfN+pprcsB1PGBkY/BngIuS/aqTENbmoC737vcb06e8uzBsbCpHtqUBjPpL2psQZVJ2Y84JmHafC3B7nFQrjdZBbc9eMHfPo240Rh+pDLwxdxPqRAZdeLaUkCQ==',
];
$metadata['http://ssp-idp3.local'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp3.local',
    'name' => [
        'en' => 'IDP 3',
    ],
    'IDPNamespace' => 'IDP-3',
    'logoCaption' => 'IDP-3 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+3',

    'description' => 'Local IDP3 for testing SSP Hub',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp3.local/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp3.local/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIDzzCCAregAwIBAgIJALBaUrvz1X5DMA0GCSqGSIb3DQEBCwUAMH4xCzAJBgNVBAYTAlVTMQswCQYDVQQIDAJOQzEPMA0GA1UEBwwGV2F4aGF3MQwwCgYDVQQKDANTSUwxDTALBgNVBAsMBEdUSVMxDjAMBgNVBAMMBVN0ZXZlMSQwIgYJKoZIhvcNAQkBFhVzdGV2ZV9iYWd3ZWxsQHNpbC5vcmcwHhcNMTYxMDE4MTQwMDUxWhcNMjYxMDE4MTQwMDUxWjB+MQswCQYDVQQGEwJVUzELMAkGA1UECAwCTkMxDzANBgNVBAcMBldheGhhdzEMMAoGA1UECgwDU0lMMQ0wCwYDVQQLDARHVElTMQ4wDAYDVQQDDAVTdGV2ZTEkMCIGCSqGSIb3DQEJARYVc3RldmVfYmFnd2VsbEBzaWwub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAx5mZNwjEnakJho+5etuFyx+2g9rs96iLX/LDC24aBAsdNxTNuIc1jJ7pxBxGrepEND4LkietLNBlOr1q50nq2+ddTrCfmoJB+9BqBOxcm9qWeqWbp8/arUjaxPzK3DfZrxJxIVFjzqFF7gI91y9yvEW/fqLRMhvnH1ns+N1ne59zr1y6h9mmHfBffGr1YXAfyEAuV1ich4AfTfjqhdwFwxhFLLCVnxA0bDbNw/0eGCSiA13N7a013xTurLeJu0AQaZYssMqvc/17UphH4gWDMEZAwy0EfRSBOsDOYCxeNxVajnWX1834VDpBDfpnZj996Gh8tzRQxQgT9/plHKhGiwIDAQABo1AwTjAdBgNVHQ4EFgQUApxlUQg26GrG3eH8lEG3SkqbH/swHwYDVR0jBBgwFoAUApxlUQg26GrG3eH8lEG3SkqbH/swDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEANhbm8WgIqBDlF7DIRVUbq04TEA9nOJG8wdjJYdoKrPX9f/E9slkFuD2StcK99RTcowa8Z2OmW7tksa+onyH611Lq21QXh4aHzQUAm2HbsmPQRZnkByeYoCJ/1tuEho+x+VGanaUICSBVWYiebAQVKHR6miFypRElibNBizm2nqp6Q9B87V8COzyDVngR1DlWDduxYaNOBgvht3Rk9Y2pVHqym42dIfN+pprcsB1PGBkY/BngIuS/aqTENbmoC737vcb06e8uzBsbCpHtqUBjPpL2psQZVJ2Y84JmHafC3B7nFQrjdZBbc9eMHfPo240Rh+pDLwxdxPqRAZdeLaUkCQ==',
];

/*
 * IdP 4
 */
$metadata['http://ssp-idp4.local:8088'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp4.local:8088',
    'name' => [
        'en' => 'IDP 4:8088',
    ],
    'IDPNamespace' => 'IDP-4-custom-port',
    'logoCaption' => 'IDP-4:8088 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+4+8088',

    'description' => 'Local IDP4 for testing SSP Hub (custom port)',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp4.local:8088/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp4.local:8088/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIEsTCCAxmgAwIBAgIUNoOjrtDw3E5xDwt0R28YzAXXY0AwDQYJKoZIhvcNAQELBQAwaDELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAlRYMQ8wDQYDVQQHDAZEYWxsYXMxEzARBgNVBAoMClNJTCBHbG9iYWwxDTALBgNVBAsMBEdUSVMxFzAVBgNVBAMMDnNzcC1pZHA0LmxvY2FsMB4XDTI1MDMwNDE1MjgyOFoXDTM1MDMwNDE1MjgyOFowaDELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAlRYMQ8wDQYDVQQHDAZEYWxsYXMxEzARBgNVBAoMClNJTCBHbG9iYWwxDTALBgNVBAsMBEdUSVMxFzAVBgNVBAMMDnNzcC1pZHA0LmxvY2FsMIIBojANBgkqhkiG9w0BAQEFAAOCAY8AMIIBigKCAYEA5lHSWUoWj4XDM+hhduOm7Z4Ud5uIlFEqhvRmGj6+06g8kfoIjk6vxPvv7SphHY8gOjK3+FgGjqnrMyLQcc1533bpCFCstr3ERW1GB/1i2X47H6X8bM0kP7+MurSdPuetPjVvHVCKmiRk9Uj6ILgIWGFfn2jqtqBAGKzvGdBwgTQyMM45+1IQwc+vF99T8ywt/RbKrXePgEFPYW1qPwE6fVIbsOZPZdnm3hwXfXVVwXM8R4jtUGpFjjV2nRkszPWfDZb0njOK0cJLjj/zAN/535zrbteYNXivyFgoFGKox7i5DkupeGPW/aGN6D+zr2coXRGv3qNWT4IEnL2N3YXiEsz/NkOfkoK/kF8awHMfisizNLzGm80LZV+CwuSjjbsoFWUYomTNq0WSIYTZCJJmmqBxb3Gqjisrux9p18zgG9CYoDhaNzvTjV+ttqecnRHkt73tAWw6nNOk0JCOvRbrSehYVF0RzkolLcbYWJqcGd1EwCfgCjHjgKC8oUQUpDK3AgMBAAGjUzBRMB0GA1UdDgQWBBS1RQrkWblnpdxVGPIZoVb6TIulFzAfBgNVHSMEGDAWgBS1RQrkWblnpdxVGPIZoVb6TIulFzAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBgQAmiGB34lM2KLpju8Gzce9eNDCUZHEufOfwYNicOELoJG5ElVMQMX3dqZe9VhIlgNen0Dn6YybnOy3Pi3ywaSXsOe7n3OTASvLLpwHxxIyKWrwOPER2zgbqlRitWX11ZumqxRXobo1rzVylS+8viRT1dwY6gB0lqk+oHTqk6GLnnG7hhRXTOjuQ6/dgGvJMjFIEbvywWogvWAC/u3bnIZ+bGFACD+A9hxZE/CCroLJxlbKEOj+/aiJqSn/kbhkmhyAd5uY/eVRyLe4eYeeVy2sEqE7bOjw8ptbqsULZZTk5M9nn8XBhTNLAOx6lre21zFFbfrWZm7hZHWOVac5PoqFRbDVzgzNNlC9Zh3HE63J2YfJMAgjGaeee/BtU0J5oW341oX9w9r+UQNXaKN1DxNIu31dVS6edA4xu3cNVg+gbfvGsf65Z6391rsTmQYpg3CloNuHrq3ytMq4xYKMVjm8AP0Iy6Ef+XdQgEhgaIg8psAnQp6j3Nww6j+9UCkyJHLM=',
];
$metadata['http://ssp-idp4.local'] = [
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'http://ssp-idp4.local',
    'name' => [
        'en' => 'IDP 4',
    ],
    'IDPNamespace' => 'IDP-4',
    'logoCaption' => 'IDP-4 staff',
    'logoURL' => 'https://dummyimage.com/125x125/0f4fbd/ffffff.png&text=IDP+4',

    'description' => 'Local IDP4 for testing SSP Hub',

    'SingleSignOnService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp4.local/saml2/idp/SSOService.php',
        ],
    ],
    'SingleLogoutService' => [
        [
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => 'http://ssp-idp4.local/saml2/idp/SingleLogoutService.php',
        ],
    ],
    'certData' => 'MIIEsTCCAxmgAwIBAgIUNoOjrtDw3E5xDwt0R28YzAXXY0AwDQYJKoZIhvcNAQELBQAwaDELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAlRYMQ8wDQYDVQQHDAZEYWxsYXMxEzARBgNVBAoMClNJTCBHbG9iYWwxDTALBgNVBAsMBEdUSVMxFzAVBgNVBAMMDnNzcC1pZHA0LmxvY2FsMB4XDTI1MDMwNDE1MjgyOFoXDTM1MDMwNDE1MjgyOFowaDELMAkGA1UEBhMCVVMxCzAJBgNVBAgMAlRYMQ8wDQYDVQQHDAZEYWxsYXMxEzARBgNVBAoMClNJTCBHbG9iYWwxDTALBgNVBAsMBEdUSVMxFzAVBgNVBAMMDnNzcC1pZHA0LmxvY2FsMIIBojANBgkqhkiG9w0BAQEFAAOCAY8AMIIBigKCAYEA5lHSWUoWj4XDM+hhduOm7Z4Ud5uIlFEqhvRmGj6+06g8kfoIjk6vxPvv7SphHY8gOjK3+FgGjqnrMyLQcc1533bpCFCstr3ERW1GB/1i2X47H6X8bM0kP7+MurSdPuetPjVvHVCKmiRk9Uj6ILgIWGFfn2jqtqBAGKzvGdBwgTQyMM45+1IQwc+vF99T8ywt/RbKrXePgEFPYW1qPwE6fVIbsOZPZdnm3hwXfXVVwXM8R4jtUGpFjjV2nRkszPWfDZb0njOK0cJLjj/zAN/535zrbteYNXivyFgoFGKox7i5DkupeGPW/aGN6D+zr2coXRGv3qNWT4IEnL2N3YXiEsz/NkOfkoK/kF8awHMfisizNLzGm80LZV+CwuSjjbsoFWUYomTNq0WSIYTZCJJmmqBxb3Gqjisrux9p18zgG9CYoDhaNzvTjV+ttqecnRHkt73tAWw6nNOk0JCOvRbrSehYVF0RzkolLcbYWJqcGd1EwCfgCjHjgKC8oUQUpDK3AgMBAAGjUzBRMB0GA1UdDgQWBBS1RQrkWblnpdxVGPIZoVb6TIulFzAfBgNVHSMEGDAWgBS1RQrkWblnpdxVGPIZoVb6TIulFzAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBgQAmiGB34lM2KLpju8Gzce9eNDCUZHEufOfwYNicOELoJG5ElVMQMX3dqZe9VhIlgNen0Dn6YybnOy3Pi3ywaSXsOe7n3OTASvLLpwHxxIyKWrwOPER2zgbqlRitWX11ZumqxRXobo1rzVylS+8viRT1dwY6gB0lqk+oHTqk6GLnnG7hhRXTOjuQ6/dgGvJMjFIEbvywWogvWAC/u3bnIZ+bGFACD+A9hxZE/CCroLJxlbKEOj+/aiJqSn/kbhkmhyAd5uY/eVRyLe4eYeeVy2sEqE7bOjw8ptbqsULZZTk5M9nn8XBhTNLAOx6lre21zFFbfrWZm7hZHWOVac5PoqFRbDVzgzNNlC9Zh3HE63J2YfJMAgjGaeee/BtU0J5oW341oX9w9r+UQNXaKN1DxNIu31dVS6edA4xu3cNVg+gbfvGsf65Z6391rsTmQYpg3CloNuHrq3ytMq4xYKMVjm8AP0Iy6Ef+XdQgEhgaIg8psAnQp6j3Nww6j+9UCkyJHLM=',
];
