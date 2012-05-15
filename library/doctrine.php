<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\Common\Cache\ArrayCache as Cache,
    Doctrine\Common\Annotations\AnnotationRegistry, 
    Doctrine\Common\ClassLoader;
 
//configuration
$config = new Configuration();
$cache = new Cache();
$config->setQueryCacheImpl($cache);
$config->setProxyDir(__DIR__.'/../vendor/EntityProxy');
$config->setProxyNamespace('EntityProxy');
$config->setAutoGenerateProxyClasses(true);
 
//mapping (example uses annotations, could be any of XML/YAML or plain PHP)
AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
$driver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
    new Doctrine\Common\Annotations\AnnotationReader(),
    array(__DIR__ .'/../vendor/Entity')
);
$config->setMetadataDriverImpl($driver);
$config->setMetadataCacheImpl($cache);

//getting the EntityManager
$em = EntityManager::create(
    array(
        'driver' => 'pdo_mysql',
        'user' => 'root',
        'password' => '194639gh',
        'dbname' => 'rest'
    ),
    $config
);