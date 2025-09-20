<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Service\UsergroupGroupBuilderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

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
//        if ($input->getOption('uninstall'))
//        {
//            \XF::app()->addOnManager()->getById('Hampel/Newsletters')->getSetup()->uninstall();
//        }
//        else
//        {
//            \XF::app()->addOnManager()->getById('Hampel/Newsletters')->getSetup()->install();
//        }

        \XF::db()->getSchemaManager()->alterTable('xf_newsletters_subscriber', function (Alter $table) {
//            $table->addColumn('description', 'varchar', 255)->setDefault('')->after('user_id');
//            $table->changeColumn('description', 'varchar', 255);
        });

        \XF::db()->getSchemaManager()->alterTable('xf_newsletters_group', function (Alter $table) {
//            $table->addColumn('subscriber_count', 'int')->unsigned()->after('parameters');
//            $table->addColumn('description', 'varchar', 255)->setDefault('')->after('name');
//            $table->changeColumn('description', 'varchar', 255);
        });

//        \XF::db()->getSchemaManager()->alterTable('xf_newsletters_group_builder', function (Alter $table) {
//            $table->dropColumns('description');
//        });

//        \XF::app()->addOnManager()->getById('Hampel/Newsletters')->getSetup()->addGroupBuilder();

        return Command::SUCCESS;
    }
}