<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

/**
 * Lohini wrapping class for Pawlik's xCSS
 * @link http://xcss.antpaw.org/
 * @author Lopo <lopo@lohini.net>
 */
class XCssFilter
extends PreFileFilter
{
	/**
	 * Check if we have Pawlik's xCSS
	 *
	 * @throws \Nette\NotSupportedException
	 */
	public function __construct()
	{
		if (!in_array('xCSS', get_declared_classes()) && !class_exists('xCSS')) {
			throw new \Nette\NotSupportedException("Don't have Pawlik's xCSS");
			}
	}

	/**
	 * @see PreFileFilter::__invoke()
	 */
	public static function __invoke($code, \Lohini\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL || strtolower(pathinfo($file, PATHINFO_EXTENSION))!='xcss') {
			return $code;
			}
		$config=[
			'path_to_css_dir' => WWW_DIR.'/css',
			'xCSS_files' => [],
			];
		define('XCSSCLASS', '');
		$filter=new \xCSS($config);
		return $filter->compile(file_get_contents($file));
	}
}
