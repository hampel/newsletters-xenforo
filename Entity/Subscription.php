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
            'list_id' => ['type' => self::UINT, 'required' => true],
        ];

        $structure->relations = [
            'Subscribers' => [
                'entity' => 'Hampel\Newsletters:Subscriber',
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
