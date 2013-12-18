<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2013 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader;

use Nette\Utils\Html,
	Nette\Diagnostics\Debugger;

/**
 * CssLoader
 *
 * @author Jan Marek
 * @license MIT
 * @author Lopo <lopo@lohini.net>
 */
class CssLoader
extends WebLoader
{
	/** @var bool */
	private $absolutizeUrls=TRUE;


	/**
	 * @param IContainer $parent
	 * @param string $name
	 */
	public function __construct(\Nette\ComponentModel\IContainer $parent=NULL, $name=NULL)
	{
		parent::__construct($parent, $name);
		$this->generatedFileNamePrefix='cssldr-';
		$this->generatedFileNameSuffix='.css';
		$this->sourcePath=WWW_DIR.'/css';
		$this->sourceUri=$this->baseUrl.'css/';
		$this->contentType='text/css';
		$this->preFileFilters[]=new Filters\BOMFilter;
//		$this->preFileFilters[]=new Filters\ScssFilter;
//		$this->preFileFilters[]=new Filters\LessFilter;
		$this->preFileFilters[]=new Filters\CCssFilter;
//		$this->preFileFilters[]=new Filters\XCssFilter;
		$this->preFileFilters[]=new Filters\SassFilter;
//		$this->fileFilters[]=new CssUrlsFilter;
		$this->fileFilters[]=new Filters\CssCharsetFilter;
	}

	/**
	 * @return string
	 */
	public function getMedia()
	{
		return $this->media;
	}

	/**
	 * @param string $media
	 * @return CssLoader (fluent)
	 */
	public function setMedia($media)
	{
		$this->media=$media;
		return $this;
	}

	/**
	 * Set URL absolutization on/off
	 *
	 * @param bool $abs
	 * @return CssLoader (fluent)
	 */
	public function setAbsolutizeUrls($abs)
	{
		$this->absolutizeUrls=(bool)$abs;
		return $this;
	}

	/**
	 * @see Lohini\WebLoader.WebLoader::addFile()
	 */
	public function addFile($file, $media='all')
	{
		foreach ($this->files as $f) {
			if ($f[0]==$file) {
				return;
				}
			}
		if (!file_exists("$this->sourcePath/$file")) {
			if ($this->throwExceptions) {
				if ($this->getPresenter(FALSE)->context->params['productionMode']) {
					throw new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist.");
					}
				else {
					Debugger::log(new \Nette\FileNotFoundException("File '$this->sourcePath/$file' doesn't exist."), Debugger::ERROR);
					return;
					}
				}
			}
		$this->files[]=[$file, $media];
	}

	/**
	 * @see Lohini\WebLoader.WebLoader::renderFiles()
	 */
	public function renderFiles()
	{
		$ctx=$this->getPresenter(FALSE)->context;
		if ($this->enableDirect
			&& count($this->files)==1
			&& substr($this->files[0][0], -4)=='.css'
			) { // single raw, don't parse|cache
			echo $this->getElement($this->sourceUri.$this->files[0][0], $this->files[0][1]);
			return;
			}
		$filesByMedia=[];
		foreach ($this->files as $f) {
			$filesByMedia[$f[1]][]=$f[0];
			}
		foreach ($filesByMedia as $media => $filenames) {
			if ($this->joinFiles) {
				echo $this->getElement($this->getPresenter()->link(':Lohini:WebLoader:', $this->generate($filenames)), $media);
				}
			else {
				foreach ($filenames as $filename) {
					echo $this->getElement($this->sourceUri.$filename, $media);
					}
				}
			}
	}

	/**
	 * @see Lohini\WebLoader.WebLoader::getElement()
	 */
	public function getElement($source, $media='all')
	{
		return Html::el('link')
				->rel('stylesheet')
				->media($media)
				->href($source);
	}

	/**
	 * Generates compiled+compacted file and render link
	 *
	 * @example {control css:compact 'file.css', 'file2.css'}
	 */
	public function renderCompact()
	{
		if (($hasArgs=(func_num_args()>0)) && func_num_args()==1) {
			$arg=func_get_arg(0);
			$file= is_array($arg)? key($arg) : $arg;
			$media= is_array($arg)? $arg[$file] : 'all';
			if ($this->enableDirect && strtolower(substr($file, -4))=='.css') {
				echo $this->getElement($this->sourceUri.$file, $media);
				return;
				}
			}
		if ($hasArgs) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}

		$filesByMedia=[];
		foreach ($this->files as $f) {
			$filesByMedia[$f[1]][]=$f[0];
			}
		foreach ($filesByMedia as $media => $filenames) {
			echo $this->getElement($this->getPresenter()->link(':Lohini:WebLoader:', $this->generate($filenames)), $media);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates compiled files and render links
	 *
	 * @example {control css:singles 'file.css', 'file2.css'}
	 */
	public function renderSingles()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}

		foreach ($this->files as $f) {
			echo ($this->enableDirect && strtolower(substr($f[0], -4))=='.css')
				? $this->getElement($this->sourceUri.$f[0], $f[1])
				: $this->getElement($this->getPresenter()->link(':Lohini:WebLoader:', $this->generate([$f[0]])), $f[1]);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates and render links - no processing
	 *
	 * @example {control css:static 'file.css', 'file2.css'}
	 * @throws \Nette\InvalidStateException
	 */
	public function renderStatic()
	{
		if (!$this->enableDirect) {
			throw new \Nette\InvalidStateException('Static linking not available with disabled direct linking');
			}
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}

		foreach ($this->files as $f) {
			echo $this->getElement($this->sourceUri.$f[0], $f[1]);
			}
		if ($hasArgs) {
			$this->files=$backup;
			}
	}

	/**
	 * Generates link
	 *
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	public function getLink()
	{
		if ($hasArgs=(func_num_args()>0)) {
			$backup=$this->files;
			$this->clear();
			$this->addFiles(func_get_args());
			}
		if ($this->enableDirect
			&& count($this->files)==1
			&& substr($this->files[0][0], -4)=='.css'
			) { // single raw, don't parse|cache
			$link=$this->sourceUri.$this->files[0][0];
			if ($hasArgs) {
				$this->files=$backup;
				}
			return $link;
			}
		$filesByMedia=[];
		foreach ($this->files as $f) {
			$filesByMedia[$f[1]][]=$f[0];
			}
		if (count($filesByMedia)>1) {
			throw new \Nette\InvalidStateException("Can't generate link for combined media.");
			}
		if (!$this->joinFiles) {
			throw new \Nette\InvalidStateException("Can't generate link with disabled joinFiles.");
			}
		$link=$this->getPresenter()->link(':Lohini:WebLoader:', $this->generate($filesByMedia[$f[1]]));
		if ($hasArgs) {
			$this->files=$backup;
			}
		return $link;
	}
}
