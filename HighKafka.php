<?php
declare(strict_types=1);

namespace Kafka;


use Kiri\Annotation\Inject;
use Kiri;
use Kiri\Server\Abstracts\BaseProcess;
use Kiri\Server\Broadcast\OnBroadcastInterface;
use Psr\Log\LoggerInterface;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\Topic;
use Swoole\Process;
use Throwable;

/**
 * Class Queue
 * @package Queue
 */
class HighKafka extends BaseProcess
{

	protected bool $enableSwooleCoroutine = false;


	public string $name = 'kafka';


	private KafkaConsumer $topic;


	#[Inject(LoggerInterface::class)]
	public LoggerInterface $logger;


	/**
	 * @param array $kafkaConfig
	 */
	public function __construct(public array $kafkaConfig)
	{
		$this->name .= ' consumer `' . $this->kafkaConfig['topic'] . '`';
	}


	/**
	 * @param OnBroadcastInterface $message
	 * @return void
	 */
	public function onBroadcast(OnBroadcastInterface $message): void
	{
		$message->process();
	}


	/**
	 * @return string
	 */
	public function getName(): string
	{
		return 'Kafka Consumer ' . $this->kafkaConfig['topic'];
	}


	/**
	 * @param Process $process
	 * @throws \Exception
	 */
	public function process(Process $process): void
	{
		try {
			[$config, $topic, $conf] = $this->kafkaConfig($this->kafkaConfig);
			if (empty($config) && empty($topic) && empty($conf)) {
				return;
			}
			$this->topic = new KafkaConsumer($config);
			$this->topic->subscribe([$this->kafkaConfig['topic']]);

			$this->resolve($this->topic, $conf['interval'] ?? 1000);
		} catch (Throwable $exception) {
			$this->logger->error(error_trigger_format($exception));
		}
	}


	/**
	 * @return $this
	 */
	public function onSigterm(): static
	{
		pcntl_signal(SIGTERM, function () {
			$this->topic->close();

			$this->onShutdown(1);
		});
		return $this;
	}


	/**
	 * @param KafkaConsumer $topic
	 * @param $interval
	 * @throws \Exception
	 */
	private function resolve(KafkaConsumer $topic, $interval)
	{
		try {
			$message = $topic->consume(-1);
			if (!empty($message)) {
				$this->onCall($message);
			}
		} catch (Throwable $exception) {
			$this->logger->error('throwable', [error_trigger_format($exception)]);
		} finally {
			if ($this->isStop()) {
				return;
			}
			$this->resolve($topic, $interval);
		}
	}


	/**
	 * @param Message $message
	 * @return void
	 * @throws \Exception
	 */
	private function onCall(Message $message)
	{
		if ($message->err == RD_KAFKA_RESP_ERR_NO_ERROR) {
			$this->logger->debug('kafka message', [json_encode($message)]);
			$this->handlerExecute($message->topic_name, $message);
		} else if ($message->err == RD_KAFKA_RESP_ERR__PARTITION_EOF) {
			$this->logger->warning('No more messages; will wait for more');
		} else if ($message->err == RD_KAFKA_RESP_ERR__TIMED_OUT) {
			$this->logger->error('Kafka Timed out');
		} else {
			$this->logger->error($message->errstr());
		}
	}


	/**
	 * @param $topic
	 * @param $message
	 * @throws \Exception
	 */
	protected function handlerExecute($topic, $message)
	{
		try {
			/** @var KafkaProvider $container */
			$container = Kiri::getDi()->get(KafkaProvider::class);
			$data = $container->getConsumer($topic);
			if (empty($data)) {
				return;
			}
			/** @var ConsumerInterface $handler */
			$handler = new $data(new Struct($topic, $message));
			$handler->process();
		} catch (Throwable $exception) {
			$this->logger->error('throwable', [error_trigger_format($exception)]);
		}
	}


	/**
	 * @param $kafka
	 * @return array
	 * @throws \Exception
	 */
	private function kafkaConfig($kafka): array
	{
		try {
			$conf = new Configuration();
			$conf->setRebalanceCb([$this, 'rebalanced_cb']);
			$conf->setGroupId($kafka['groupId']);
			$conf->setMetadataBrokerList($kafka['brokers']);
			$conf->setSocketTimeoutMs(30000);

			if (function_exists('pcntl_sigprocmask')) {
				pcntl_sigprocmask(SIG_BLOCK, [SIGIO]);
				$conf->setInternalTerminationSignal((string)SIGIO);
			}

			$topicConf = new TopicConfig();
			$topicConf->setAutoCommitEnable(true);
			$topicConf->setAutoCommitIntervalMs(100);

			//smallest：简单理解为从头开始消费，
			//largest：简单理解为从最新的开始消费
			$topicConf->setAutoOffsetReset('earliest');
//			$topicConf->setOffsetStorePath('kafka_offset.log');
//			$topicConf->setOffsetStoreMethod('broker');

			return [$conf, $topicConf, $kafka];
		} catch (Throwable $exception) {
			$this->logger->error('throwable', [error_trigger_format($exception)]);
			return [null, null, null];
		}
	}


	/**
	 * @param KafkaConsumer $kafka
	 * @param $err
	 * @param array|null $partitions
	 * @throws Exception
	 * @throws \Exception
	 */
	public function rebalanced_cb(KafkaConsumer $kafka, $err, array $partitions = null)
	{
		if ($err == RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS) {
			$kafka->assign($partitions);
		} else if ($err == RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS) {
			$kafka->assign(NULL);
		} else {
			throw new \Exception($err);
		}
	}


}
