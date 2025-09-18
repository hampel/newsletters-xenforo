<?php namespace Hampel\Newsletters;

use XF\Service\User\DeleteCleanUpService;

class Listener
{
    public static function userDeleteCleanInit(DeleteCleanUpService $deleteService, array &$deletes)
    {
        // TODO: is there anything else we need to clean up if a user is deleted?

        $deletes['xf_newsletters_subscriber'] = 'user_id = ?';
    }
}