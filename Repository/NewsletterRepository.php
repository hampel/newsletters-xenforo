<?php namespace Hampel\Newsletters\Repository;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscriber;
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
            'email_confirm' => \XF::phrase('newsletters_email_confirm'),
            'unsubscribed' => \XF::phrase('newsletters_unsubscribed'),
            'email_bounce' => \XF::phrase('newsletters_email_bounce'),
            'spam_complaint' => \XF::phrase('newsletters_spam_complaint'),
            'rejected' => \XF::phrase('newsletters_rejected'),
            'disabled' => \XF::phrase('newsletters_disabled'),
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

    public function getGroupTypes() : array
    {
        return [
            'manual' => \XF::phrase('newsletters_manual'),
            'usergroup' => \XF::phrase('newsletters_usergroup'),
            'joinable' => \XF::phrase('newsletters_joinable'),
        ];
    }
}
