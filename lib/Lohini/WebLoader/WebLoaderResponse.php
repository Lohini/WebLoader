<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

/**
 * @author Lopo <lopo@lohini.net>
 */
class WebLoaderResponse
extends \Nette\Object
implements \Nette\Application\IResponse
{
	/** @var string */
	private $content;
	/** @var string */
	private $contentType;
	/** @var string */
	private $etag;


	/**
	 * @param string $content
	 * @param string $contentType
	 * @param string $etag
	 */
	public function __construct($content, $contentType, $etag=NULL)
	{
		$this->content=$content;
		$this->contentType=$contentType;
		$this->etag=$etag;
	}

	/**
	 * @return string
	 */
	final public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return string
	 */
	final public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * @return string
	 */
	final public function getEtag()
	{
		return $this->etag;
	}

	/**
	 * Sends response to output.
	 *
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 */
	public function send(\Nette\Http\IRequest $httpRequest, \Nette\Http\IResponse $httpResponse)
	{
		if (strlen($this->etag)) {
			$httpResponse->setHeader('Etag', $this->etag);
			}
		$httpResponse->setExpiration(\Nette\Http\IResponse::PERMANENT);
		if (($inm=$httpRequest->getHeader('if-none-match')) && $inm==$this->etag) {
			$httpResponse->setCode(\Nette\Http\IResponse::S304_NOT_MODIFIED);
			}
		$httpResponse->setContentType($this->contentType);
		echo $this->content;
	}
}
