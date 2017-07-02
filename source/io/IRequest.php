<?php

namespace lola\io;

use lola\io\IReply;
use lola\io\IClient;



interface IRequest
{

	public function& useReply() : IReply;

	public function& useClient() : IClient;


	public function getTime() : int;

	public function setTime(int $time) : IRequest;


	public function getTLS() : bool;

	public function setTLS(bool $tls) : IRequest;


	public function getHostName() : string;

	public function setHostName(string $hostName) : IRequest;


	public function getPath() : string;

	public function setPath(string $path) : IRequest;


	public function getQuery() : array;

	public function setQuery(array $query) : IRequest;
}
