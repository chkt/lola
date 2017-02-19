<?php

namespace lola\io;

use lola\io\IRequest;



interface IReply
{

	public function& useRequest() : IRequest;


	public function send();
}
