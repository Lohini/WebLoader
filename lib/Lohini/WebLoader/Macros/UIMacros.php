<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Macros;

use Nette\Utils\Strings,
	Latte\CompileException;

/**
 * /--code latte
 * {* css *}
 * {css 'style.css'}
 * {* js *}
 * {js 'script.js'}
 * \--
 *
 * @author Lopo <lopo@lohini.net>
 */
class UIMacros
extends \Latte\Macros\MacroSet
{
	/**
	 * @param \Latte\Engine
	 * @return \Latte\Macros\MacroSet
	 */
	public static function factory(\Latte\Engine $engine)
	{
		return static::install($engine->getCompiler());
	}

	/**
	 * @param \Latte\Compiler $compiler
	 * @return UIMacros
	 */
	public static function install(\Latte\Compiler $compiler)
	{
		$set=new static($compiler);
		$set->addMacro('css', [$set, 'macroCss']);
		$set->addMacro('js', [$set, 'macroJs']);
		return $set;
	}

	/**
	 * {css [:renderType] file[=>media][, file2[=>media]]}
	 *
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 * @throws CompileException
	 */
	public function macroCss(\Latte\MacroNode $node, \Latte\PhpWriter $writer)
	{
		$words=$node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing args in {css}");
			}
		$method=isset($words[1]) ? ucfirst($words[1]) : '';
		$method=Strings::match($method, '#^\w*\z#') ? "render$method" : "{\"render$method\"}";
		if (isset($words[0]) && !isset($words[1])) {
			$node->tokenizer->reset();
			}
		$param=$writer->formatArray();
		if (!Strings::contains($node->args, '=>')) {
			$param=substr($param, 6, -1); // removes array()
			}
		return 'if (($_ctrl=$_control->getComponent("css")) instanceof Nette\Application\UI\IRenderable) $_ctrl->redrawControl(NULL, FALSE); '
			.($node->modifiers===''? "\$_ctrl->$method($param)" : $writer->write("ob_start(); \$_ctrl->$method($param); echo %modify(ob_get_clean())"));
	}

	/**
	 * {js [:renderType] file[, file2]}
	 *
	 * @param \Latte\MacroNode $node
	 * @param \Latte\PhpWriter $writer
	 * @return string
	 * @throws CompileException
	 */
	public function macroJs(\Latte\MacroNode $node, \Latte\PhpWriter $writer)
	{
		$words=$node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing args in {js}");
			}
		$method=isset($words[1]) ? ucfirst($words[1]) : '';
		$method=Strings::match($method, '#^\w*\z#') ? "render$method" : "{\"render$method\"}";
		if (isset($words[0]) && !isset($words[1])) {
			$node->tokenizer->reset();
			}
		$param=$writer->formatArray();
		if (!Strings::contains($node->args, '=>')) {
			$param=substr($param, 6, -1); // removes array()
			}
		return 'if (($_ctrl=$_control->getComponent("js")) instanceof Nette\Application\UI\IRenderable) $_ctrl->redrawControl(NULL, FALSE); '
			.($node->modifiers===''? "\$_ctrl->$method($param)" : $writer->write("ob_start(); \$_ctrl->$method($param); echo %modify(ob_get_clean())"));
	}
}
