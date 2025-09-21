<?php namespace Hampel\Newsletters\Cron;

use Hampel\Newsletters\Job\SubscriberRebuild;
use Hampel\Newsletters\Job\SubscriptionRebuild;
use Hampel\Newsletters\Repository\NewsletterRepository;

class Newsletters
{
    public static function RebuildSubscribers()
    {
        \XF::repository(NewsletterRepository::class)->deleteSubscribersOfDeletedUsers();

        \XF::app()->jobManager()->enqueueUnique('newslettersSubscriberRebuild', SubscriberRebuild::class, [], false);
    }

    public static function RebuildSubscriptions()
    {
        \XF::repository(NewsletterRepository::class)->deleteInvalidSubscriptions();

        \XF::app()->jobManager()->enqueueUnique('newslettersSubscriptionRebuild', SubscriptionRebuild::class, [], false);
    }
}
