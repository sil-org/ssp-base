<?php


use PHPUnit\Framework\TestCase;
use SAML2\XML\saml\NameID;
use SimpleSAML\Configuration;
use SimpleSAML\Module\sildisco\Auth\Process\AddIdp2NameId;

class AddIdpTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Configuration::setConfigDir(__DIR__ . '/fixtures/config/');
    }

    private static function getNameID($idp)
    {
        return [
            'saml:sp:IdP' => $idp,
            'saml:sp:NameID' => [
                [
                    'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                    'Value' => 'Tester1_Smith',
                    'SPNameQualifier' => 'https://ssp-sp1.local',
                ],
            ],
            'Attributes' => [],
        ];
    }

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config The filter configuration.
     * @param array $request The request state.
     * @return array  The state array after processing.
     */
    private static function processAddIdp2NameId(array $config, array $request)
    {
        $filter = new AddIdp2NameId($config, NULL);
        $filter->process($request);
        return $request;
    }

    /*
     * Test with IdP metadata not having an IDPNamespace entry
     * @expectedException \SimpleSAML\Error\Exception
     */
    public function testAddIdp2NameId_NoIDPNamespace()
    {
        $this->expectException('\SimpleSAML\Error\Exception');
        $config = ['test' => ['value1', 'value2'],];
        $request = self::getNameID('idp-bare');

        self::processAddIdp2NameId($config, $request);
    }


    /*
     * Test with IdP metadata not having an IDPNamespace entry
     * @expectedException \SimpleSAML\Error\Exception
     */
    public function testAddIdp2NameId_EmptyIDPNamespace()
    {
        $this->expectException('\SimpleSAML\Error\Exception');
        $config = ['test' => ['value1', 'value2'],];
        $request = self::getNameID('idp-empty');
        self::processAddIdp2NameId($config, $request);
    }

    /*
     * Test with IdP metadata not having an IDPNamespace entry
     * @expectedException \SimpleSAML\Error\Exception
     */
    public function testAddIdp2NameId_BadIDPNamespace()
    {
        $this->expectException('\SimpleSAML\Error\Exception');
        $config = [
            'test' => ['value1', 'value2'],
        ];
        $request = self::getNameID('idp-bad');
        self::processAddIdp2NameId($config, $request);
    }


    /*
     * Test with IdP metadata having a good IDPNamespace entry
     */
    public function testAddIdp2NameId_GoodString()
    {
        $nameID = new NameID();
        $nameID->setValue('Tester1_SmithA');
        $config = ['test' => ['value1', 'value2']];
        $state = [
            'saml:sp:IdP' => 'idp-good',
            'saml:sp:NameID' => $nameID,
            'Attributes' => [],
        ];

        $newNameID = new NameID();
        $newNameID->setValue('Tester1_SmithA@idpGood');

        $expected = $state;
        $expected['saml:NameID']['urn:oasis:names:tc:SAML:2.0:nameid-format:persistent'] = $newNameID;

        $results = self::processAddIdp2NameId($config, $state);
        $this->assertEquals($expected, $results);
    }

    /*
     * Test with IdP metadata having a good IDPNamespace entry
     */
    public function testAddIdp2NameId_GoodArray()
    {
        $nameID = new NameID();
        $nameID->setValue('Tester1_SmithA');
        $nameID->setFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent');
        $nameID->setSPNameQualifier('https://ssp-sp1.local');

        $config = ['test' => ['value1', 'value2']];
        $state = [
            'saml:sp:IdP' => 'idp-good',
            'saml:sp:NameID' => $nameID,
            'Attributes' => [],
        ];

        $newNameID = $state['saml:sp:NameID'];
        $newNameID->setValue('Tester1_SmithA@idpGood');

        $expected = $state;
        $expected['saml:NameID']['urn:oasis:names:tc:SAML:2.0:nameid-format:persistent'] = $newNameID;

        $results = self::processAddIdp2NameId($config, $state);

        $this->assertEquals($expected, $results);
    }

}
