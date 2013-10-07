<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\DI;

/**
 * @author Lopo <lopo@lohini.net>
 */
class WebLoaderExtension
extends \Nette\DI\CompilerExtension
{
	public $defaults=[
		'basePath' => '/webLoader/'
		];


	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();
		$config=$this->getConfig(['debug' => $builder->parameters['debugMode']]);

		$builder->parameters[$this->prefix('debug')]=!empty($config['debug']);

		$this->loadConfig('console');

		$config=$this->resolveConfig($config, $this->defaults);
		if (!\Nette\Utils\Strings::startsWith($config['basePath'], '/')) {
			$config['basePath']='/'.$config['basePath'];
			}

		$builder->addDefinition($this->prefix('route'))
				->setClass('Lohini\WebLoader\WebLoaderRoute', [$config['basePath'].'<id>', ['presenter' => 'Lohini:WebLoader']])
				->setAutowired(FALSE)
				->setInject(FALSE);
		$builder->getDefinition('router')
				->addSetup('Lohini\WebLoader\WebLoaderRoute::prependTo($service, ?, ?)', [$this->prefix('@route'), $config['basePath']]);
		$builder->getDefinition('nette.presenterFactory')
				->addSetup('if (method_exists($service, ?)) { $service->setMapping([? => ?]); } '
						.'elseif (property_exists($service, ?)) { $service->mapping[?] = ?; }',
					['setMapping', 'Lohini', 'LohiniModule\*\*Presenter', 'mapping', 'Lohini', 'LohiniModule\*\*Presenter']
					);

		$builder->addDefinition($this->prefix('cache'))
				->setClass('Lohini\WebLoader\Caching\Cache', ['@cacheStorage', 'Lohini.WebLoader'])
				->setInject(FALSE)
				;
	}

	/**
	 * @param $provided
	 * @param $defaults
	 * @param $diff
	 * @return array
	 */
	private function resolveConfig(array $provided, array $defaults, array $diff=[])
	{
		return $this->getContainerBuilder()->expand(
			\Nette\DI\Config\Helpers::merge(
				array_diff_key($provided, array_diff_key($diff, $defaults)),
				$defaults
				)
			);
	}

	/**
	 * @param string $name
	 */
	private function loadConfig($name)
	{
		$this->compiler->parseServices(
			$this->getContainerBuilder(),
			$this->loadFromFile(__DIR__.'/config/'.$name.'.neon'),
			$this->prefix($name)
			);
	}
}
