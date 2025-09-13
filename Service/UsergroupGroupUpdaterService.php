<?php namespace Hampel\Newsletters\Service;

use XF\Entity\User;

class UsergroupGroupUpdaterService extends AbstractGroupUpdaterService
{
    protected string $type = 'usergroup';

    protected array $requiredCriteria = ['usergroups'];

    public function updateGroupMembers()
    {
        $subscriptions = $this->group->Subscriptions;

        foreach ($subscriptions as $subscription)
        {
            $user = $subscription->Subscriber->User;
            if ($user && !$user->isMemberOf($this->group->criteria['usergroups']))
            {
                // user is no longer part of the user group, remove their subscription to this group
                $subscription->delete();
            }
        }

        $finder = $this->finder(User::class);
        $columnName = $finder->columnSqlName('secondary_group_ids');

        $expressions = [];
        foreach ($this->group->criteria['usergroups'] as $userGroup)
        {
            $expressions[] = $finder->expression("FIND_IN_SET(" . $finder->quote($userGroup) . ", {$columnName})");
        }

        $users = $finder->whereOr($expressions)->fetch();

        foreach ($users as $user)
        {
            $subscriber = $user->NewsletterSubscriber;
            if ($subscriber)
            {
                $this->findOrCreateSubscription($subscriber->subscriber_id);
            }
        }
    }
}
