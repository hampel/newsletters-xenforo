<?php namespace Hampel\Newsletters\Job;

use Hampel\Newsletters\Entity\Group;
use XF\Job\AbstractRebuildJob;

class SubscriptionRebuild extends AbstractRebuildJob
{
    protected function getNextIds($start, $batch)
    {
        $db = $this->app->db();

        return $db->fetchAllColumn($db->limit(
            "
				SELECT group_id
				FROM xf_newsletters_group
				WHERE group_id > ?
				AND builder_id IS NOT NULL
				ORDER BY group_id
			",
            $batch
        ), $start);
    }

    protected function rebuildById($id)
    {
        /** @var \XF\Entity\User $user */
        $group = $this->app->em()->find(Group::class, $id, 'GroupBuilder');
        if (!$group)
        {
            return;
        }

        // check that the Group Builder / Addon is active
        if ($group->GroupBuilder->isActive())
        {
            $group->updateSubscriptions();
        }

    }

    protected function getStatusType()
    {
        return \XF::phrase('newsletters_groups');
    }
}
