<?php namespace Hampel\Newsletters\Repository;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscriber;
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

    public function getAllGroupsKeyedByType(array $with = []) : AbstractCollection
    {
        return $this->finder(Group::class)
            ->with($with)
            ->order('name', 'ASC')
            ->fetch()
            ->groupBy('type');
    }

    public function getGroupTypes() : array
    {
        return [
            'manual' => \XF::phrase('newsletters_manual'),
            'usergroup' => \XF::phrase('newsletters_usergroup'),
            'joinable' => \XF::phrase('newsletters_joinable'),
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

}
