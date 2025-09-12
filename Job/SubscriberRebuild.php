<?php namespace Hampel\Newsletters\Job;

use XF\Job\AbstractRebuildJob;

class SubscriberRebuild extends AbstractRebuildJob
{

    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit(
            "
				SELECT user_id
				FROM xf_user
				WHERE user_id > ?
				ORDER BY user_id
			",
            $batch
        ), $start);
    }

    protected function rebuildById($id)
    {
        /** @var \XF\Entity\User $user */
        $user = $this->app->em()->find(\XF\Entity\User::class, $id, ['NewsletterSubscriber']);
        if (!$user)
        {
            return;
        }

        $user->rebuildNewsletterSubscriber();
    }

    protected function getStatusType()
    {
        return \XF::phrase('users');
    }
}
