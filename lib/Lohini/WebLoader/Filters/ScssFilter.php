<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

/**
 * Lohini wrapping class for Leafo's scssphp
 * @link https://github.com/leafo/scssphp
 * @author Lopo <lopo@lohini.net>
 */
class ScssFilter
extends PreFileFilter
{
	/**
	 * Check if we have Leafo's scssc
	 *
	 * @throws \Nette\NotSupportedException
	 */
	public function __construct()
	{
		if (!in_array('scssc', get_declared_classes()) && !class_exists('scssc')) {
			throw new \Nette\NotSupportedException("Don't have Leafo's scssc");
			}
	}

	/**
	 * @see PreFileFilter::__invoke()
	 */
	public function __invoke($code, \Lohini\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL || strtolower(pathinfo($file, PATHINFO_EXTENSION))!='scss') {
			return $code;
			}
		$filter=new \scssc($file);
		return $filter->compile();
	}
}
