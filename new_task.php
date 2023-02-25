<?php

require_once __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(
    $_ENV["RABBITMQ_HOST"], 
    $_ENV["RABBITMQ_PORT"], 
    $_ENV["RABBITMQ_USER"], 
    $_ENV["RABBITMQ_PASSWORD"]
);

$channel = $connection->channel();

$channel->queue_declare(queue: 'task_queue', durable: true, auto_delete: false);

$data = implode(' ', array_slice($argv, 1));

if (empty($data)) {
    $data = "Hello World!";
}

// Messages marked as 'persistent' that are delivered to 'durable' queues will be logged to disk. 
// Durable queues are recovered in the event of a crash, along with any persistent messages they stored prior to the crash.
$msg = new AMQPMessage(
    $data,
    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
);

$channel->basic_publish(msg: $msg, routing_key: 'task_queue');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();
