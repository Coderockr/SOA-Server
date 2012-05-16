<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\MonologServiceProvider;

use service as service;

include 'bootstrap.php';


$app = new Silex\Application();

if(!getenv('APPLICATION_ENV'))
    $env = 'testing';
else
    $env = getenv('APPLICATION_ENV');

//app configuration
$app->register(new DoctrineServiceProvider(), array(
    'db.options'  => $dbOptions,
    'db.dbal.class_path' => 'vendor/doctrine-dbal/lib',
    'db.common.class_path' => 'vendor/doctrine-common/lib',
));


$app->register(new Silex\Provider\ValidatorServiceProvider(), array(
    'validator.class_path'    => __DIR__.'/vendor',
));

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile'       => getenv('APPLICATION_PATH').'/logs/soa.log',
    'monolog.class_path'    => __DIR__.'/vendor/monolog/src',
));


//Rest functions
$app->get('/{entity}/{id}', function ($entity, $id) use ($em, $app) 
{
    try {
        $data = $em->find('model\\'.ucfirst($entity), $id);
    }
    catch(Exception $e) {
        $app['monolog']->addError($e->getMessage());
        return new Response($e->getMessage(), 500, array('Content-Type' => 'text/json'));
    }

    if($data === null) {
       return new Response('Data not found', 404, array('Content-Type' => 'text/json'));
    }
   
    $serializer = new service\Serializer();
    $serializer->setGroups(array('entity', $entity));
    return new Response($serializer->serialize($data, 'json'), 200,  array('Content-Type' => 'text/json'));
})->assert('id', '\d+');

$app->get('/{entity}/{filter}', function ($entity, $filter) use ($app, $em) 
{
    try {
        $serializer = new service\Serializer();    
        $serializer->setGroups(array('entity', $entity));
        $entityName = 'model\\' .ucfirst($entity);

        $qb = $em->createQueryBuilder();

        $qb->select('e')
            ->from($entityName, 'e');            
        
        if ($filter !== null) {
            parse_str($filter, $filter);
            foreach ($filter as $key => $value) {
                $qb->andWhere($qb->expr()->eq("e.$key", ":$key"));
                $qb->setParameter($key, $value);
            }
        }

        $entities = $qb->getQuery()->getResult();
        $data = array();
        foreach ($entities as $e) {
            $data[] = $e;
        }
        if (count($data) > 0)
            $data = $serializer->serialize($data, 'json');
    }
    catch(Exception $e) {
        $app['monolog']->addError($e->getMessage());
        return new Response($e->getMessage(), 500, array('Content-Type' => 'text/json'));
    }
    if(count($data) == 0) {
        return new Response('Data not found', 404, array('Content-Type' => 'text/json'));
    }
    return new Response($data, 200, array('Content-Type' => 'text/json'));

})->value('filter', null);

$app->post('/{entity}', function ($entity, Request $request) use ($app, $em, $filter) 
{
    $serializer = new service\Serializer();
    $serializer->setGroups(array('entity', $entity));

    // Get POST data or 400 HTTP response
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400, array('Content-Type' => 'text/json'));
    }

    // Persist data to the database
    try {
        $entityName = 'model\\' .ucfirst($entity);
        $entity = new $entityName; 
        $class = new \ReflectionClass($entity);
        
        foreach ($data as $name => $value) {
            if (is_array($value)) { //it's a relationship to another entity
                $id = $value['id'];
                $relatedEntity = 'model\\' .ucfirst($name);
                if (isset($value['entityName'])) 
                    $relatedEntity = 'model\\' .ucfirst($value['entityName']);
                
                $value = $em->find($relatedEntity, $id);
            }
            $method = 'set'. ucfirst($name);
            if ($class->hasMethod($method)) {
               call_user_func(array($entity, $method), $value); 
            }
        }
        //valid entity
        $errors = $app['validator']->validate($entity);
        if (count($errors) > 0) {
            foreach($errors as $r)
                $app['monolog']->addError($r);
            return new Response('Invalid parameters.', 400, array('Content-Type' => 'text/json'));
        }

        //Filter entity
        $filter->filterEntity($entity);

        $em->persist($entity);
        $em->flush();
    } catch(Exception $e) {
        $app['monolog']->addError($e->getMessage());
        return new Response($e->getMessage(), 500, array('Content-Type' => 'text/json'));
    }
    
    return new Response($serializer->serialize($entity, 'json'), 200,  array('Content-Type' => 'text/json'));
});

$app->put('/{entity}/{id}', function ($entity, $id, Request $request) use ($app, $em, $filter) 
{
    $serializer = new service\Serializer();
    $serializer->setGroups(array('entity', $entity));
    $entityName = 'model\\' .ucfirst($entity);
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400, array('Content-Type' => 'text/json'));
    }

    if (!$entity = $em->find($entityName, $id)) {
        return new Response('Not found.', 404, array('Content-Type' => 'text/json'));
    }

    try {
        $class = new \ReflectionClass($entity);
        foreach ($data as $name => $value) {
            if (is_array($value)) { //it's a relationship to another entity
                $id = $value['id'];
                $relatedEntity = 'model\\' .ucfirst($name);
                if (isset($value['entityName']))
                    $relatedEntity = 'model\\' .ucfirst($value['entityName']);
                
                $value = $em->find($relatedEntity, $id);
            }
            $method = 'set'. ucfirst($name);
            if ($class->hasMethod($method)) {
               call_user_func(array($entity, $method), $value); 
            }
        }
        $entity->setUpdated(date('Y-m-d H:i:s'));
        $errors = $app['validator']->validate($entity);
        if (count($errors) > 0) {
            foreach($errors as $r)
                $app['monolog']->addError($r);
            return new Response('Invalid parameters.', 400, array('Content-Type' => 'text/json'));
        }

        //Filter entity
        $filter->filterEntity($entity);
        
        //$em->persist($entity);
        $em->flush();
    } catch(Exception $e) {
        $app['monolog']->addError($e->getMessage());
        return new Response($e->getMessage(), 500, array('Content-Type' => 'text/json'));
    }
    return new Response($serializer->serialize($entity, 'json'), 200,  array('Content-Type' => 'text/json'));
});

$app->delete('/{entity}/{id}', function ($entity, $id) use ($app, $em) 
{
    if (!$entity = $em->find('model\\'.ucfirst($entity), $id)) {
        return new Response('Data not found.', 404, array('Content-Type' => 'text/json'));
    }
    $em->remove($entity);
    $em->flush();
    
    return new Response('Data deleted.', 200, array('Content-Type' => 'text/json'));
});

//rpc
$app->post('/rpc/{service}', function ($service, Request $request) use ($app, $em)
{
    $service = "service\\". ucfirst($service);

    if (!class_exists($service)) {
        return new Response('Invalid service.', 400, array('Content-Type' => 'text/json'));
    }
    $class = new $service($em);
    if (!$parameters = $request->get('parameters')) 
        $parameters = array();

    $result = $class->execute($parameters);

    switch ($result['status']) {
        case 'success':
            return new Response($result['data'], 200, array('Content-Type' => 'text/json'));
            break;
        case 'error':
            return new Response('Error executing service - ' . $result['data'], 400, array('Content-Type' => 'text/json'));
            break;
    }

});

//options - used in cross domain access
$app->match('{entity}/{id}', function ($entity, $id, Request $request) use ($app) 
{
    return new Response('', 200, array(
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
        'Access-Control-Allow-Headers' => 'Authorization'
    ));
})->method('OPTIONS')->value('id', null);

$app->before(function (Request $request) use ($app) {
    if ($request->getMethod() == 'OPTIONS') {
        return;
    }

    if( ! $request->headers->has('authorization')){
        return new Response('Unauthorized', 401);
    }

    require_once getenv('APPLICATION_PATH').'/configs/clients.php';
    if (!in_array($request->headers->get('authorization'), array_keys($clients))) {
        return new Response('Unauthorized', 401);
    }
});

$app->after(function (Request $request, Response $response) {
    $response->headers->set('Content-Type', 'text/json');
});



$app->run();
