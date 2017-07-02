<?php

namespace lola\io;

use lola\io\connect\IConnection;



interface IRequestReplyDriver
{

	public function& useRequest() : IRequest;

	public function& useReply() : IReply;

	public function& useClient() : IClient;


	public function& useConnection() : IConnection;

	public function setConnection(IConnection& $connection) : IRequestReplyDriver;


	public function sendReply();
}
