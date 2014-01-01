<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters;

/**
 * @author Lopo <lopo@lohini.net>
 */
class SassFilter
extends PreFileFilter
{
	/**
	 * Invoke filter
	 *
	 * @param string $code
	 * @param \Lohini\WebLoader\WebLoader $loader
	 * @param string $file
	 * @return string
	 */
	public function __invoke($code, \Lohini\WebLoader\WebLoader $loader, $file=NULL)
	{
		if ($file===NULL
			|| !in_array(\Nette\Utils\Strings::lower(pathinfo($file, PATHINFO_EXTENSION)), [\PHPSass\File::SASS, \PHPSass\File::SCSS])
			) {
			return $code;
			}
		$so=[
			'style' => \PHPSass\Renderers\Renderer::STYLE_COMPRESSED,
			'syntax' => pathinfo($file, PATHINFO_EXTENSION),
			'load_paths' => [
				dirname($file),
				]
			];
		if (class_exists('PHPSass\Extensions\Compass')) {
			$so['functions']=\PHPSass\Extensions\Compass::getFunctions('Compass');
			$so['extensions']=['Compass'];
			$so['load_paths'][]=dirname(\Nette\Reflection\ClassType::from('PHPSass\Extensions\Compass')->getFileName());
			}
		if (!\Nette\Environment::isProduction()) {
			$so['debug']=TRUE;
			$so['debug_info']=TRUE;
			$so['style']=\PHPSass\Renderers\Renderer::STYLE_NESTED;
			}
		$filter=new \PHPSass\Parser($so);
		return $filter->toCss($file, TRUE);
	}
}

