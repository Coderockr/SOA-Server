<?php
namespace test;

use model;

class AuthenticateTest extends ApiTest
{

    public function __construct() 
    {
        parent::__construct();
        $this->curl_url = $this->url . '/rpc/authenticate';
    }

    public function tearDown(){
        $query = $this->em->createQuery('delete from model\User');
        $query->execute();
        parent::tearDown();
    }

    public function testExecute()
    {
        $user   = $this->buildUser();
        
        $params = array(
            'parameters[login]'     => 'invalid user',
            'parameters[password]'  => 'invalid password'
        );
        
        $result = $this->post($this->curl_url, $params);
        $this->assertEquals($result['code'], 400);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
        $this->assertEquals($result['response'], "Error executing service - User not found");

        $params = array(
            'parameters[login]'     => 'gow',
            'parameters[password]'  => 'invalid password'
        );
        
        $result = $this->post($this->curl_url, $params);
        $this->assertEquals($result['code'], 400);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
        $this->assertEquals($result['response'], "Error executing service - Invalid password");

        $params = array(
            'parameters[login]'     => 'gow',
            'parameters[password]'  => 'SantaMonica'
        );
        
        $result = $this->post($this->curl_url, $params);
        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");


    }

    private function buildUser()
    {
        $fields = array(
            'user[name]'=> 'Kratos',
            'user[login]' => 'gow',
            'user[password]'=> 'SantaMonica',
            'user[email]'=> 'kratos@ps3.com',
            'user[type]'=> model\UserType::ADMIN,
        );
        $result = $this->post($this->url . '/user', $fields);

        return $result['response'];

    }     

}