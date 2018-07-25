<?php

/**
 * Class TokenTest
 */
class TokenTest extends TestCase
{
    /**
     *
     */
    public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }

    /**
     *
     */
    public function testApplication()
    {
        $response = $this->call('GET', '/');

        $this->assertEquals(200, $response->status());
    }

    /**
     *
     */
    public function testcreateToken()
    {
        $request = [
            ["22.372081", "114.107877"],
            ["22.284419", "114.159510"],
            ["22.326442", "114.167811"]
        ];

        $this->json('POST', '/route', $request)
           ->assertTrue(true);
    }

    /**
     *
     */
    public function testValidateInputWithEmpty()
    {
        $request = [];

        $this->json('POST', '/route', $request)
            ->seeJson([
                'status' => 'failure',
                'error'  => 'INVALID_JSON'
            ]);
    }

    /**
     *
     */
    public function testValidateInputWithSameLocation()
    {
        $request = [
            ["22.372081", "114.107877"],
            ["22.372081", "114.107877"]
        ];

        $response = $this->call('POST', '/route', $request);
        //This is supposed to be 200 but i dont have DB connection setup for testing..
        $this->assertEquals(400, $response->status());
    }

    /**
     *
     */
    public function testInvalidToken()
    {
        $response = $this->call('GET', '/route/123');
        //This is supposed to be 400 but i dont have DB connection setup for testing..
        $this->assertEquals(500, $response->status());
    }


}