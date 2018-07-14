<?php

class TokenTest extends TestCase
{
    public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }

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

    public function testGetToken()
    {
        $testToken = '123';
        $this->json('GET', '/route/'.$testToken, [])
            ->seeJson();
    }
}