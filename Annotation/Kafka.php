<?php


namespace Kafka\Annotation;


use Kiri\Annotation\AbstractAttribute;
use Exception;
use Kafka\ConsumerInterface;
use Kafka\KafkaProvider;
use Kiri;

/**
 * Class Kafka
 * @package Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)] class Kafka extends AbstractAttribute
{


	/**
	 * Kafka constructor.
	 * @param string $topic
	 */
	public function __construct(public string $topic)
	{
	}


	/**
	 * @param mixed $class
	 * @param mixed|null $method
	 * @return bool
	 */
    public function execute(mixed $class, mixed $method = null): bool
	{
		if (!in_array(ConsumerInterface::class, class_implements($class))) {
    		return false;
		}
		/** @var KafkaProvider $container */
		$container = Kiri::getDi()->get(KafkaProvider::class);
		$container->addConsumer($this->topic, $class);

		return true;
	}


}
