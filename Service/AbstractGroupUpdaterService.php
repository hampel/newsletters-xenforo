<?php namespace Hampel\Newsletters\Service;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscription;
use XF\Service\AbstractService;

abstract class AbstractGroupUpdaterService extends AbstractService
{
    protected ?Group $group;

    protected string $type;

    protected array $requiredCriteria = [];

    protected function setup()
    {
        parent::setup();

        if (empty($this->type))
        {
            throw new \LogicException('Class must define the type for this service');
        }

        if (empty($this->requiredCriteria))
        {
            throw new \LogicException('Class must define the required criteria for this service');
        }
    }

    public function setGroup(Group $group)
    {
        if ($group->type != $this->type)
        {
            throw new \InvalidArgumentException("Incorrect usergroup type [{$group->type}]");
        }

        foreach ($this->requiredCriteria as $criteria)
        {
            if (!array_key_exists($criteria, $group->criteria))
            {
                throw new \InvalidArgumentException("Required criteria [{$criteria}] not specified");
            }
        }

        $this->group = $group;
    }

    abstract public function updateGroupMembers();

    protected function findOrCreateSubscription($subscriber_id) : Subscription
    {
        $subscription = $this->findOne(Subscription::class, [
            'subscriber_id' => $subscriber_id,
            'group_id' => $this->group->group_id,
        ]);

        if (!$subscription)
        {
            $subscription = $this->em()->create(Subscription::class);
            $subscription->subscriber_id = $subscriber_id;
            $subscription->group_id = $this->group->group_id;
            $subscription->save();
        }

        return $subscription;
    }
}
