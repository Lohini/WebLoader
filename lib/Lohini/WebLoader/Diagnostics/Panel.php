<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Diagnostics;

/**
 * @author Lopo <lopo@lohini.net>
 */
final class Panel
implements \Nette\Diagnostics\IBarPanel
{
	public static $icon="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJ+SURBVBgZBcExbFRlAADg7//fu7teC3elQEoMgeDkYDQ6oMQQTYyGxMHZuDA6Ypw0cWI20cHJUdl0cJLIiomR6OACGhUCpqGWtlzbu/b97/3v9/tCKQVc/e7RRXz+7OrSpUXbW7S9tu8ddv0M+3iCjF1s42v8WAP0XffKi2eOXfro9dMAYJ766SL1092jfDa17DfZgycHfvh7/hau1QB9161PhgE8epoNQlAHqprRIDo3iqoYDSpeOjv2zHRl7atfNj6LALltJys1Xc9+CmYtTxtmR8yO2D7kv4MMPr7x0KULK54/NThdA+S2XTs+jOYN86MsxqBGVRErKkEV6BHynp//2fXbw9lGDZBTWp+OK7PDzqIpYiyqSMxBFakUVYVS2dxrfHHrrz1crQG6lM6vTwZmR0UHhSoHsSBTKeoS9YU8yLrUXfj+w9d2IkBOzfkz05F5KkKkCkFERACEQil0TSOnJkMNV67fHNdVHI4GUcpZVFAUZAEExEibs4P5osMeROiadHoUiIEeCgFREAoRBOMB2weNrkmbNz+9UiBCTs1yrVdHqhgIkRL0EOj7QGG5jrZ2D+XUbADEy9dunOpSun7xuXMe7xUPNrOd/WyeyKUIoRgOGS8xWWZ7b6FLaROgzim9iXd+vXvf7mHtoCnaXDRtkLpel3t9KdamUx+8fcbj7YWc0hZAndv25XffeGH8yfuvAoBcaHOROhS+vLlhecD+wUJu222AOrft/cdPZr65ddfqsbHVyZLVlZHpysjx5aHRMBrV0XuX141qtnb25bb9F6Duu+7b23funb195955nMRJnMAJTJeGg8HS0sBkZWx1suz3Px79iZ8A/gd7ijssEaZF9QAAAABJRU5ErkJggg==";
	private static $files=[];


	public static function register()
	{
		\Tracy\Debugger::addPanel(new self());
	}

	public static function addFile($source, $generated, $memory=NULL)
	{
		if (is_array($source)) {
			foreach ($source as $file) {
				self::$files[$file]=[
					'name' => $generated,
					'memory' => $memory
					];
				}
			}
		else {
			self::$files[$source]=[
				'name' => $generated,
				'memory' => $memory
				];
			}
	}

	private static function link($file)
	{
		//$link = 'editor://open/?file=' . urlencode($file) . '&line=0';
		$link=str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $file);
		$name=str_replace(WWW_DIR, '', $file);
		return '<a href="'.$link.'" target="_blank">'.$name.'</a>';
	}

	/*** IDebugPanel ***/
	public function getTab()
	{
		return '<span><img src="'.self::$icon.'">WebLoader ('.count(self::$files).')</span>';
	}

	public function getPanel()
	{
		$buff='<h1>WebLoader</h1>'
			.'<div class="nette-inner">'
			.'<table>'
			.'<thead><tr><th>Source</th><th>Generated file</th><th>Memory usage</th></tr></thead>';
		$i=0;
		foreach (self::$files as $source => $generated) {
			$buff.='<tr><th'.($i%2? 'class="nette-alt"' : '').'>'
					.self::link($source)
					.'</th><td>'
					.self::link($generated['name'])
					.'</td><td>'
					.\Nette\Templating\Helpers::bytes($generated['memory'])
					.'</td></tr>';
			}
		return $buff.'</table></div>';
	}

	public function getId()
	{
		return 'WebLoader';
	}
}
