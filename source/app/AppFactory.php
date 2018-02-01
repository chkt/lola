<?php

namespace lola\app;

use eve\common\factory\ICoreFactory;
use eve\common\factory\ASimpleFactory;
use eve\inject\IInjectableIdentity;
use lola\error\INativeErrorSource;
use lola\error\NativeErrorSource;



class AppFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'providers' => [
				'core' => \lola\app\CoreProvider::class,
				'environment' => \lola\provide\EnvironmentProvider::class,
				'service' => \lola\service\ServiceProvider::class,
				'controller' => \lola\ctrl\ControllerProvider::class
			],
			'global' => []
		];
	}


	protected function _produceNativeErrorSource(IApp $app, array $config) : INativeErrorSource {
		return $app
			->getInjector()
			->produce(NativeErrorSource::class);
	}


	protected function _produceInstance(ICoreFactory $base, array $config) {
		$driverFactory = $base->newInstance(CoreProviderFactory::class, [ $base ]);
		$driver = $driverFactory->produce($config);

		$key = $driver
			->getKeyEncoder()
			->encode(CoreProvider::class, IInjectableIdentity::IDENTITY_SINGLE);

		$driver
			->getInstanceCache()
			->setItem($key, $driver);

		$app = $driver
			->getInjector()
				->produce(App::class, [
				'driver' => $driver,
				'component' => $base->newInstance(AppConfig::class, [ $config['global'] ])
			]);

		$this->_produceNativeErrorSource($app, $config);

		return $app;
	}
}
