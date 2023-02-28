## Run RabbitMQ + RabbitMQ Management

``` sh
docker run -d --hostname my-rabbit --name some-rabbit -p 8080:15672 rabbitmq:3-management
```

## Build Dockerfile

``` sh
docker build -t rabbitmqexamples:latest .
```

## "Hello World!" - The simplest thing that does something 

### Run the consumer (receiver)

``` sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name receiver rabbitmqexamples bash
```

``` sh
php receive.php
```

### Run the publisher (sender)

``` sh
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php send.php
```

## Work queues - Distributing tasks among workers

### Run the consumer (worker 1)

``` sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name worker1 rabbitmqexamples bash
```

``` sh
php worker.php
```

### Run the consumer (worker 2)

``` sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name worker2 rabbitmqexamples bash
```

``` sh
php worker.php
```

### Run the publisher (complex task)

``` sh
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php new_task.php "A very hard task which takes two seconds.."
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php new_task.php "A very hard task which takes ten seconds.........."
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php new_task.php "A very hard task which takes four seconds...."
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php new_task.php "A very hard task which takes twenty seconds...................."
```

## Publish/Subscribe - Sending messages to many consumers at once

### Run the consumer (subscriber 1) - save logs to a file

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer1 rabbitmqexamples bash
```

```sh
php receive_logs.php > logs_from_rabbit.log
```

### Run the consumer (subscriber 2) - see the logs on your screen

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer2 rabbitmqexamples bash
```

```sh
php receive_logs.php
```

### Run the producer

```sh
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log.php
```

## Routing - Receiving messages selectively

### Run the consumer (subscriber 1) - save only 'warning' and 'error' (and not 'info') log messages to a file

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer1 rabbitmqexamples bash
```

```sh
php receive_logs_direct.php warning error > logs_from_rabbit.log
```

### Run the consumer (subscriber 2) - see all the log messages on your screen

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer2 rabbitmqexamples bash
```

```sh
php receive_logs_direct.php info warning error
```

### Run the producer

```sh
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_direct.php error "Error log."
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_direct.php warning "Warning log."
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_direct.php info "Info log."
```

## Topics - Receiving messages based on a pattern (topics)

### Run the consumer (subscriber 1) - Receive all the logs

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer1 rabbitmqexamples bash
```

```sh
php receive_logs_topic.php "#"
```

### Run the consumer (subscriber 2) - Receive all logs from the facility "kern"

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer2 rabbitmqexamples bash
```

```sh
php receive_logs_topic.php "kern.*"
```

### Run the consumer (subscriber 3) - Receive only about "critical" logs

```sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name consumer3 rabbitmqexamples bash
```

```sh
php receive_logs_topic.php "*.critical"
```

### Run the producer

```sh
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_topic.php "kern.critical" "A critical kernel error"
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_topic.php "kern.info" "A kernel info"
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_topic.php "kern.warning" "A kernel warning"
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php emit_log_topic.php "syslog.critical" "A critical system error"
```

## Dead letter queue

### Run the consumer

``` sh
docker run -tti --rm --volume "$(pwd)":/app -w /app --name worker rabbitmqexamples bash
```

``` sh
php worker_dead_letter.php
```

### Run the publisher

``` sh
docker run --rm --volume "$(pwd)":/app -w /app rabbitmqexamples php new_task_dead_letter.php "A very hard task which takes two seconds.."
```