<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Group extends Entity
{
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
                       'allowedValues' => ['', 'manual', 'usergroup', 'joinable']
            ],
            'criteria' => ['type' => self::JSON_ARRAY, 'default' => []],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
        ];

        $structure->relations = [
            'Subscriptions' => [
                'entity' => 'Hampel\Newsletters:Subscription',
                'type' => self::TO_MANY,
                'conditions' => 'group_id'
            ],
            'ListMaps' => [
                'entity' => 'Hampel\Newsletters:Map',
                'type' => self::TO_MANY,
                'conditions' => 'group_id'
            ],
        ];

        return $structure;
    }
}
