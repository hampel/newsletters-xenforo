<?php namespace Hampel\Newsletters\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command
{
	protected function configure()
	{
		$this
			->setName('newsletters:install')
			->setDescription('Install tables')
            ->addOption(
                'uninstall',
                'u',
                InputOption::VALUE_NONE,
                'Remove tables'
            );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('uninstall'))
        {
            \XF::app()->addOnManager()->getById('Hampel/Newsletters')->getSetup()->uninstall();
        }
        else
        {
            \XF::app()->addOnManager()->getById('Hampel/Newsletters')->getSetup()->install();
        }

        return Command::SUCCESS;
    }
}