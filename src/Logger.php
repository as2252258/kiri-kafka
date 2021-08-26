<?php
declare(strict_types=1);

namespace Kafka;


use Exception;
use Psr\Log\LoggerInterface;
use Kiri\Kiri;

/**
 * Class Logger
 * @package Kafka
 */
class Logger implements LoggerInterface
{


	/**
	 * @param mixed $message
	 * @param array $context
	 */
    public function emergency(mixed $message, array $context = array())
    {
        // TODO: Implement emergency() method.
        var_dump(func_get_args());
    }

	/**
	 * @param string $message
	 * @param array $context
	 * @throws Exception
	 */
    public function alert(mixed $message, array $context = array())
    {
	    $logger = Kiri::app()->getLogger();
	    $logger->debug($message);
    }

    public function critical(mixed $message, array $context = array())
    {
        // TODO: Implement critical() method.
        var_dump(func_get_args());
    }

	/**
	 * @param string $message
	 * @param array $context
	 * @throws Exception
	 */
    public function error(mixed $message, array $context = array())
    {
	    $logger = Kiri::app()->getLogger();
	    $logger->error($message);
    }

	/**
	 * @param string $message
	 * @param array $context
	 * @throws Exception
	 */
    public function warning(mixed $message, array $context = array())
    {
	    $logger = Kiri::app()->getLogger();
	    $logger->warning($message);
    }

	/**
	 * @param string $message
	 * @param array $context
	 * @throws Exception
	 */
    public function notice(mixed $message, array $context = array())
    {
	    $logger = Kiri::app()->getLogger();
	    $logger->info($message);
    }

	/**
	 * @param string $message
	 * @param array $context
	 * @throws Exception
	 */
    public function info(mixed $message, array $context = array())
    {
	    $logger = Kiri::app()->getLogger();
	    $logger->info($message);
    }


	/**
	 * @param string $message
	 * @param array $context
	 * @throws Exception
	 */
    public function debug(mixed $message, array $context = array())
    {
	    $logger = Kiri::app()->getLogger();
	    $logger->debug($message);
    }

	/**
	 * @param $level
	 * @param $message
	 * @param array $context
	 * @throws Exception
	 */
    public function log($level, mixed $message, array $context = array())
    {
        $logger = Kiri::app()->getLogger();
        $logger->debug($message);
    }


}
