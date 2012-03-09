<?php
use Doctrine\Common\Annotations\AnnotationReader,
    DMS\Filter\Mapping,
    DMS\Filter\Filter;

//Get Doctrine Reader
$reader = new AnnotationReader();
//$reader->setEnableParsePhpImports(true);

//Load AnnotationLoader
$loader = new Mapping\Loader\AnnotationLoader($reader);

//Get a MetadataFactory
$metadataFactory = new Mapping\ClassMetadataFactory($loader);

//Get a Filter
$filter = new Filter($metadataFactory);
