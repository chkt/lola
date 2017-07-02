<?php

namespace lola\io\connect;

use lola\inject\IInjectable;



interface IConnectionFactory
extends IInjectable
{

	public function getConnection() : IConnection;
}
