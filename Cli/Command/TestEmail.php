<?php namespace Hampel\Newsletters\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\AbstractCommand;
use XF\Entity\Thread;

class TestEmail extends AbstractCommand
{
	protected function configure()
	{
		$this
			->setName('newsletters:test')
            ->addArgument(
                'thread_id',
                InputArgument::REQUIRED,
                "Thread id to send as an email"
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                "Email address to send to"
            )
			->setDescription('Send test newsletter email');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $thread = \XF::em()->find(Thread::class, intval($input->getArgument('thread_id')));

        if (!$thread)
        {
            $output->writeln("<error>Thread not found</error>");
            return self::FAILURE;
        }

        $output->writeln("Thread: {$thread->title}");

        $options = \XF::options();

        $email = $input->getOption('email') ?: $options->contactEmailAddress ?: $options->defaultEmailAddress;

        $params = [
            'post' => $thread->FirstPost,
            'thread' => $thread,
            'forum' => $thread->Forum,
        ];

        \XF::app()->mailer()->newMail()
            ->setTo($email)
            ->setTemplate("hampel_newsletter_email", $params)
            ->send();

        $output->writeln("<info>Email sent to {$email}</info>");

		return self::SUCCESS;
	}
}