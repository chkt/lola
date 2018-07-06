<?php

namespace lola\ctrl;



final class NoActionException
extends \Exception
implements IActionException
{

	public function __construct(string $action) {
		parent::__construct(sprintf('CTR no action "%s"', $action));
	}
}
