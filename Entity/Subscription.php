<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Subscription extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_subscription';
        $structure->shortName = 'Hampel\Newsletters:Subscription';
        $structure->primaryKey = 'subscription_id';
        $structure->columns = [
            'subscription_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'subscriber_id' => ['type' => self::UINT, 'required' => true],
            'group_id' => ['type' => self::UINT, 'required' => true],
        ];

        $structure->relations = [
            'Subscriber' => [
                'entity' => 'Hampel\Newsletters:Subscriber',
                'type' => self::TO_ONE,
                'conditions' => 'subscriber_id',
                'primary' => true,
            ],
            'Group' => [
                'entity' => 'Hampel\Newsletters:Group',
                'type' => self::TO_ONE,
                'conditions' => 'group_id',
                'primary' => true,
            ],
        ];

        return $structure;
    }
}
