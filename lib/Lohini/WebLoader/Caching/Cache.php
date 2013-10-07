<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Caching;

use \Lohini\WebLoader\WebLoader;

/**
 * @author Lopo <lopo@lohini.net>
 */
class Cache
extends \Nette\Caching\Cache
{
	/**
	 * Retrieves the specified item from the cache or NULL if the key is not found.
	 *
	 * @param string $key
	 * @return array|NULL
	 */
	public function getItem($key)
	{
		$item=$this->offsetGet($key);
		$content=$item[WebLoader::CONTENT];

		preg_replace_callback(
			'/{\[of#(?P<filter>.*?)#(?P<key>.*?)#cf\]}/m',
			function($matches) use(& $content) {
				$content=str_replace($matches[0], call_user_func("\\Lohini\\WebLoader\\Filters\\{$matches['filter']}Filter::getItem", $matches['key']), $content);
				},
			$content);
		return [
			WebLoader::CONTENT_TYPE => $item[WebLoader::CONTENT_TYPE],
			WebLoader::ETAG => md5($content),
			WebLoader::CONTENT => $content
			];
	}

	/**
	 * Remove all items cached by WebLoader
	 *
	 * @param array $conditions
	 */
	public function clean(array $conditions=NULL)
	{
		parent::clean([self::TAGS => ['webloader']]);
	}
}
