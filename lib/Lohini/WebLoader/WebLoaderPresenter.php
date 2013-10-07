<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniModule;

use Lohini\WebLoader\WebLoader,
	Nette\Utils\Strings,
	Nette\Application\Responses;

/**
 * @author Lopo <lopo@lohini.net>
 */
class WebLoaderPresenter
extends \NetteModule\MicroPresenter
{
	/**
	 * @return Nette\Application\IResponse
	 */
	public function run(\Nette\Application\Request $request)
	{
		$httpRequest=$this->context->getByType('Nette\Http\IRequest');
		if (!$httpRequest->isAjax() && ($request->isMethod('get') || $request->isMethod('head'))) {
			$refUrl=clone $httpRequest->getUrl();
			$url=$this->context->getService('router')->constructUrl($request, $refUrl->setPath($refUrl->getScriptPath()));
			if ($url!==NULL && !$httpRequest->getUrl()->isEqual($url)) {
				return new Responses\RedirectResponse($url, \Nette\Http\IResponse::S301_MOVED_PERMANENTLY);
				}
			}

		$params=$request->getParameters();
		if (!isset($params['id'])) {
			throw new \Nette\Application\BadRequestException('Parameter id is missing.');
			}

		if (NULL===($item=$this->context->getService('webloader.cache')->getItem(Strings::webalize($params['id'])))) {
			return new Responses\TextResponse('');
			}
		return new \Lohini\WebLoader\WebLoaderResponse($item[WebLoader::CONTENT], $item[WebLoader::CONTENT_TYPE], $item[WebLoader::ETAG]);
	}
}
