<?php
declare(strict_types=1);

namespace Kafka;


use Exception;
use Kiri\Abstracts\Config;
use Kiri\Abstracts\Config as SConfig;
use Kiri\Abstracts\Providers;
use Kiri\Application;


/**
 * Class QueueProviders
 * @package Queue
 */
class KafkaImports extends Providers
{

	/**
	 * @param Application $application
	 * @throws Exception
	 */
	public function onImport(Application $application)
	{
		if (!extension_loaded('rdkafka')) {
			return;
		}
		$kafka = SConfig::get('kafka', ['enable' => false]);
		if (($kafka['enable'] ?? false) == false) {
			return;
		}
		$kafkaServers = Config::get('kafka.consumers', []);
		if (empty($kafkaServers)) {
			return;
		}
		$server = $application->getServer();
		foreach ($kafkaServers as $kafkaServer) {
			$server->addProcess(new Kafka($kafkaServer));
		}
	}

}
