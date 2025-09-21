<?php namespace Hampel\Newsletters\Entity;

use Hampel\Newsletters\Service\AbstractGroupBuilderService;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Group extends Entity
{
    public function getParams($key = null)
    {
        $parameters = $this->getValue('parameters');

        if ($this->type == 'programmatic')
        {
            $params = $parameters['params'] ?? [];
        }
        else
        {
            $params = $parameters;
        }

        return $key ? ($params[$key] ?? null) : $params;
    }

    public function setParameters(array $params)
    {
        if ($this->type == 'programmatic')
        {
            $this->parameters = compact('params');
        }
        else
        {
            $this->parameters = $params;
        }
    }

    public function updateSubscriptions() : int
    {
        $updater = $this->createUpdater();
        $updater->setGroup($this);
        return $updater->update();
    }

    public function createUpdater() : AbstractGroupBuilderService
    {
        if (empty($this->builder_id))
        {
            throw new \LogicException('Can only call createUpdater for programmatic groups');
        }

        return $this->GroupBuilder->createUpdater();
    }

    protected function _preSave()
    {
        if ($this->isInsert() && empty($this->getValue('type')))
        {
            $this->error(\XF::phrase('newsletters_please_select_a_group_type'), 'type');
        }

        if ($this->getValue('type') == 'usergroup')
        {
            $parameters = $this->getValue('parameters');
            if (empty($parameters['usergroups']))
            {
                $this->error(\XF::phrase('newsletters_please_select_at_least_one_usergroup'), 'parameters[usergroups]');
            }

            // use our pre-defined builder
            $this->builder_id = 'usergroup';
        }

        $type = $this->getValue('type');
        if ($type == 'programmatic' || $type == 'usergroup')
        {
            if (empty($this->getValue('builder_id')))
            {
                $this->error(\XF::phrase('newsletters_please_select_a_builder'), 'builder_id');
            }

            if ($this->isChanged('parameters'))
            {
                $params = $this->getParams();

                if (!is_array($params))
                {
                    // params is in string form, assume it is JSON encoded and convert it to an array
                    $params = json_decode($params, true);
                    if (!is_array($params))
                    {
                        // that didn't work - so fail gracefully
                        $params = [];
                    }
                }

                $this->setParameters($params);

                // validate the group to check that the parameters are valid
                // this will fail if parameters are invalid
                $errors = $this->createUpdater()->validateGroup($this);
                if (!empty($errors))
                {
                    $this->error(array_shift($errors), 'parameters');
                }
            }
        }
        else
        {
            // make sure builder_id is not set - otherwise could be blank
            $this->builder_id = null;
        }
    }

    protected function _postDelete()
    {
        // clean up any subscriptions for this group
        foreach ($this->Subscriptions as $subscription)
        {
            $subscription->delete();
        }

        // clean up any list maps for this group
        foreach ($this->ListMaps as $map)
        {
            $map->delete();
        }
    }

    // automatically trim the name to fit our column size
    protected function verifyName(&$name)
    {
        $name = substr(trim($name), 0, 50);

        return true;
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_group';
        $structure->shortName = 'Hampel\Newsletters:Group';
        $structure->primaryKey = 'group_id';
        $structure->columns = [
            'group_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'name' => ['type' => self::STR, 'maxLength' => 50, 'required' => true],
            'description' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'type' => [
                'type' => self::STR,
                'allowedValues' => ['manual', 'joinable', 'usergroup', 'programmatic'],
            ],
            'builder_id' => ['type' => self::BINARY, 'maxLength' => 50, 'nullable' => true],
            'parameters' => ['type' => self::JSON_ARRAY, 'default' => []],
            'subscriber_count' => ['type' => self::UINT, 'default' => 0],
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
            'GroupBuilder' => [
                'entity' => GroupBuilder::class,
                'type' => self::TO_ONE,
                'conditions' => 'builder_id',
                'primary' => true
            ]
        ];

        return $structure;
    }
}
