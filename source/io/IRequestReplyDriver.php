<?php

namespace lola\io;

use lola\io\IRequest;
use lola\io\IReply;
use lola\io\IClient;



interface IRequestReplyDriver
{

	public function& useRequest() : IRequest;

	public function& useReply() : IReply;

	public function& useClient() : IClient;


	public function sendReply();
}
