<?php namespace Hampel\Newsletters\Service;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscriber;
use Hampel\Newsletters\Entity\Subscription;
use XF\Service\AbstractService;

abstract class AbstractGroupBuilderService extends AbstractService
{
    protected ?Group $group;

    protected string $type;

    protected array $parameters = [];

    protected array $requiredParameters = [];

    protected function setup()
    {
        parent::setup();

        if (empty($this->type))
        {
            throw new \LogicException('Class must define the type for this service');
        }

        if (empty($this->requiredParameters))
        {
            throw new \LogicException('Class must define the required parameters for this service');
        }
    }

    public function setGroup(Group $group)
    {
        $errors = $this->validateGroup($group);

        if (!empty($errors))
        {
            throw new \InvalidArgumentException(array_shift($errors));
        }

        $this->group = $group;
        $this->parameters = $group->getParams();
    }

    public function validateGroup(Group $group) : array
    {
        $errors = [];

        if ($group->type != $this->type)
        {
            $errors[] = "Incorrect usergroup type [{$group->type}]";
        }

        foreach ($this->requiredParameters as $parameter)
        {
            if (!array_key_exists($parameter, $group->getParams()))
            {
                $errors[] = "Missing required parameter [{$parameter}]";
            }
        }

        return $errors;
    }

    public function update() : int
    {
        $this->updateSubscriptions();
        $count = $this->countSubscriptions();
        $this->group->subscriber_count = $count;
        $this->group->save();

        return $count;
    }

    abstract protected function updateSubscriptions();

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

    protected function getSubscriptions()
    {
        if (!$this->group)
        {
            throw new \LogicException('Must call setGroup before using this service');
        }

        return $this->group->Subscriptions;
    }

    protected function findSubscriber(string $email) : ?Subscriber
    {
        return $this->findOne(Subscriber::class, ['email', $email]);
    }

    public function countSubscriptions() : int
    {
        return $this->finder(Subscription::class)
            ->with('Subscriber', true)
            ->where('group_id', $this->group->group_id)
            ->where('Subscriber.status', 'active')
            ->total();
    }
}
