<?php
/**
 * Frontend Service Provider
 *
 * @copyright   Weltspiegel Cottbus
 * @license     MIT; see LICENSE file
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Weltspiegel\Component\Weltspiegel\Administrator\Extension\WeltspiegelComponent;
use Joomla\CMS\Component\Router\RouterFactoryInterface;

return new class implements ServiceProviderInterface {
	public function register(Container $container): void
	{
		$container->registerServiceProvider(new MVCFactory('\\Weltspiegel\\Component\\Weltspiegel'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\Weltspiegel\\Component\\Weltspiegel'));
		$container->registerServiceProvider(new RouterFactory('\\Weltspiegel\\Component\\Weltspiegel'));

		$container->set(ComponentInterface::class,
			function (Container $container) {
				$component = new WeltspiegelComponent($container->get(ComponentDispatcherFactoryInterface::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));
				return $component;
			}
		);
	}
};
