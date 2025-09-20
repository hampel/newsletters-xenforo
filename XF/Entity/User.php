<?php namespace Hampel\Newsletters\XF\Entity;

use Hampel\Newsletters\Entity\Subscriber;
use XF\Mvc\Entity\Structure;

class User extends XFCP_User
{
    public function rebuildNewsletterSubscriber() : ?Subscriber
    {
        if (!$this->user_id)
        {
            throw new \LogicException("User must be saved first");
        }

        $subscriber = $this->NewsletterSubscriber;
        if (empty($this->email))
        {
            if ($subscriber)
            {
                // we have a subscriber, but no email - delete the subscriber
                $subscriber->delete(false);
            }

            // either way, we can't have a subscriber with no email
            return null;
        }

        if (!$subscriber)
        {
            $subscriber = $this->em()->create(Subscriber::class);
        }

        // over-ride existing subscriber information because user registration trumps previously imported subscribers
        $subscriber->bulkSet([
            'email' => $this->email,
            'user_id' => $this->user_id,
            'description' => "User: {$this->username}",
            'status' => $this->newsletter_status,
            'signup_date' => $this->register_date,
            'source' => 'user',
        ]);

        $subscriber->save();

        // TODO: update user change log?

        return $subscriber;
    }

    protected function getNewsletterStatus() : string
    {
        // user has opted not to receive admin emails - they are considered unsubscribed
        if (!$this->Option->receive_admin_email)
        {
            return 'unsubscribed';
        }

        return $this->user_state == 'valid' ? 'active' : 'invalid';
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isInsert() && !empty($this->email))
        {
            // newly created user - create a subscriber for them
            // but only if they have an email address!
            $this->rebuildNewsletterSubscriber();
        }

        if ($this->isUpdate() && $this->isChanged('email'))
        {
            $this->rebuildNewsletterSubscriber();
        }

        if ($this->isStateChanged('user_state', 'valid'))
        {
            $this->rebuildNewsletterSubscriber();
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->getters['newsletter_status'] = true;

        $structure->relations['NewsletterSubscriber'] = [
            'entity' => 'Hampel\Newsletters:Subscriber',
            'type' => self::TO_ONE,
            'conditions' => 'user_id',
        ];

        return $structure;
    }
}
