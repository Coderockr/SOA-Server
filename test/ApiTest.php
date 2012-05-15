<?php
namespace test;

abstract class ApiTest extends \PHPUnit_Framework_TestCase
{

    protected $token = '85e4a615f62c711d3aac0e7def5b4903';

    protected $url;

    protected $curl_url;

    protected $em;

    public function __construct() {
        include 'configs/configs.testing.php';
        $this->url = $soaUrl;
        include './bootstrap.php';
        $this->em = $em;
        parent::__construct();
    }

    function initCurl($url) {
        // initialize curl
        $ch = curl_init();
        // set curl to return the response back to us after curl_exec
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->token
        ));
        return $ch;
    }


    protected function get($url) 
    {
        $ch = $this->initCurl($url);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($this->isJson($response))
            $response = json_decode($response);

        return array(
            'code' =>   $code,
            'type' =>   $type, 
            'response' => $response
            );

    }
    
    protected function post($url, $data = array()) 
    {
        $ch = $this->initCurl($url);
        curl_setopt($ch,CURLOPT_POST,count($data));
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($this->isJson($response))
            $response = json_decode($response);

        return array(
            'code' =>   $code,
            'type' =>   $type, 
            'response' => $response
            );
    }    

    protected function put($url, $data) 
    {
        $ch = $this->initCurl($url);
        curl_setopt($ch,CURLOPT_POST,count($data));
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        if ($this->isJson($response))
            $response = json_decode($response);

        return array(
            'code' =>   $code,
            'type' =>   $type, 
            'response' => $response
            );
    }    

    protected function delete($url) 
    {
        $ch = $this->initCurl($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($this->isJson($response))
            $response = json_decode($response);

        return array(
            'code' =>   $code,
            'type' =>   $type, 
            'response' => $response
            );
    } 

    protected function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}