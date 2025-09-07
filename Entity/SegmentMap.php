<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class SegmentMap extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_segment_map';
        $structure->shortName = 'Hampel\Newsletters:SegmentMap';
        $structure->primaryKey = 'segment_map_id';
        $structure->columns = [
            'segment_map_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'segment_id' => ['type' => self::UINT, 'required' => true],
            'list_id' => ['type' => self::UINT, 'required' => true],
        ];

        $structure->relations = [
            'Segments' => [
                'entity' => 'Hampel\Newsletters:Segment',
                'type' => self::TO_MANY,
                'conditions' => 'subscriber_id'
            ],
            'MailingLists' => [
                'entity' => 'Hampel\Newsletters:MailingList',
                'type' => self::TO_MANY,
                'conditions' => 'list_id'
            ],
        ];

        return $structure;
    }
}
