<?php

require_once 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$clients = new SplObjectStorage();
$i = 0;

// Incoming connections.
$socket->on('connection', function($connection) use($clients, &$i) {
	$connection->id = ++$i;
	$connection->write('Enter your nickname: ');

	// Message coming through!
	$connection->on('data', function($message) use($clients, $connection) {
		// For now, assume that the first input is the nickname.
		if (empty($connection->nickName)) {
			$connection->nickName = str_replace(
				array("\n", "\r\n", "\r"),
				'',
				$message
			);
		}
		else {
			foreach ($clients as $client) {
				// Skip ourselves, we don't like echoes.
				if ($client->id == $connection->id) {
					continue;
				}

				// Send out messages to all clients.
				$client->write(
					sprintf(
						'<%s> %s',
						$connection->nickName,
						$message
					)
				);
			}
		}
	});

	// Store connection.
	$clients->attach($connection);
});

$socket->listen(1337);
$loop->run();
