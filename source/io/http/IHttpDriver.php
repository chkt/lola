<?php

namespace lola\io\http;

use lola\io\IRequestReplyDriver;

use lola\type\IStateTransform;
use lola\io\connect\IConnection;
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


	public function& useReplyResource() : IHttpReplyResource;

	public function setReplyResource(IHttpReplyResource& $resource) : IHttpDriver;


	public function& useReplyTransform() : IStateTransform;

	public function setReplyTransform(IStateTransform& $transform) : IHttpDriver;
}
