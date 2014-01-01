<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

use Nette\Application\Request;

/**
 * @author Lopo <lopo@lohini.net>
 */
class WebLoaderRoute
extends \Nette\Application\Routers\Route
{
	/** @var string */
	protected static $path;


	/**
	 * Constructs absolute URL from Request object.
	 */
	public function constructUrl(Request $appRequest, \Nette\Http\Url $refUrl)
	{
		if ($appRequest->getPresenterName()!=$this->getTargetPresenter()) {
			return NULL;
			}
		$params=$appRequest->getParameters();
		return self::$path.(isset($params['id'])? $params['id'] : $params[0]);
	}

	/**
	 * @param \Nette\Application\IRouter $router
	 * @param \Nette\DI\Container $container
	 * @param string $path
	 * @return \Nette\Application\Routers\RouteList
	 */
	public static function prependTo(\Nette\Application\IRouter &$router, self $wlRouter, $path)
	{
		if (!$router instanceof \Nette\Application\Routers\RouteList) {
			throw new \Nette\Utils\AssertionException(
				'If you want to use Lohini/WebLoader then your main router '
				.'must be an instance of Nette\Application\Routers\RouteList'
				);
			}
		self::$path=$path;
		$router[]=$wlRouter;
		$lastKey=count($router)-1;
		foreach ($router as $i => $route) {
			if ($i===$lastKey) {
				break;
				}
			$router[$i+1]=$route;
			}
		$router[0]=$wlRouter;
	}
}
