<?php

namespace lola\input\valid;



interface IValidationInterceptor
{

	public function intercept(IValidationStep& $step);
}
