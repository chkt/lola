<?php

namespace lola\io\http;

use lola\io\IRequestReplyDriver;

use lola\type\IStateTransform;
use lola\io\mime\IMimePayload;



interface IHttpDriver
extends IRequestReplyDriver
{

	public function& useRequestPayload() : IMimePayload;

	public function& useReplyPayload() : IMimePayload;

	public function& useCookies() : IHttpCookies;


	public function& useConfig() : IHttpConfig;

	public function setConfig(IHttpConfig& $config) : IHttpDriver;


	public function& useRequestMessage() : IHttpMessage;

	public function setRequestMessage(IHttpMessage& $message) : IHttpDriver;


	public function& useReplyMessage() : IHttpMessage;

	public function setReplyMessage(IHttpMessage& $message) : IHttpDriver;


	public function& useReplyTransform() : IStateTransform;

	public function setReplyTransform(IStateTransform& $transform) : IHttpDriver;
}
