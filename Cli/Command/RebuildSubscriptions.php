<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Job\SubscriptionRebuild;
use Hampel\Newsletters\Traits\RepositoryAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\JobRunnerTrait;

class RebuildSubscriptions extends AbstractLoggingCommand
{
    use JobRunnerTrait;

	protected function configure()
	{
		$this
			->setName('newsletters:rebuild-subscriptions')
			->setDescription('Rebuild newsletter groups');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->line('Cleaning up orphaned subscriptions');

        $count = $this->repo->deleteInvalidSubscriptions();

        $this->line("Removed {$count} orphaned subscriptions");
        $this->newLine();

        $this->line('Rebuilding newsletter groups');

        $this->setupAndRunJob(
            'newslettersSubscriptionRebuild',
            SubscriptionRebuild::class,
            [],
            $output
        );

        return self::SUCCESS;
    }
}