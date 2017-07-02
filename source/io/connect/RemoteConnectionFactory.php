<?php

namespace lola\io\connect;



final class RemoteConnectionFactory
extends AConnectionFactory
{

	protected function _produceInstance() : IConnection {
		$https = filter_input(INPUT_SERVER, 'HTTPS');

		$data = [
			'ts' => $_SERVER['REQUEST_TIME'],
			'tls' => !empty($https) && $https !== 'off',
			'client' => [
				'ip' => filter_input(INPUT_SERVER, 'REMOTE_ADDR')
			],
			'host' => [
				'name' => filter_input(INPUT_SERVER, 'SERVER_NAME'),
				'ip' => filter_input(INPUT_SERVER, 'SERVER_ADDR')
			]
		];

		return new Connection($data);
	}
}
