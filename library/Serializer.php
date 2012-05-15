<?php
namespace library;


use JMS\SerializerBundle\Serializer\Serializer as JMSSerializer;
use Metadata\MetadataFactory;
use JMS\SerializerBundle\Metadata\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\SerializerBundle\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\SerializerBundle\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Serializer\Handler\ObjectBasedCustomHandler;
use JMS\SerializerBundle\Serializer\Handler\DateTimeHandler;
use JMS\SerializerBundle\Serializer\Handler\ConstraintViolationHandler;
use JMS\SerializerBundle\Serializer\Handler\DoctrineProxyHandler;
use JMS\SerializerBundle\Serializer\Handler\ArrayCollectionHandler;
use JMS\SerializerBundle\Serializer\JsonSerializationVisitor;
use JMS\SerializerBundle\Serializer\JsonDeserializationVisitor;



class Serializer
{

    protected $serializer;

    public function __construct()
    {
        $factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $namingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $objectConstructor = new UnserializeObjectConstructor();

        $customSerializationHandlers = $this->getSerializationHandlers();
        $customDeserializationHandlers = $this->getDeserializationHandlers();

        $serializationVisitors = array(
            'json' => new JsonSerializationVisitor($namingStrategy, $customSerializationHandlers),
            //'xml'  => new XmlSerializationVisitor($namingStrategy, $customSerializationHandlers),
            //'yml'  => new YamlSerializationVisitor($namingStrategy, $customSerializationHandlers),
        );
        $deserializationVisitors = array(
            'json' => new JsonDeserializationVisitor($namingStrategy, $customDeserializationHandlers, $objectConstructor),
            //'xml'  => new XmlDeserializationVisitor($namingStrategy, $customDeserializationHandlers, $objectConstructor),
        );

        $this->serializer = new JMSSerializer($factory, $serializationVisitors, $deserializationVisitors);
    }

    public function serialize($data, $format)
    {
        return $this->serializer->serialize($data, $format);
    }


    public function deserialize($data, $type, $format) 
    {
        return $this->serializer->deserialize($data, $type, $format);
    }

    public function setGroups($groups)
    {
        return $this->serializer->setGroups($groups);
    }

    protected function getSerializationHandlers()
    {
    
        $factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $objectConstructor = new UnserializeObjectConstructor();

        $handlers = array(
            new ObjectBasedCustomHandler($objectConstructor, $factory),
            new DateTimeHandler(),
            new ConstraintViolationHandler(),
            new DoctrineProxyHandler(),
        );

        return $handlers;
    }

    protected function getDeserializationHandlers()
    {
        $factory = new MetadataFactory(new AnnotationDriver(new AnnotationReader()));
        $objectConstructor = new UnserializeObjectConstructor();

        $handlers = array(
            new ObjectBasedCustomHandler($objectConstructor, $factory),
            new DateTimeHandler(),
            new ArrayCollectionHandler(),
        );

        return $handlers;
    } 
}