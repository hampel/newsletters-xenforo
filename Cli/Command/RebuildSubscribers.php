<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Job\SubscriberRebuild;
use Hampel\Newsletters\Traits\RepositoryAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\JobRunnerTrait;

class RebuildSubscribers extends AbstractLoggingCommand
{
    use JobRunnerTrait;

	protected function configure()
	{
		$this
			->setName('newsletters:rebuild-subscribers')
			->setDescription('Rebuild subscriber data from currently registered users');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->line("Cleaning up subscribers of deleted users");

        $count = $this->repo->deleteSubscribersOfDeletedUsers();

        $this->line("Removed {$count} invalid subscribers");
        $this->newLine();

        $this->line('Rebuilding subscribers from existing users');

        $this->setupAndRunJob(
            'newslettersSubscriberRebuild',
            SubscriberRebuild::class,
            [],
            $output
        );

        return Command::SUCCESS;
    }
}