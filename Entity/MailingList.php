<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class MailingList extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_mailing_list';
        $structure->shortName = 'Hampel\Newsletters:MailingList';
        $structure->primaryKey = 'list_id';
        $structure->columns = [
            'list_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'name' => ['type' => self::STR, 'maxLength' => 128, 'required' => true],
            'description' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'group_ids' => ['type' => self::LIST_COMMA, 'required' => true,
                'list' => ['type' => 'posint', 'unique' => true, 'sort' => SORT_NUMERIC],
            ],
            'subscriber_count' => ['type' => self::UINT, 'default' => 0],
        ];

        $structure->relations = [
            'GroupMaps' => [
                'entity' => Map::class,
                'type' => self::TO_MANY,
                'conditions' => 'list_id'
            ],
        ];

        return $structure;
    }
}
