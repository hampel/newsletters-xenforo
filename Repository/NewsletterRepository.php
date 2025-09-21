<?php namespace Hampel\Newsletters\Repository;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\GroupBuilder;
use Hampel\Newsletters\Entity\Subscriber;
use Hampel\Newsletters\Entity\Subscription;
use XF\Entity\AddOn;
use XF\Entity\User;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\Entity\Repository;

class NewsletterRepository extends Repository
{
    public function getSubscribers(string $status = 'active', array $with = []) : AbstractCollection
    {
        return $this->finder(Subscriber::class)
            ->with($with)
            ->whereIf($status != 'all', ['status', $status], [])
            ->order('signup_date', 'DESC')
            ->fetch();
    }

    public function getStatusOptions() : array
    {
        return [
            'active' => \XF::phrase('newsletters_active'),
            'invalid' => \XF::phrase('newsletters_invalid'),
            'unsubscribed' => \XF::phrase('newsletters_unsubscribed'),
        ];
    }

    public function getGroups(string $type = 'all', array $with = []) : AbstractCollection
    {
        return $this->finder(Group::class)
            ->with($with)
            ->whereIf($type != 'all', ['type', $type], [])
            ->order('name', 'ASC')
            ->fetch();
    }

    public function getBuilders(string $addon_id = null, array $with = []) : AbstractCollection
    {
        return $this->finder(GroupBuilder::class)
            ->with($with)
            ->whereIf($addon_id != null, ['addon_id', $addon_id], [])
            ->order('name', 'ASC')
            ->fetch();
    }

    public function findBuildersForList(bool $excludeInternal = true)
    {
        return $this->finder(GroupBuilder::class)
            ->whereIf($excludeInternal, ['addon_id', '!=', 'Hampel/Newsletters'], [])
            ->order('name');
    }

    public function getBuilderTitlePairs()
    {
        return $this->findBuildersForList()->fetch()->pluckNamed('name', 'builder_id');
    }

    public function getAllGroupsKeyedByType(array $with = []) : AbstractCollection
    {
        return $this->finder(Group::class)
            ->with($with)
            ->order('name', 'ASC')
            ->fetch()
            ->groupBy('type');
    }

    public function getAddonsForGroupBuilders(array $addon_ids) : AbstractCollection
    {
        return $this->finder(AddOn::class)->where('addon_id', '=', $addon_ids)->fetch();
    }

    public function getGroupTypes() : array
    {
        return [
            'joinable' => \XF::phrase('newsletters_joinable'),
            'manual' => \XF::phrase('newsletters_manual'),
            'usergroup' => \XF::phrase('newsletters_usergroup'),
            'programmatic' => \XF::phrase('newsletters_programmatic'),
        ];
    }

    public function findOrCreateSubscriberByEmail(string $email) : ?Subscriber
    {
        $subscriber = \XF::em()->findOne(Subscriber::class, ['email' => $email], 'User');
        if (!$subscriber)
        {
            $subscriber = \XF::em()->create(Subscriber::class);
            $subscriber->email = $email;
        }

        return $subscriber;
    }

    public function deleteSubscribersOfDeletedUsers() : int
    {
        $subscribers = $this->finder(Subscriber::class)
            ->where('user_id', '!=', null)
            ->with('User')
            ->fetch();

        $count = 0;
        foreach ($subscribers as $subscriber)
        {
            if (!$subscriber->User)
            {
                $subscriber->delete();
                $count++;
            }
        }

        return $count;
    }

    public function deleteInvalidSubscriptions() : int
    {
        $subscriptions = $this->finder(Subscription::class)
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

        return $count;
    }

    public function deleteAllSubscribers()
    {
        $this->db()->emptyTable('xf_newsletters_subscriber');
    }

    public function deleteAllSubscriptions()
    {
        $this->db()->emptyTable('xf_newsletters_subscription');
    }

}
