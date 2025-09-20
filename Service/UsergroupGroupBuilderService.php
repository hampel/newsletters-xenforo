<?php namespace Hampel\Newsletters\Service;

use Hampel\Newsletters\Entity\Group;
use XF\Entity\User;

class UsergroupGroupBuilderService extends AbstractGroupBuilderService
{
    protected string $type = 'usergroup';

    protected array $requiredParameters = ['usergroups'];

    protected array $usergroups = [];

    public function setGroup(Group $group)
    {
        parent::setGroup($group);

        $this->usergroups = $this->parameters['usergroups'];
    }

    public function updateSubscriptions()
    {
        foreach ($this->getSubscriptions() as $subscription)
        {
            $user = $subscription->Subscriber->User;
            if ($user && !$user->isMemberOf($this->group->parameters['usergroups']))
            {
                // user is no longer part of the user group, remove their subscription to this group
                $subscription->delete();
            }
        }

        $finder = $this->finder(User::class);
        $columnName = $finder->columnSqlName('secondary_group_ids');

        $expressions = [];
        foreach ($this->usergroups as $userGroup)
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
