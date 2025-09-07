<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Segment extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_segment';
        $structure->shortName = 'Hampel\Newsletters:Segment';
        $structure->primaryKey = 'segment_id';
        $structure->columns = [
            'segment_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'name' => ['type' => self::STR, 'maxLength' => 128, 'required' => true],
            'created_date' => ['type' => self::UINT, 'required' => true],
        ];

        $structure->relations = [
            'SegmentMaps' => [
                'entity' => 'Hampel\Newsletters:SegmentMap',
                'type' => self::TO_MANY,
                'conditions' => 'segment_id'
            ],
        ];

        return $structure;
    }
}
