<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Entity\Group;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildNewsletterGroups extends Command
{
	protected function configure()
	{
		$this
			->setName('newsletters:rebuild-groups')
			->setDescription('Rebuild newsletter groups');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: make this into a job?

        $output->writeln('<info>Rebuilding newsletter groups</info>');
        $output->writeln('');

        $groups = \XF::finder(Group::class)
            ->where('builder_id', '!=', null)
            ->with('GroupBuilder', true)
            ->fetch();

        /** @var Group $group */
        foreach ($groups as $group)
        {
            $count = $group->updateSubscribers();

            $output->writeln("{$count} subscribers in group {$group->name} [{$group->GroupBuilder->class}]");
        }

        return self::SUCCESS;
    }
}