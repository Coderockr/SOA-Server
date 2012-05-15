<?php
namespace test;

use model;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;
use DMS\Filter\Rules as Filter;


/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 */
class User extends model\Entity
{

    /**
     * @ORM\Column(type="string", length=50)
     *
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @var string
     */
    private $login;
    
    /**
     * @ORM\Column(type="string", length=150)
     *
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @var string
     */
    private $password;

    public function getLogin()
    {
        return $this->login;
    }
    
    public function setLogin($login)
    {
        return $this->login = $login;
    }

    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        return $this->password = md5($password);
    }
}

class EntityTest extends ApiTest
{

    public function __construct() 
    {
        parent::__construct();
        $this->curl_url = $this->url . '/ad';
    }
    
    public function tearDown(){
        $query = $this->em->createQuery('delete from model\AdMedia');
        $query->execute();
        $query = $this->em->createQuery('delete from model\Ad');
        $query->execute();
        parent::tearDown();
        
    }

    public function testNoData()
    {
        $result = $this->get($this->curl_url);
        $this->assertEquals($result['code'], 404);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
    }

    public function testPost()
    {
        
        $fields = $this->buildData();
        $result = $this->post($this->curl_url, $fields);
        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
       
    }


    public function testInvalidPost()
    {
       $fields = array(
            'ad[name]'=>''
        );

        $result = $this->post($this->curl_url, $fields);
        $this->assertEquals($result['code'], 400);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
    }

    public function testGet()
    {
        $fields = $this->buildData();
        $client = $this->post($this->curl_url, $fields);
        
        $result = $this->get($this->curl_url . '/' . $client['response']->id);
        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
        $this->assertEquals(count($result['response']), 1);
        $this->assertEquals($result['response']->name, 'Jack Daniels');
    }

    public function testPostRelationships()
    {
        $fields = $this->buildData();
        $client = $this->post($this->curl_url, $fields);
        $adId = $client['response']->id;

        $result = $this->get($this->curl_url . '/' . $adId);
        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
        $this->assertEquals(count($result['response']->media_collection), 0);
        $this->assertEquals($result['response']->name, 'Jack Daniels');

        //media
        $this->buildMedia($adId, model\MediaType::IMAGE);
        $this->buildMedia($adId, model\MediaType::VIDEO);

        //get original ad
        $result = $this->get($this->curl_url . '/' . $adId);
        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
        $this->assertEquals(count($result['response']->media_collection), 2);
    }    

    public function testPut()
    {
        $fields = $this->buildData();

        //create
        $result = $this->post($this->curl_url, $fields);
        
        //update
        $fields = array(
            'ad[name]'  => 'Jim Beam',
            'ad[space]' => model\AdSpace::MAINSCREEN
        );
        
        $result = $this->put($this->curl_url . '/' . $result['response']->id, $fields);

        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");
        $this->assertEquals($result['response']->name, 'Jim Beam');
    }    

    public function testDelete()
    {
        $fields = $this->buildData();

        //create
        $result = $this->post($this->curl_url, $fields);
        $url = $this->curl_url . '/' . $result['response']->id;
        $result = $this->delete($url);
        
        $this->assertEquals($result['code'], 200);
        $this->assertEquals($result['type'], "text/json; charset=UTF-8");

        $result = $this->get($url);
        $this->assertEquals($result['code'], 404);
    } 

    private function buildData()
    {

        $fields = array(
            'ad[name]' =>'Jack Daniels',
            'ad[banner_small]' =>'banner_small.png',
            'ad[banner_large]' =>'banner_large.png',
            'ad[banner_full]' =>'banner_full.png',
            'ad[space]' => model\AdSpace::STANDBY,
            'ad[text]' => 'Campanha do Jack Daniels',
            'ad[date_start]'=> date('Y-m-d'),
            'ad[date_end]'=> date('Y-m-d'),
        );
    
        return $fields;
    }

    private function buildMedia($ad, $type)
    {
        
        $fields = array(
            'adMedia[description]'  => 'Media ' .$type ,
            'adMedia[file]'         => 'file',
            'adMedia[type]'         => $type,
            'adMedia[ad]' => array (
                'id'  => $ad
            )
        );
        $result = $this->post($this->url . '/adMedia', $fields);
        
        return $result['response']->id;

    }
}