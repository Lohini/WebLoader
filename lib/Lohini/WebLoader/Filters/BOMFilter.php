<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

/**
 * Remove BOM Flags from files
 *
 * @author Mgr. Martin Jantošovič <martin.jantosovic@freya.sk>
 */
class BOMFilter
extends \Nette\Object
{
	const BOM='\xEF\xBB\xBF';


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
		return \Nette\Utils\Strings::replace($code, '/'.self::BOM.'/');
	}
}
