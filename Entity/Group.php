<?php namespace Hampel\Newsletters\Entity;

use Hampel\Newsletters\Service\AbstractGroupUpdaterService;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Group extends Entity
{
    public function createUpdater() : AbstractGroupUpdaterService
    {
        if ($this->type != 'programmatic')
        {
            throw new \LogicException('Group must be programmatic');
        }

        if (empty($this->criteria['class']))
        {
            throw new \LogicException('Updater class cannot be empty');
        }

        $className = $this->criteria['class'];

        if (!class_exists($className) || !is_subclass_of($className, AbstractGroupUpdaterService::class))
        {
            throw new \LogicException('Updater class must be a subclass of ' .  AbstractGroupUpdaterService::class);
        }

        $class = $this->app()->extendClass($className);
        /** @var AbstractGroupUpdaterService $updater */
        $updater = new $class($this->app());
        $updater->setGroup($this);
        return $updater;
    }

    public static function getStructure(Structure $structure)
    {
        // TODO: add display name for public display? description too?

        $structure->table = 'xf_newsletters_group';
        $structure->shortName = 'Hampel\Newsletters:Group';
        $structure->primaryKey = 'group_id';
        $structure->columns = [
            'group_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'name' => ['type' => self::STR, 'maxLength' => 128, 'required' => true],
            'type' => ['type' => self::STR,
                       'allowedValues' => ['', 'manual', 'joinable', 'usergroup', 'programmatic'],
            ],
            'criteria' => ['type' => self::JSON_ARRAY, 'default' => []],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'addon_id' => ['type' => self::BINARY, 'maxLength' => 50, 'required' => true],
        ];

        $structure->relations = [
            'Subscriptions' => [
                'entity' => Subscription::class,
                'type' => self::TO_MANY,
                'conditions' => 'group_id'
            ],
            'ListMaps' => [
                'entity' => Map::class,
                'type' => self::TO_MANY,
                'conditions' => 'group_id'
            ],
        ];

        return $structure;
    }
}
