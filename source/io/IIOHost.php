<?php

namespace lola\io;



interface IIOHost
{

	public function getRequest() : IRequest;

	public function getReply() : IReply;
}
