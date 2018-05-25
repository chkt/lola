<?php

namespace lola\app;

use eve\common\factory\IBaseFactory;
use eve\common\factory\ASimpleFactory;
use eve\inject\IInjectableIdentity;
use lola\common\IComponentConfig;
use lola\common\access\AccessorSelector;
use lola\error\INativeErrorSource;
use lola\error\NativeErrorSource;



class AppFactory
extends ASimpleFactory
{

	protected function _getConfigDefaults() : array {
		return [
			'resolvers' => [
				'core' => \eve\inject\resolve\ProviderResolver::class,
				'environment' => \eve\inject\resolve\ProviderResolver::class,
				'service' => \eve\inject\resolve\ProviderResolver::class,
				'controller' => \eve\inject\resolve\ProviderResolver::class
			],
			'providers' => [
				'core' => \lola\app\CoreProvider::class,
				'environment' => [
					'qname' => \lola\provide\MapProvider::class,
					'config' => [
						'app' => \lola\app\App::class,
						'registry' => \lola\module\Registry::class,
						'http' => \lola\io\http\HttpDriver::class,
						'log' => \lola\log\FileLogger::class,
						'errors' => \lola\error\ErrorDriver::class
					]
				],
				'service' => \lola\service\ServiceProvider::class,
				'controller' => \lola\ctrl\ControllerProvider::class
			],
			'global' => []
		];
	}


	protected function _produceComponentConfig(IBaseFactory $base, array $config) : IComponentConfig {
		return $base->newInstance(AppConfig::class, [
			$base->newInstance(AccessorSelector::class),
			$config
		]);
	}


	protected function _produceNativeErrorSource(IApp $app, array $config) : INativeErrorSource {
		return $app
			->getInjector()
			->produce(NativeErrorSource::class);
	}


	protected function _produceInstance(IBaseFactory $base, array $config) {
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
				'component' => $this->_produceComponentConfig($base, $config['global'])
			]);

		$this->_produceNativeErrorSource($app, $config);

		return $app;
	}
}
