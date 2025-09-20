<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Entity\Subscription;
use Hampel\Newsletters\Repository\NewsletterRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ClearSubscriptions extends Command
{
	protected function configure()
	{
		$this
			->setName('newsletters:clear-subscriptions')
			->setDescription('Remove all subscriptions')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force processing without verification'
            );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = \XF::em();
        $repo = \XF::repository(NewsletterRepository::class);

        if (!$input->getOption('force'))
        {
            $subscriptions = $em->getFinder(Subscription::class)->total();

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $output->writeln("<question>Purge {$subscriptions} subscriptions ?</question>");
            $output->writeln("<warning>Caution: cannot be undone</warning>");
            $question = new Question("<info>type yes to continue or any other key to abort: </info>");
            $continue = $helper->ask($input, $output, $question);
            $output->writeln("");

            if (!in_array($continue, ['yes', 'Yes', 'YES']))
            {
                $output->writeln("Aborting");
                return Command::SUCCESS;
            }
        }

        $repo->deleteAllSubscriptions();

        return Command::SUCCESS;
    }
}