<?php namespace Hampel\Newsletters\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Subscriber extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_subscriber';
        $structure->shortName = 'Hampel\Newsletters:Subscriber';
        $structure->primaryKey = 'subscriber_id';
        $structure->columns = [
            'subscriber_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'email' => ['type' => self::STR, 'maxLength' => 120, 'required' => true],
            'status' => ['type' => self::STR, 'required' => true, 'allowedValues' => ['active', 'email_confirm', 'unsubscribed', 'email_bounce', 'spam_complaint', 'rejected', 'disabled']],
            'created_date' => ['type' => self::UINT, 'required' => true],
            'source' => ['type' => self::STR, 'required' => true, 'allowedValues' => ['import', 'webform', 'user']],
            'signup_date' => ['type' => self::UINT, 'required' => true],
            'signup_ip' => ['type' => self::BINARY, 'maxLength' => 16], 'nullable' => true,
            'confirmation_date' => ['type' => self::UINT, 'nullable' => true],
            'confirmation_ip' => ['type' => self::BINARY, 'maxLength' => 16, 'nullable' => true],
        ];

        $structure->relations = [
            'Subscriptions' => [
                'entity' => 'Hampel\Newsletters:Subscription',
                'type' => self::TO_MANY,
                'conditions' => 'subscriber_id'
            ],
        ];

        return $structure;
    }
}
