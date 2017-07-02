<?php

namespace lola\io\http;

use lola\io\IReply;
use lola\io\mime\IMimeContainer;

use lola\io\mime\IMimePayload;



interface IHttpReply
extends IReply, IMimeContainer
{

	public function& usePayload() : IMimePayload;

	public function& useCookies() : IHttpCookies;


	public function getCode() : string;

	public function setCode(string $code) : IHttpReply;

	public function getCodeHeader() : string;

	public function getCodeMessage() : string;


	public function isRedirect() : bool;

	public function getRedirectTarget() : string;

	public function setRedirectTarget(string $url) : IHttpReply;


	public function hasHeader(string $name) : bool;

	public function getHeader(string $name) : string;

	public function setHeader(string $name, string $value) : IHttpReply;

	public function resetHeader(string $name) : IHttpReply;
}
