<?php

namespace lola\io\connect;

use lola\type\data\IScalarAccessor;



interface IConnection
extends IScalarAccessor
{

	const CONNECTION_TIME = 'ts';
	const CONNECTION_TLS = 'tls';
	const CLIENT_IP = 'client.ip';
	const HOST_NAME = 'host.name';
	const HOST_IP = 'host.ip';
}
