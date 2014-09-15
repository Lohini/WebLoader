<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

use Nette\Application;

/**
 * @author Lopo <lopo@lohini.net>
 */
class WebLoaderRoute
extends \Nette\Application\Routers\Route
{
	/** @var string */
	protected static $path;


	/**
	 * Constructs absolute URL from Request object
	 *
	 * @param \Nette\Application\Request $appRequest
	 * @param \Nette\Http\Url $refUrl
	 * @return string|NULL
	 */
	public function constructUrl(Application\Request $appRequest, \Nette\Http\Url $refUrl)
	{
		if ($appRequest->getPresenterName()!=$this->getTargetPresenter()) {
			return NULL;
			}
		$params=$appRequest->getParameters();
		if (!isset($params['id']) && isset($params[0])) {
			$params['id']=$params[0];
			unset($params[0]);
			$appRequest->setParameters($params);
			}
		return parent::constructUrl($appRequest, $refUrl);
	}

	/**
	 * @param \Nette\Application\IRouter $router
	 * @param WebLoaderRoute $wlRouter
	 * @throws \Nette\Utils\AssertionException
	 */
	public static function prependTo(Application\IRouter &$router, self $wlRouter)
	{
		if (!$router instanceof Application\Routers\RouteList) {
			throw new \Nette\Utils\AssertionException(
				'If you want to use Lohini/WebLoader then your main router '
				.'must be an instance of Nette\Application\Routers\RouteList'
				);
			}
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
