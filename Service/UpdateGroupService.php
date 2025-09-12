<?php namespace Hampel\Newsletters\Service;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscription;
use XF\Entity\User;
use XF\Service\AbstractService;

class UpdateGroupService extends AbstractService
{
    protected ?Group $group = null;

    public function setGroup(Group $group)
    {
        if ($group->type != 'usergroup')
        {
            throw new \InvalidArgumentException("Can only use UpdateGroupService with usergroup type groups");
        }

        if (empty($group->criteria['usergroups']))
        {
            throw new \InvalidArgumentException("No usergroups specified");
        }

        $this->group = $group;
    }

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
                $subscription = $this->finder(Subscription::class)
                    ->where('subscriber_id', $subscriber->subscriber_id)
                    ->where('group_id', $this->group->group_id)
                    ->fetchOne();

                if (!$subscription)
                {
                    $subscription = $this->em()->create(Subscription::class);
                    $subscription->subscriber_id = $subscriber->subscriber_id;
                    $subscription->group_id = $this->group->group_id;
                    $subscription->save();
                }
            }
        }
    }
}
