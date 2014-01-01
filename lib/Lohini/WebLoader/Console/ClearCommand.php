<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Console;

use Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Lopo <lopo@lohini.net>
 */
class ClearCommand
extends \Symfony\Component\Console\Command\Command
{
	/** @var \Nette\DI\Container */
	private $serviceLocator;


	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
        $this
            ->setName('lohini:webloader-clear')
            ->setDescription('Clear WebLoader cache')
//			->addOption('css', 'c', InputOption::VALUE_OPTIONAL, 'Clear only css')
//			->addOption('js', 'j', InputOption::VALUE_OPTIONAL, 'Clear only js')
//			->addOption('all', 'a', InputOption::VALUE_OPTIONAL, 'Clear all')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		$this->serviceLocator=$this->getHelper('container')->getContainer();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->serviceLocator->{'webloader.cache'}->clean();
		return 0;
	}
}
