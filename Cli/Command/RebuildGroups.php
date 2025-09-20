<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscription;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildGroups extends Command
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

        $output->writeln('<info>Cleaning up orphaned subscriptions</info>');

        $subscriptions = \XF::finder(Subscription::class)
            ->with(['Subscriber', 'Group'])
            ->whereOr([
                ['Subscriber.subscriber_id', null],
                ['Group.group_id', null]
            ])
            ->fetch();

        $count = 0;
        foreach ($subscriptions as $subscription)
        {
            $subscription->delete();
            $count++;
        }

        $output->writeln("Removed {$count} orphaned subscriptions");
        $output->writeln('');

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