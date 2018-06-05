<?php
/**
* My custom RabbitAMQP Library
*/
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Myrabbit
{
	
	function __construct()
	{
		$this->connect();		
	}

	public function connect()
	{
		$url = parse_url(getenv('CLOUDAMQP_URL'));
		$this->connection = new AMQPConnection(
			$url['host'],
		 	isset($url['port']) ? $url['port'] : 5672, 
		 	$url['user'], 
		 	$url['pass'], 
		 	substr($url['path'], 1)
		 );
		$this->channel = $this->connection->channel();
	}

	public function close()
	{
		$this->channel->close();
		$this->connection->close();
	}

	public function basic_publish($queue, $message)
	{
		$this->channel->queue_declare($queue, false, false, false, false);

		$msg = new AMQPMessage($message);
		$this->channel->basic_publish($msg, '', $queue);
	}

	public function basic_consume($queue)
	{
		$this->channel->queue_declare($queue, false, false, false, false);
		$final = [];
		$callback = function ($message) {
			$final[] = $message->body;
		  var_dump($final);
		  	//$message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
		    //cancel consumer
		    //$message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
		  //$this->channel->basic_cancel($message->delivery_info['consumer_tag']);
		};

		$this->channel->basic_consume($queue, '', false, true, false, false, $callback);

		while (count($this->channel->callbacks)) {
		    $this->channel->wait();
		}
	}
}