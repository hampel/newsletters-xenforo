<?php namespace Hampel\Newsletters\Entity;

use Hampel\Newsletters\Service\AbstractGroupBuilderService;
use XF\Entity\AddOn;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class GroupBuilder extends Entity
{
    public function createGroup(string $name, array $params, string $description = '')
    {
        if (empty($this->builder_id) || $this->isInsert())
        {
            throw new \LogicException('GroupBuilder must be saved before creating a group');
        }

        if (!$this->isActive())
        {
            throw new \LogicException('Cannot create group for inactive Addon ' . $this->addon_id);
        }

        $group = $this->em()->create(Group::class);
        $group->name = $name;
        $group->description = $description;
        $group->type = $this->builder_id == 'usergroup' ? 'usergroup' : 'programmatic';
        $group->builder_id = $this->builder_id;
        $group->setParameters($params);
        $group->save();

        return $group;
    }

    public function createUpdater() : AbstractGroupBuilderService
    {
        if (!class_exists($this->class))
        {
            throw new \LogicException('Updater class does not exist: ' . $this->class);
        }

        if (!is_subclass_of($this->class, AbstractGroupBuilderService::class))
        {
            throw new \LogicException('Updater class must be a subclass of ' .  AbstractGroupBuilderService::class);
        }

        if (!$this->isActive())
        {
            throw new \LogicException('Cannot create updater for inactive Addon ' . $this->addon_id);
        }

        $class = $this->app()->extendClass($this->class);
        /** @var AbstractGroupBuilderService $updater */
        return new $class($this->app());
    }

    public function isActive()
    {
        return ($this->AddOn ? $this->AddOn->active : false);
    }

    protected function _postDelete()
    {
        // clean up any groups for this builder
        foreach ($this->Groups as $group)
        {
            $group->delete();
        }
    }

    // automatically trim the name to fit our column size
    protected function verifyName(&$name)
    {
        $name = substr(trim($name), 0, 50);

        return true;
    }

    protected function verifyClass(&$class)
    {
        if (!class_exists($class))
        {
            $this->error(\XF::phrase('newsletters_class_does_not_exist_x', ['class' => $this->class]));
            return false;
        }

        if (!is_subclass_of($class, AbstractGroupBuilderService::class))
        {
            $this->error(\XF::phrase('newsletters_class_must_be_subclass_of_x', ['class' => AbstractGroupBuilderService::class]));
            return false;
        }

        return true;
    }

    protected function _preSave()
    {
        if ($this->isUpdate() && $this->isChanged('builder_id'))
        {
            $this->error('The builder ID cannot be changed once set.', 'builder_id');
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_group_builder';
        $structure->shortName = 'Hampel\Newsletters:GroupBuilder';
        $structure->primaryKey = 'builder_id';

        $structure->columns = [
            'builder_id' => ['type' => self::STR, 'maxLength' => 50,
                             'required' => 'newsletters_please_enter_valid_builder_id',
                             'unique' => 'newsletters_builder_ids_must_be_unique',
                             'match' => self::MATCH_ALPHANUMERIC,
            ],
            'name' => ['type' => self::STR, 'maxLength' => 50, 'required' => true],
            'class' => ['type' => self::STR, 'maxLength' => 100, 'required' => true],
            'addon_id' => ['type' => self::BINARY, 'maxLength' => 50, 'required' => true],
        ];

        $structure->relations = [
            'Groups' => [
                'entity' => Group::class,
                'type' => self::TO_MANY,
                'conditions' => 'builder_id',
            ],
            'AddOn' => [
                'entity' => AddOn::class,
                'type' => self::TO_ONE,
                'conditions' => 'addon_id',
                'primary' => true,
            ],
        ];

        return $structure;
    }
}
