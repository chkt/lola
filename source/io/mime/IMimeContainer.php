<?php

namespace lola\io\mime;



interface IMimeContainer
{

	public function getBody() : string;

	public function setBody(string $body) : IMimeContainer;


	public function getMime() : string;

	public function setMime(string $mime) : IMimeContainer;


	public function getEncoding() : string;

	public function setEncoding(string $encoding) : IMimeContainer;
}
