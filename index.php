<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

use model\User;

require_once 'vendor/silex.phar';

require_once __DIR__.'/vendor/Symfony/Component/Classloader/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'                    => __DIR__.'/vendor',
    'Doctrine\\Common'           => __DIR__.'/vendor/doctrine/common/lib',
    'Doctrine\\DBAL\\Migrations' => __DIR__.'/vendor/doctrine/dbal/lib',
    'Doctrine\\DBAL'             => __DIR__.'/vendor/doctrine/dbal/lib',
    'Doctrine'                   => __DIR__.'/vendor/doctrine/orm/lib',
    'DMS'                => __DIR__.'/vendor',
    'model'                      => __DIR__
));

$loader->register();


$app = new Silex\Application();
require_once __DIR__.'/library/doctrine.php';
require_once __DIR__.'/library/filter.php';


//app configuration
$app->register(new DoctrineServiceProvider(), array(
    'db.options'  => array(
        'driver'  => 'pdo_mysql',
        'host'    => 'localhost',
        'user'    => 'root',
        'dbname'  => 'rest',
    ),
    'db.dbal.class_path' => 'vendor/doctrine-dbal/lib',
    'db.common.class_path' => 'vendor/doctrine-common/lib',
));


$app->register(new Silex\Provider\ValidatorServiceProvider(), array(
    'validator.class_path'    => __DIR__.'/vendor',
));

$app->get('/{entity}.{format}', function ($entity, $format) use ($app) {
    $query = "SELECT * FROM $entity";
    $data = $app['db']->fetchAll($query);
    //@todo ver se está certo usar o codigo 404 quando não encontrado
    if(count($data) == 0) {
        $app->abort(404, 'Data not found');
    }
    return new Response(json_encode($data));

})
->assert('format', 'xml|json');

$app->get('/{entity}/{id}.{format}', function ($entity, $id, $format) use ($app, $em) {
    $data = $em->find('model\\'.ucfirst($entity), $id);
    
    //@todo ver se está certo usar o codigo 404 quando não encontrado
    if($data === null) {
       $app->abort(404, 'Data not found');
    }

    return new Response($data->toJson());
})
->assert('format', 'xml|json');


$app->post('/{entity}', function ($entity, Request $request) use ($app, $em, $filter) {
    // Get POST data or 400 HTTP response
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400);
    }

    // Persist data to the database
    $entityName = 'model\\' .ucfirst($entity);
    $entity = new $entityName;
    $entity->set($data);

    //valid entity
    if (count($app['validator']->validate($entity)) > 0) {
        $app->abort(400, 'Invalid parameters.');
    }

    //Filter entity
    $filter->filterEntity($entity);


    $em->persist($entity);
    $em->flush();

    return new Response($entity->toJson());
});

$app->put('/{entity}/{id}', function ($entity, $id, Request $request) use ($app, $em, $filter) {
    
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400);
    }

    if (!$entity = $em->find('model\\'.ucfirst($entity), $id)) {
        return new Response('Not found.', 404);
    }

    $entity->set($data);

    if (count($app['validator']->validate($entity)) > 0) {
        $app->abort(400, 'Invalid parameters.');
    }

    //Filter entity
    $filter->filterEntity($entity);

    $em->persist($entity);
    $em->flush();

    return new Response($entity->toJson(), 200);
});


$app->delete('/{entity}/{id}', function ($entity, $id) use ($app, $em) {
    if (!$entity = $em->find('model\\'.ucfirst($entity), $id)) {
        return new Response('Data not found.', 404);
    }
    $em->remove($entity);
    $em->flush();
    return new Response('Data deleted.', 200);
});


$app->error(function (\Exception $e, $code) {
    echo $e->getMessage(), "\n";
    switch ($code) {
        case 400:
            $message = 'Bad request.';
            break;
        case 404:
            $message = 'Page not found.';
            break;
        default:
            $message = 'Internal Server Error.';
    return new Response($message, $code);
} });

//$app['api_user'] == 'elton';
//$app['api_pwd']  == 'elton';

$app->before(function (Request $request) use ($app) {
    //echo '<pre>'; var_dump($request->headers->has('authorization'));exit;
    /*$user = $request->server->get('PHP_AUTH_USER');
    $pwd  = $request->server->get('PHP_AUTH_PW');

    if( $app['api_user'] !== $user || $app['api_pwd'] !== $pwd){
        return new Response('Unauthorized', 403);
    } */
});

$app->after(function (Request $request, Response $response) {
    // Get URI parameter to determine requested output format
    $format = $request->attributes->get('format');
    switch ($format) {
        case 'xml':
            $response->headers->set('Content-Type', 'text/xml');
            break;
        case 'json':    
            $response->headers->set('Content-Type', 'text/json');
            break;
        break; 
    }
});

$app->run();
