<?php

namespace Hampel\Newsletters;

use XF\AddOn\AbstractSetup;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{

    // ################################ CHECK REQUIREMENTS ####################

//    public function checkRequirements(&$errors = [], &$warnings = [])
//    {
//        $vendorDirectory = sprintf("%s/vendor", $this->addOn->getAddOnDirectory());
//        if (!file_exists($vendorDirectory))
//        {
//            $errors[] = "vendor folder does not exist - cannot proceed with addon install";
//        }
//    }

    // ################################ INSTALLATION ###########################

	public function install(array $stepParams = [])
	{
        $this->schemaManager()->createTable('xf_newsletters_subscriber', function (Create $table) {
            $table->addColumn('subscriber_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('email', 'varchar', 120);
            $table->addColumn('user_id', 'int')->unsigned()->nullable();
            $table->addColumn('status', 'enum')->values(['active', 'invalid', 'unsubscribed']);
            $table->addColumn('created_date', 'int')->unsigned();
            $table->addColumn('source', 'enum')->values(['import', 'webform', 'user']);
            $table->addColumn('signup_date', 'int')->unsigned();
            $table->addColumn('signup_ip', 'varbinary', 16)->nullable();
            $table->addColumn('confirmation_date', 'int')->unsigned()->nullable();
            $table->addColumn('confirmation_ip', 'varbinary', 16)->nullable();
            $table->addUniqueKey('email');
            $table->addKey('status');
        });

        $this->schemaManager()->createTable('xf_newsletters_subscription', function (Create $table) {
            $table->addColumn('subscription_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('subscriber_id', 'int');
            $table->addColumn('group_id', 'int');
            $table->addKey('subscriber_id');
            $table->addKey('group_id');
        });

        $this->schemaManager()->createTable('xf_newsletters_group', function (Create $table) {
            $table->addColumn('group_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('name', 'varchar', 128);
            $table->addColumn('type', 'enum')->values(['manual', 'usergroup', 'joinable']);
            $table->addColumn('created_date', 'int')->unsigned();
        });

        $this->schemaManager()->createTable('xf_newsletters_map', function (Create $table) {
            $table->addColumn('map_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('list_id', 'int')->unsigned();
            $table->addColumn('group_id', 'int')->unsigned();
            $table->addKey('list_id');
            $table->addKey('group_id');
        });

        $this->schemaManager()->createTable('xf_newsletters_list', function (Create $table) {
            $table->addColumn('list_id', 'int')->unsigned()->autoIncrement();
            $table->addColumn('name', 'varchar', 128);
            $table->addColumn('created_date', 'int')->unsigned();
        });

	}

    // ################################ FINAL INSTALL ACTIONS ##########################

    public function postInstall(array &$stateChanges)
    {
        // rebuild subscribers
        $this->app->jobManager()->enqueueUnique(
            'newslettersSubscriberRebuild',
            SubscriberRebuild::class
        );
    }

    // ################################ UPGRADE ##################

	public function upgrade(array $stepParams = [])
	{
		// TODO: Implement upgrade() method.
	}

    // ################################ FINAL UPGRADE ACTIONS ##########################

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        if (\XF::$versionId >= 2030000) { // XF 2.3+
            $this->enqueuePostUpgradeCleanUp();
        }
    }

    // ################################ UNINSTALL #######################################

	public function uninstall(array $stepParams = [])
	{
        $this->schemaManager()->dropTable('xf_newsletters_subscriber');
        $this->schemaManager()->dropTable('xf_newsletters_subscription');
        $this->schemaManager()->dropTable('xf_newsletters_group');
        $this->schemaManager()->dropTable('xf_newsletters_map');
        $this->schemaManager()->dropTable('xf_newsletters_list');
	}
}