<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Map extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_map';
        $structure->shortName = 'Hampel\Newsletters:Map';
        $structure->primaryKey = 'map_id';
        $structure->columns = [
            'map_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'group_id' => ['type' => self::UINT, 'required' => true],
            'list_id' => ['type' => self::UINT, 'required' => true],
        ];

        $structure->relations = [
            'Groups' => [
                'entity' => Group::class,
                'type' => self::TO_MANY,
                'conditions' => 'group_id'
            ],

            'MailingLists' => [
                'entity' => MailingList::class,
                'type' => self::TO_MANY,
                'conditions' => 'list_id'
            ],
        ];

        return $structure;
    }
}
