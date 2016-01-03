<?php

namespace chkt\inject;



interface IInjectable {
	static public function getDependencyConfig(Array $config);
}
