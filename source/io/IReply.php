<?php

namespace lola\io;

use lola\io\IRequest;



interface IReply
{

	public function& useRequest() : IRequest;


	public function getBody() : string;

	public function setBody(string $body) : IReply;

	public function setBodyFromOB() : IReply;


	public function send() : null;

	public function sendOB() : null;
}
