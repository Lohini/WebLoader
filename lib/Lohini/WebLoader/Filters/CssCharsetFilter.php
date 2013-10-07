<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

use Nette\Utils\Strings;

/**
 * Remove all @charset 'utf8' and write only one at beginning of the file
 *
 * @author Mgr. Martin Jantošovič <martin.jantosovic@freya.sk>
 */
class CssCharsetFilter
{
	const CHARSET='@charset "utf-8";';


	/**
	 * Invoke filter
	 *
	 * @param string code
	 * @param WebLoader loader
	 * @param string file
	 * @return string
	 */
	public function __invoke($code, \Lohini\WebLoader\WebLoader $loader, $file=NULL)
	{
		$regexp='/@charset ["\']utf\-8["\'];(\n)?/i';
		$removed=Strings::replace($code, $regexp);
		// At least one charset was in the code
		if (Strings::length($removed)<Strings::length($code)) {
			$code=self::CHARSET."\n".$removed;
			}

		return $code;
	}
}
