<?php
use Silex\Provider\DoctrineServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\ClassLoader\UniversalClassLoader;


use Doctrine\ORM\Tools\Setup,
    Doctrine\ORM\EntityManager,
    Doctrine\Common\EventManager as EventManager,
    Doctrine\ORM\Events,
    Doctrine\ORM\Configuration,
    Doctrine\Common\Cache\ArrayCache as Cache,
    Doctrine\Common\Annotations\AnnotationRegistry, 
    Doctrine\Common\Annotations\AnnotationReader,
    DMS\Filter\Mapping,
    DMS\Filter\Filter,
    Doctrine\Common\ClassLoader;

require_once __DIR__.'/vendor/silex.phar';

require_once __DIR__.'/vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php';

$classLoaderSymfony = new \Doctrine\Common\ClassLoader('Symfony', __DIR__.'/vendor' );
$classLoaderSymfony->register(); 

$classLoaderDoctrineCommon = new \Doctrine\Common\ClassLoader('Doctrine\\Common', __DIR__.'/vendor/doctrine/common/lib' );
$classLoaderDoctrineCommon->register(); 

$classLoaderDoctrineMigrations = new \Doctrine\Common\ClassLoader('Doctrine\\DBAL\\Migrations', __DIR__.'/vendor/doctrine/dbal/lib' );
$classLoaderDoctrineMigrations->register(); 

$classLoaderDoctrineDbal = new \Doctrine\Common\ClassLoader('Doctrine\\DBAL', __DIR__.'/vendor/doctrine/dbal/lib' );
$classLoaderDoctrineDbal->register(); 

$classLoaderDoctrine = new \Doctrine\Common\ClassLoader('Doctrine', __DIR__.'/vendor/doctrine/orm/lib' );
$classLoaderDoctrine->register(); 

$classLoaderJMS = new \Doctrine\Common\ClassLoader('JMS', __DIR__.'/vendor' );
$classLoaderJMS->register(); 

$classLoaderDMS = new \Doctrine\Common\ClassLoader('DMS', __DIR__.'/vendor' );
$classLoaderDMS->register(); 

$classLoaderMetadata = new \Doctrine\Common\ClassLoader('Metadata', __DIR__.'/vendor/Metadata/src' );
$classLoaderMetadata->register(); 

$classLoaderLibrary = new \Doctrine\Common\ClassLoader('library', __DIR__ );
$classLoaderLibrary->register(); 

$classLoaderCoderockr = new \Doctrine\Common\ClassLoader('Coderockr', __DIR__.'/vendor' );
$classLoaderCoderockr->register();

$classLoaderImageWorkshop = new \Doctrine\Common\ClassLoader('PHPImageWorkshop', __DIR__.'/vendor/ImageWorkshop/src' );
$classLoaderImageWorkshop->register();

$classLoaderModel = new \Doctrine\Common\ClassLoader('model', getenv('APPLICATION_PATH') . '/library' );
$classLoaderModel->register(); 

$classLoaderService = new \Doctrine\Common\ClassLoader('service', getenv('APPLICATION_PATH') . '/library' );
$classLoaderService->register(); 

$classLoaderTest = new \Doctrine\Common\ClassLoader('test', getenv('APPLICATION_PATH') . '/library' );
$classLoaderTest->register(); 



if(!getenv('APPLICATION_ENV')) 
    $env = 'testing';
else
    $env = getenv('APPLICATION_ENV');

if ($env == 'testing')
    include getenv('APPLICATION_PATH'). DIRECTORY_SEPARATOR .  'configs' . DIRECTORY_SEPARATOR . 'configs.testing.php';
elseif ($env == 'development')
    include getenv('APPLICATION_PATH'). DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'configs.development.php';
else
    include getenv('APPLICATION_PATH').DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'configs.php';

//filter
//Get Doctrine Reader
$reader = new AnnotationReader();
//$reader->setEnableParsePhpImports(true);

//Load AnnotationLoader
$loader = new Mapping\Loader\AnnotationLoader($reader);

//Get a MetadataFactory
$metadataFactory = new Mapping\ClassMetadataFactory($loader);

//Get a Filter
$filter = new Filter($metadataFactory);

//doctrine
$config = new Configuration();
//$cache = new Cache();
$cache = new \Doctrine\Common\Cache\ApcCache();
$config->setQueryCacheImpl($cache);
$config->setProxyDir('/tmp');
$config->setProxyNamespace('EntityProxy');
$config->setAutoGenerateProxyClasses(true);
$entityCache  = null;
if ($useCache == 1) {
    $entityCache = $cache;
}
 
//mapping (example uses annotations, could be any of XML/YAML or plain PHP)
AnnotationRegistry::registerFile(__DIR__. DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'doctrine' . DIRECTORY_SEPARATOR . 'orm' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Doctrine' . DIRECTORY_SEPARATOR . 'ORM' . DIRECTORY_SEPARATOR . 'Mapping' . DIRECTORY_SEPARATOR . 'Driver' . DIRECTORY_SEPARATOR . 'DoctrineAnnotations.php');
AnnotationRegistry::registerAutoloadNamespace('JMS', __DIR__. DIRECTORY_SEPARATOR . 'vendor');
AnnotationRegistry::registerAutoloadNamespace('DMS', __DIR__. DIRECTORY_SEPARATOR . 'vendor');

$driver = new Doctrine\ORM\Mapping\Driver\AnnotationDriver(
    new Doctrine\Common\Annotations\AnnotationReader(),
    array(__DIR__ . DIRECTORY_SEPARATOR . 'model')
);
$config->setMetadataDriverImpl($driver);
$config->setMetadataCacheImpl($cache);

//getting the EntityManager
$em = EntityManager::create(
    $dbOptions,
    $config
);

//load subscribers
$evm = $em->getEventManager();
try {
    $directoryIterator = new \DirectoryIterator(getenv('APPLICATION_PATH') . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'subscriber');
    foreach ($directoryIterator as $f) {
        if ($f->getFileName() != '.' && $f->getFilename() !='..') {
            $subscriber = 'model\\subscriber\\' . $f->getBasename('.php');
            $evm->addEventSubscriber(new $subscriber);
        }
    }
}catch (UnexpectedValueException $e) {
    //directory doesn't exists
}

