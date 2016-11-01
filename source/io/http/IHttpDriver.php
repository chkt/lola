<?php

namespace lola\io\http;

use lola\io\IRequestReplyDriver;

use lola\type\IStateTransform;
use lola\io\http\IHttpCookies;
use lola\io\http\IHttpConfig;
use lola\io\http\IHttpRequestResource;
use lola\io\http\IHttpReplyResource;



interface IHttpDriver
extends IRequestReplyDriver
{

	public function& useCookies() : IHttpCookies;


	public function& useConfig() : IHttpConfig;

	public function setConfig(IHttpConfig& $config) : IHttpDriver;


	public function& useRequestResource() : IHttpRequestResource;

	public function setRequestResource(IHttpRequestResource& $resource) : IHttpDriver;


	public function& useReplyResource() : IHttpReplyResource;

	public function setReplyResource(IHttpReplyResource& $resource) : IHttpDriver;


	public function& useReplyTransform() : IStateTransform;

	public function setReplyTransform(IStateTransform& $transform) : IHttpDriver;
}
