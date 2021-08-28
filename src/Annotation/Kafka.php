<?php


namespace Kafka\Annotation;


use Annotation\Attribute;
use Exception;
use Kafka\ConsumerInterface;
use Kafka\KafkaProvider;
use Kiri\Kiri;

/**
 * Class Kafka
 * @package Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)] class Kafka extends Attribute
{


	/**
	 * Kafka constructor.
	 * @param string $topic
	 */
	public function __construct(string $topic)
	{
	}


	/**
	 * @param static $params
	 * @param mixed $class
	 * @param mixed|null $method
	 * @return bool
	 * @throws Exception
	 */
    public static function execute(mixed $params, mixed $class, mixed $method = null): bool
	{
		if (!in_array(ConsumerInterface::class, class_implements($class))) {
    		return false;
		}
		/** @var KafkaProvider $container */
		$container = Kiri::getDi()->get(KafkaProvider::class);
		$container->addConsumer($params->topic, $class);

		return true;
	}


}
