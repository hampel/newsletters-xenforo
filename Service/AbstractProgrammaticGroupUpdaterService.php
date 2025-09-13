<?php namespace Hampel\Newsletters\Service;

use Hampel\Newsletters\Entity\Group;

abstract class AbstractProgrammaticGroupUpdaterService extends AbstractGroupUpdaterService
{
    protected string $type = 'programmatic';

    protected array $requiredCriteria = ['class', 'parameters'];

    protected array $parameters = [];

    public function setGroup(Group $group)
    {
        parent::setGroup($group);

        if (empty($group->criteria['parameters']))
        {
            throw new \LogicException('You must set parameters for this service');
        }

        $this->parameters = $group->criteria['parameters'];
    }
}
