<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;
/**
 * WebLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@lohini.net>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Caching\Cache,
	Nette\Utils\Strings,
	Lohini\WebLoader\Filters\PreFileFilter;

abstract class WebLoader
extends \Nette\Application\UI\Control
{
	/**#@+ cache content */
	const CONTENT_TYPE='contentType';
	const CONTENT='content';
	const ETAG='Etag';
	/**#@-*/
	/** @var string */
	protected $sourcePath;
	/** @var string */
	protected $baseUrl;
	/** @var string */
	protected $sourceUri;
	/** @var bool */
	protected $joinFiles=TRUE;
	/** @var string */
	protected $generatedFileNamePrefix='wldr-';
	/** @var string */
	protected $generatedFileNameSuffix='';
	/** @var bool */
	public $throwExceptions=FALSE;
	/** @var array */
	public $filters=[];
	/** @var array */
	public $preFileFilters=[];
	/** @var array */
	public $fileFilters=[];
	/** @var array */
	protected $files=[];
	/** @var string */
	protected $contentType;
	/** @var bool */
	protected $enableDirect=TRUE;


	/**
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param string $name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->baseUrl=$this->getPresenter(FALSE)->context->getService('httpRequest')->getUrl()->getBaseUrl();
	}

	/**
	 * Get html element including generated content
	 *
	 * @param string $source
	 * @return Html
	 */
	abstract public function getElement($source);

	/**
	 * Process files and render elements including generated content
	 *
	 * @return Html
	 */
	abstract public function renderFiles();

	/**
	 * Add file
	 *
	 * @param string $file filename
	 * @param mixed $mixed
	 */
	abstract public function addFile($file, $mixed);

	/**
	 * Generates link
	 */
	abstract public function getLink();


	/**
	 * Generate compiled file(s) and render link(s)
	 */
	public function render()
	{
		$hasArgs=func_num_args()>0;
		if ($hasArgs) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		$this->renderFiles();
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Set source path
	 *
	 * @param string $sourcePath
	 * @return WebLoader (fluent)
	 * @throws \Nette\FileNotFoundException
	 */
	public function setSourcePath($sourcePath)
	{
		if (FALSE===($sourcePath=realpath($sourcePath))) {
			throw new \Nette\FileNotFoundException("Source path '$sourcePath' doesn't exist.");
			}
		$this->sourcePath=$sourcePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSourcePath()
	{
		return $this->sourcePath;
	}

	/**
	 * @param string $sourceUri
	 * @return WebLoader (fluent)
	 */
	public function setSourceUri($sourceUri)
	{
		$this->sourceUri=(string)$sourceUri;
		return $this;
	}

	/**
	 * Set joining of files
	 *
	 * @param bool $join
	 * @return WebLoader (fluent)
	 */
	public function setJoinFiles($join)
	{
		$this->joinFiles=(bool)$join;
		return $this;
	}

	/**
	 * @param bool $enable
	 * @return WebLoader (fluent)
	 */
	public function setEnableDirect($enable=TRUE)
	{
		$this->enableDirect=(bool)$enable;
		return $this;
	}

	/**
	 * Remove all files
	 */
	public function clear()
	{
		$this->files=[];
	}

	/**
	 * Add files
	 *
	 * Three ways how to set css files.
	 * 1. Media is not set, this type of files will be packed/minimized to file with media = screen,
	 *
	 *		{assign css=>[
	 *						'web/screen.css',
	 *						'web/menu.css',
	 *						]
	 *			}
	 * 2. Media is set, files will be separated by media, there will be to much packs as much is types of media (every pack will be minimized),
	 *		{assign css=>[
	 *						'web/screen.css'=>'screen,projection,tv',
	 *						'web/print.css'=>'print',
	 *						]
	 *			}
	 * 3. You can combine ways.
	 *		{assign css=>[
	 *						'web/screen.css',
	 *						'web/print.css'=>'print',
	 *						]
	 *			}
	 * {$control['css']->addFiles($css)}
	 *
	 * At the end you can render all saved files with widget
	 *		{control css}
	 *
	 * Alternatively you can render files directly, the same result like the lines above is:
	 *		{control css 'web/screen.css'}
	 *		{control css 'web/screen.css', 'web/menu.css'}
	 *		{control css 'web/screen.css', 'web/print.css'=>'print'}
	 *		{control css 'web/screen.css'=>'screen,projection,tv', 'web/print.css'=>'print'}
	 *
	 * But in this case you render files set only in render, not the before saved files from presenter etc.
	 *
	 * Adding of javascript files is similar, but if there is not set type of processing, there is automaticaly set default type COMPACT, actually it means compact without minimizing.
	 *		{assign js=>[
	 *					'datagrid.js',
	 *					'mootools.nette.js',
	 *					]
	 *			}
	 *		{assign js=>[
	 *					'datagrid.js',
	 *					'mootools.nette.js'=>JSLoader::MINIFY,
	 *					]
	 *			}
	 *		{$control['js']->addFiles($js)}
	 *
	 *		{control js}
	 *
	 *		{control js 'datagrid.js'}
	 *		{control js 'datagrid.js', 'jquery.nette.js'}
	 *		{control js 'datagrid.js', 'mootools.nette.js'=>JSLoader::MINIFY}
	 *
	 * @param array $files list of files
	 */
	public function addFiles(array $files)
	{
		foreach ($files as $k => $v) {
			if (is_int($k)) {
				if (is_string($v)) {
					$this->addFile($v);
					}
				elseif (is_array($v)) {
					foreach ($v as $k1 => $v1) {
						if (is_int($k1)) {
							$this->addFile($v1);
							}
						elseif (is_string($k1)) {
							$this->addFile($k1, $v1);
							}
						}
					}
				}
			elseif (is_string($k)) {
				$this->addFile ($k, $v);
				}
			}
	}

	/**
	 * Get last modified timestamp of newest file
	 *
	 * @param array $files
	 * @return int
	 */
	public function getLastModified(array $files=NULL)
	{
		if ($files===NULL) {
			$files=$this->files;
			}
		$modified=0;
		foreach ($files as $file) {
			$modified=max($modified, filemtime($this->sourcePath.'/'.$file));
			}
		return $modified;
	}

	/**
	 * Filename of generated file
	 *
	 * @param array $files
	 * @return string
	 */
	public function getGeneratedFilename(array $files=NULL)
	{
		if ($files===NULL) {
			$files=$this->files;
			}
		$name=substr(md5(implode('|', $files)), 0, 12);
		if (count($files)===1) {
			$name.='-'.pathinfo($files[0], PATHINFO_FILENAME);
			}
		return $this->generatedFileNamePrefix.$name.$this->generatedFileNameSuffix;
	}

	/**
	 * Get joined content of all files
	 *
	 * @param array $files
	 * @return string
	 */
	public function getContent(array $files=NULL)
	{
		if ($files===NULL) {
			$files=$this->files;
			}
		// load content
		$content='';
		foreach ($files as $file) {
			$content.=$this->loadFile($file);
			}
		// apply filters
		foreach ($this->filters as $filter) {
			$content=call_user_func($filter, $content, $this);
			}
		return $content;
	}

	/**
	 * Load content and save cache
	 *
	 * @param array $files
	 * @param mixed $content
	 * @return string filename of generated file
	 */
	protected function generate($files, $content=NULL)
	{
		$cache=$this->getPresenter(FALSE)->context->getService('webloader.cache');

		if (NULL===$cache[$key=Strings::webalize($this->getGeneratedFilename($files))]) {
			$before=memory_get_peak_usage();
			$content= $content ?: $this->getContent($files);
			Diagnostics\Panel::addFile($files, $content, memory_get_peak_usage()-$before);
			$cache->save(
				$key,
				[
					self::CONTENT_TYPE => $this->contentType,
//					self::ETAG => md5($content),//.'-'.dechex(time()),
					self::CONTENT => $content
					],
				[
					Cache::TAGS => ['webloader'],
					Cache::FILES => array_map(function($file) { return $this->sourcePath.'/'.$file; }, $files),
					Cache::CONSTS => [
						'Nette\Framework::REVISION',
						'Lohini\Framework::REVISION'
						],
					]
				);
			$cache->release();
			}
		return $key;
	}

	/**
	 * Load file
	 *
	 * @param string $file filepath
	 * @return string
	 * @throws FileNotFoundException
	 */
	protected function loadFile($file)
	{
		if (FALSE===($content=file_get_contents($this->sourcePath.'/'.$file))) {
			if ($this->throwExceptions) {
				if ($this->getPresenter(FALSE)->context->params['productionMode']) {
					throw new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
					}
				else {
					\Tracy\Debugger::processException(new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."));
					return '';
					}
				}
			return '';
			}
		foreach ($this->preFileFilters as $filter) {
			$fcontent=call_user_func($filter, $content, $this, $this->sourcePath.'/'.$file);
			$content= is_array($fcontent)? $fcontent[PreFileFilter::CONTENT] : $fcontent;
			foreach ($this->fileFilters as $filter) {
				$content=call_user_func($filter, $content, $this, $this->sourcePath.'/'.$file);
				}
			}
		return $content;
	}

	/**
	 * Generates and render link
	 */
	public function renderLink()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		echo $this->getLink();
		if ($hasArgs) {
			$this->files=$backup;
			}
	}
}
