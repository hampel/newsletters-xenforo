<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class MailingList extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_list';
        $structure->shortName = 'Hampel\Newsletters:MailingList';
        $structure->primaryKey = 'list_id';
        $structure->columns = [
            'list_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'name' => ['type' => self::STR, 'maxLength' => 128, 'required' => true],
            'created_date' => ['type' => self::UINT, 'required' => true],
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
