<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Entity\GroupBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUsergroupBuilder extends Command
{
	protected function configure()
	{
		$this
			->setName('newsletters:create-usergroup-builder')
			->setDescription('Re-create the usergroup builder if it accidentally gets deleted');
    }

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupBuilder = \XF::em()->findOne(GroupBuilder::class, ['builder_id', 'usergroup']);
        if (!$groupBuilder)
        {
            \XF::app()->addOnManager()->getById('Hampel/Newsletters')->getSetup()->addGroupBuilder();
        }

        return Command::SUCCESS;
    }
}