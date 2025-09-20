<?php namespace Hampel\Newsletters\Entity;

use XF\Entity\User;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;
use XF\Repository\BanningRepository;
use XF\Validator\Email;

class Subscriber extends Entity
{
    protected function _postDelete()
    {
        // clean up any subscriptions for this subscriber
        foreach ($this->Subscriptions as $subscription)
        {
            $subscription->delete();
        }
    }

    protected function verifyEmail(&$email)
    {
        if ($this->isUpdate() && $email === $this->getExistingValue('email'))
        {
            return true;
        }

        /** @var BanningRepository $banningRepo */
        $banningRepo = $this->repository(BanningRepository::class);

        $bannedEmails = $this->app()->container('bannedEmails');

        $emailValidator = $this->app()->validator(Email::class);
        $emailValidator->setOption('banned', $bannedEmails);
        $emailValidator->setOption('check_typos', true);

        $email = $emailValidator->coerceValue($email);

        if (!$emailValidator->isValid($email, $errorKey))
        {
            if ($errorKey == 'banned')
            {
                $this->error(\XF::phrase('email_address_you_entered_has_been_banned_by_administrator'), 'email');
            }
            else if ($errorKey == 'typo')
            {
                $this->error(\XF::phrase('email_address_you_entered_appears_have_typo'));
            }
            else
            {
                $this->error(\XF::phrase('please_enter_valid_email'), 'email');
            }

            return false;
        }

        return true;
    }

    protected function verifyUserId(&$user_id)
    {
        $user = $this->em()->findOne(User::class, ['email' => $this->email]);
        if ($user)
        {
            if (empty($user_id))
            {
                // there is an existing user associated with this email - link them

                $user_id = $user->user_id;
            }
            elseif ($user->user_id != $user_id)
            {
                // there is an existing user associated with this email - but it doesn't match the user_id specified

                $this->error(\XF::phrase('newsletters_subscriber_user_id_does_not_match_email'), 'user_id');

                return false;
            }
        }

        return true;
    }

    protected function verifyDescription(&$description)
    {
        // automatically trim the description to fit our column size
        $description = substr(trim($description), 0, 255);

        return true;
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_newsletters_subscriber';
        $structure->shortName = 'Hampel\Newsletters:Subscriber';
        $structure->primaryKey = 'subscriber_id';
        $structure->columns = [
            'subscriber_id' => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'email' => ['type' => self::STR, 'maxLength' => 120, 'required' => true],
            'user_id' => ['type' => self::UINT, 'nullable' => true],
            'description' => ['type' => self::STR, 'maxLength' => 255, 'default' => ''],
            'status' => ['type' => self::STR, 'required' => true, 'allowedValues' => ['active', 'invalid', 'unsubscribed']],
            'created_date' => ['type' => self::UINT, 'default' => \XF::$time],
            'source' => ['type' => self::STR, 'required' => true, 'allowedValues' => ['import', 'webform', 'user']],
            'signup_date' => ['type' => self::UINT, 'required' => true],
            'signup_ip' => ['type' => self::BINARY, 'maxLength' => 16, 'nullable' => true],
            'confirmation_date' => ['type' => self::UINT, 'nullable' => true],
            'confirmation_ip' => ['type' => self::BINARY, 'maxLength' => 16, 'nullable' => true],
        ];

        $structure->relations = [
            'User' => [
                'entity' => User::class,
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ],
            'Subscriptions' => [
                'entity' => Subscription::class,
                'type' => self::TO_MANY,
                'conditions' => 'subscriber_id'
            ],
        ];

        return $structure;
    }
}
