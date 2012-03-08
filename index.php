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
    'model'                      => __DIR__
));

$loader->register();


$app = new Silex\Application();
require_once 'doctrine.php';


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


$app->post('/{entity}', function ($entity, Request $request) use ($app, $em) {
    // Get POST data or 400 HTTP response
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400);
    }

    // Persist data to the database
    $entityName = 'model\\' .ucfirst($entity);
    $entity = new $entityName;
    $entity->set($data);

    if (count($app['validator']->validate($entity)) > 0) {
        $app->abort(400, 'Invalid parameters.');
    }

    $em->persist($entity);
    $em->flush();

    return $app->redirect('/user/'. $entity->getId(). '.json', 201);
});

//@todo - terminar e testar. Criar método get no entity?
$app->put('/{entity}/{id}', function ($entity, $id) use ($app) {
    
    if (!$data = $request->get($entity)) {
        return new Response('Missing parameters.', 400);
    }

    if (!$user = $app['event_manager']->find($id)) {
        return new Response('User not found.', 404);
    }
    $user->login = $data['login'];
    $user->password = $data['password'];

    if (count($app['validator']->validate($event)) > 0) {
        $app->abort(400, 'Invalid parameters.');
    }

    $user->save();

    return new Response('Data updated.', 200);

});

//@todo: testar
$app->delete('/{entity}/{id}', function ($entity, $id) use ($app) {
    $entity = $app['event_manager']->find($id);
    if (!$entity) {
        return new Response('Data not found.', 404);
    }
    $entity->delete();
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
