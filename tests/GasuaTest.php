<?php

class GasuaTest extends PHPUnit_Framework_TestCase
{

    /**
     * Just check if the GasuaClient has no syntax error
     */
    public function testIsThereAnySyntaxError()
    {
        $client = new Livich\Gasua\GasuaClient();
        $this->assertTrue(is_object($client));
        unset($client);
    }

    public function testUnsuccessfulLogin()
    {
        $client = new Livich\Gasua\GasuaClient();
        $this->expectException(\Livich\Gasua\GasuaLoginException::class);
        $client->login('invalid@invalid.invalid', 'invalid');
    }

    public function testSuccessfulLogin()
    {
        $client = new Livich\Gasua\GasuaClient();
        $result = $client->login(getenv("login"), getenv('password'));
        $this->assertEquals($client, $result);
    }

    public function testAccountInfo()
    {
        $client = new Livich\Gasua\GasuaClient();
        $result = $client->login(getenv("login"), getenv('password'));
        $this->assertEquals($client, $result);
        $accountInfo = $result->getAccountInfo();
        $this->assertArrayHasKey('balance_date', $accountInfo);
    }

}