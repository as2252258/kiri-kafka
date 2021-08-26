<?php


namespace Kafka;


use Kiri\Abstracts\BaseObject;


/**
 * Class KafkaProvider
 * @package Kafka
 */
class KafkaProvider extends BaseObject
{


    private array $_topics = [];


    /**
     * @param $topic
     * @param $handler
     */
    public function addConsumer($topic, $handler)
    {
        if (isset($this->_topics[$topic])) {
            return;
        }
        $this->_topics[$topic] = $handler;
    }


	/**
	 * @param string $topic
	 * @return mixed
	 */
    public function getConsumer(string $topic): mixed
    {
        return $this->_topics[$topic] ?? null;
    }

}
