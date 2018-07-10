<?php

namespace lola\io;



interface IRequestReplyDriver
extends IIOHost
{

	public function& useClient() : IClient;


	public function sendReply();
}
