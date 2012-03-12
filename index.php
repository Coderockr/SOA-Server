<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

use model\User;
use procedure as procedure;

require_once 'vendor/silex.phar';

require_once __DIR__.'/vendor/Symfony/Component/Classloader/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                    => __DIR__.'/vendor',
    'Doctrine\\Common'           => __DIR__.'/vendor/doctrine/common/lib',
    'Doctrine\\DBAL\\Migrations' => __DIR__.'/vendor/doctrine/dbal/lib',
    'Doctrine\\DBAL'             => __DIR__.'/vendor/doctrine/dbal/lib',
    'Doctrine'                   => __DIR__.'/vendor/doctrine/orm/lib',
    'DMS'                        => __DIR__.'/vendor',
    'model'                      => __DIR__,
    'procedure'                  => __DIR__
));

$loader->register();


$app = new Silex\Application();
require_once __DIR__.'/configs/configs.php';
require_once __DIR__.'/library/doctrine.php';
require_once __DIR__.'/library/filter.php';

//app configuration
$app->register(new DoctrineServiceProvider(), array(
    'db.options'  => $dbOptions,
    'db.dbal.class_path' => 'vendor/doctrine-dbal/lib',
    'db.common.class_path' => 'vendor/doctrine-common/lib',
));


$app->register(new Silex\Provider\ValidatorServiceProvider(), array(
    'validator.class_path'    => __DIR__.'/vendor',
));

//Rest functions
$app->get('/{entity}', function ($entity) use ($app) 
{
    $query = "SELECT * FROM $entity";
    $data = $app['db']->fetchAll($query);
    if(count($data) == 0) {
        $app->abort(404, 'Data not found');
    }
    return new Response(json_encode($data));

});

$app->get('/{entity}/{id}', function ($entity, $id) use ($em) 
{
    $data = $em->find('model\\'.ucfirst($entity), $id);
    
    if($data === null) {
       $app->abort(404, 'Data not found');
    }

    return new Response($data->toJson());
});

$app->post('/{entity}', function ($entity, Request $request) use ($app, $em, $filter) 
{
    // Get POST data or 400 HTTP response
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400, array('Content-Type' => 'text/json'));
    }

    // Persist data to the database
    $entityName = 'model\\' .ucfirst($entity);
    $entity = new $entityName;
    $entity->set($data);

    //valid entity
    if (count($app['validator']->validate($entity)) > 0) {
        return new Response('Invalid parameters.', 400, array('Content-Type' => 'text/json'));
    }

    //Filter entity
    $filter->filterEntity($entity);


    $em->persist($entity);
    $em->flush();

    return new Response($entity->toJson());
});

$app->put('/{entity}/{id}', function ($entity, $id, Request $request) use ($app, $em, $filter) 
{
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400, array('Content-Type' => 'text/json'));
    }

    if (!$entity = $em->find('model\\'.ucfirst($entity), $id)) {
        return new Response('Not found.', 404, array('Content-Type' => 'text/json'));
    }

    $entity->set($data);

    if (count($app['validator']->validate($entity)) > 0) {
        return new Response('Invalid parameters.', 400, array('Content-Type' => 'text/json'));
    }

    //Filter entity
    $filter->filterEntity($entity);

    $em->persist($entity);
    $em->flush();

    return new Response($entity->toJson(), 200);
});

$app->delete('/{entity}/{id}', function ($entity, $id) use ($app, $em) 
{
    if (!$entity = $em->find('model\\'.ucfirst($entity), $id)) {
        return new Response('Data not found.', 404, array('Content-Type' => 'text/json'));
    }
    $em->remove($entity);
    $em->flush();
    return new Response('Data deleted.', 200);
});

//rpc
$app->post('/rpc/{procedure}', function ($procedure, Request $request) use ($app)
{
    $data = json_decode($request->getContent());
    
    if (!isset($data->parameters)) {
        return new Response('Missing parameters.', 400, array('Content-Type' => 'text/json'));
    }
    
    $procedure = "procedure\\". ucfirst($procedure);
    if (!class_exists($procedure)) {
        return new Response('Invalid procedure.', 400, array('Content-Type' => 'text/json'));
    }
    $class = new $procedure;
    $result = $class->execute($data->parameters);

    switch ($result['status']) {
        case 'success':
            return new Response(json_encode($result['data']), 200);
            break;
        case 'error':
            return new Response('Error executing procedure', 400, array('Content-Type' => 'text/json'));
            break;
    }

});

$app->before(function (Request $request) use ($app) {
    if( ! $request->headers->has('authorization')){
        return new Response('Unauthorized', 403);
    }

    require_once __DIR__.'/configs/clients.php';
    if (!in_array($request->headers->get('authorization'), array_keys($clients))) {
        return new Response('Unauthorized', 403);
    }
});

$app->after(function (Request $request, Response $response) {
    $response->headers->set('Content-Type', 'text/json');
});

$app->run();
