<?php

namespace lola\inject;



interface IInjectable {
	static public function getDependencyConfig(Array $config);
}
