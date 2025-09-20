<?php namespace Hampel\Newsletters\Cli\Command;

use Carbon\Carbon;
use Hampel\Newsletters\Repository\NewsletterRepository;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MailerLiteImport extends Command
{
	protected function configure()
	{
		$this
			->setName('newsletters:mailerlite-import')
			->setDescription('Import subscribers from MailerLite')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                "Path to read file from"
            )
            ->addOption(
                'unsubscribed',
                'u',
                InputOption::VALUE_NONE,
                'Import unsubscribed users'
            )
            ->addOption(
                'bounced',
                'b',
                InputOption::VALUE_NONE,
                'Import bounced users'
            );
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (!is_readable($file))
        {
            $output->writeln("<error>Error: No such file or file unreadable</error>");
            return Command::INVALID;
        }

        $data = str_replace("\n", PHP_EOL, file_get_contents($file));

        $csv = Reader::createFromString($data);
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();

        $requiredHeaders = [
            'Subscriber',
            'Subscribed',
            'Name',
        ];

        foreach ($requiredHeaders as $required)
        {
            if (!in_array($required, $header))
            {
                $output->writeln("<error>Error: invalid MailerList CSV file - does not contain required header [{$required}]</error>");
                return Command::FAILURE;
            }
        }

        if ($input->getOption('unsubscribed'))
        {
            $status = 'unsubscribed';
        }
        elseif ($input->getOption('bounced'))
        {
            $status = 'invalid';
        }
        else
        {
            $status = 'active';
        }

        $output->writeln("<info>Importing {$status} subscribers from MailerLite</info>");

        $imported = 0;
        $skipped = 0;

        $records = $csv->getRecords();

        foreach ($records as $record)
        {
            $email = $record['Subscriber'];

            $name = $record['Name'];
            if (!empty($record['Last name']))
            {
                $name .= " {$record['Last name']}";
            }

            $nameOrEmail = $name ?? $email;

            $subscriber = \XF::repository(NewsletterRepository::class)->findOrCreateSubscriberByEmail($email);
            if ($subscriber->isInsert())
            {
                $subscriber->status = $status;
                $subscriber->source = 'import';
                $subscriber->description = "MailerLite import: {$nameOrEmail}";
                $subscriber->signup_date = Carbon::createFromFormat("Y-m-d H:i:s", $record['Subscribed'])->timestamp;;
                $subscriber->save();

                $output->writeln("Imported subscriber: {$nameOrEmail}");
                $imported++;
            }
            else
            {
                $output->writeln("Skipped existing subscriber: {$nameOrEmail}");
                $skipped++;
            }

        }

        $output->writeln("<info>{$imported} subscribers imported, {$skipped} existing subscribers skipped</info>");

        return Command::SUCCESS;
    }
}