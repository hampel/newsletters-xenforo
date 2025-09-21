<?php namespace Hampel\Newsletters;

use XF\App;
use XF\Service\User\DeleteCleanUpService;

class Listener
{
    public static function appSetup(App $app)
    {
        $container = $app->container();

        $container['newsletters.log'] = function(\XF\Container $c) use ($app)
        {
            if ($c->offsetExists('monolog'))
            {
                return $c['monolog']->newChannel('newsletters');
            }
        };
    }

    public static function userDeleteCleanInit(DeleteCleanUpService $deleteService, array &$deletes)
    {
        // TODO: is there anything else we need to clean up if a user is deleted?

        $deletes['xf_newsletters_subscriber'] = 'user_id = ?';
    }
}