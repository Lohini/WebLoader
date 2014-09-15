<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniModule;

use Lohini\WebLoader\WebLoader,
	Nette\Application,
	Nette\Application\Responses,
	Nette\Http;

/**
 * @author Lopo <lopo@lohini.net>
 */
class WebLoaderPresenter
extends \Nette\Object
implements \Nette\Application\IPresenter
{
	/** @var \Nette\DI\Container */
	private $context;
	/** @var \Nette\Http\IRequest */
	private $httpRequest;
	/** @var \Nette\Application\IRouter */
	private $router;
	/** @var \Nette\Application\Request */
	private $request;


	/**
	 * @param \Nette\DI\Container $context
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Application\IRouter $router
	 */
	public function __construct(\Nette\DI\Container $context=NULL, Http\IRequest $httpRequest=NULL, Application\IRouter $router=NULL)
	{
		$this->context=$context;
		$this->httpRequest=$httpRequest;
		$this->router=$router;
	}

	/**
	 * @param \Nette\Application\Request $request
	 * @return \Nette\Application\IResponse
	 * @throws \Nette\Application\BadRequestException
	 */
	public function run(Application\Request $request)
	{
		$this->request=$request;

		if ($this->httpRequest && $this->router && !$this->httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl=clone $this->httpRequest->getUrl();
			$url=$this->router->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));
			if ($url!==NULL && !$this->httpRequest->getUrl()->isEqual($url)) {
				return new Responses\RedirectResponse($url, Http\IResponse::S301_MOVED_PERMANENTLY);
				}
			}

		$params=$request->getParameters();
		if (!isset($params['id'])) {
			throw new Application\BadRequestException('Parameter id is missing.');
			}

		if (NULL===($item=$this->context->getService('webloader.cache')->getItem(\Nette\Utils\Strings::webalize($params['id'])))) {
			return new Responses\TextResponse('');
			}
		return new \Lohini\WebLoader\WebLoaderResponse($item[WebLoader::CONTENT], $item[WebLoader::CONTENT_TYPE], $item[WebLoader::ETAG]);
	}
}
