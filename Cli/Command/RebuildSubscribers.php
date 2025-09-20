<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Entity\Subscriber;
use Hampel\Newsletters\Job\SubscriberRebuild;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\JobRunnerTrait;

class RebuildSubscribers extends Command
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
        $subscribers = \XF::finder(Subscriber::class)
            ->where('user_id', '!=', null)
            ->with('User')
            ->fetch();
        foreach ($subscribers as $subscriber)
        {
            if (!$subscriber->User)
            {
                // user no longer exists, remove them
                $output->writeln("Removing subscriber {$subscriber->email} because associated user no longer exists [{$subscriber->description}]");
                $subscriber->delete();
            }
        }

        $this->setupAndRunJob(
            'newslettersSubscriberRebuild',
            SubscriberRebuild::class,
            [],
            $output
        );

        return Command::SUCCESS;
    }
}