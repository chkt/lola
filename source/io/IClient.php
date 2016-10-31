<?php

namespace lola\io;



interface IClient
{

	public function isIP4() : bool;

	public function isIP6() : bool;


	public function getIP() : string;

	public function setIP(string $ip) : IClient;


	public function getUA() : string;

	public function setUA(string $ua) : IClient;
}
