<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Entity\Subscription;
use XF\ControllerPlugin\DeletePlugin;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;
use XF\Repository\UserGroupRepository;

class Groups extends AbstractBaseController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('newsletters');
    }

    public function actionAdminSection()
    {
        return $this->plugin('XF:AdminSection')->actionView('newslettersGroups');
    }

    public function actionIndex(ParameterBag $params)
    {
        $this->setSectionContext('newslettersGroups');

        if ($params->group_id)
        {
            $group = $this->assertGroupExists($params->group_id);

            return $this->redirect($this->buildLink('groups/edit', $group));
        }

        $groups = $this->repo->getGroups();
        $groupedGroups = array_replace(array_flip(['joinable', 'manual', 'usergroup', 'programmatic']), $groups->groupBy('type'));

        $viewParams = [
            'groups' => $groupedGroups,
            'totalGroups' => $groups->count(),
            'typeOptions' => $this->repo->getGroupTypes(),
        ];

        return $this->view('Hampel\Nesletters:Groups\List', 'newsletters_groups_list', $viewParams);
    }

    protected function groupAddEdit(Group $group)
    {
        $userGroupRepo = $this->app->repository(UserGroupRepository::class);
        $userGroups = $userGroupRepo->getUserGroupTitlePairs();
        $builders = $this->repo->getBuilderTitlePairs();

        $devMode = $this->app->config('development');

        $viewParams = [
            'group' => $group,
            'typeOptions' => $this->repo->getGroupTypes(),
            'userGroups' => $userGroups,
            'builders' => $builders,
            'userGroupsSelected' => $group['parameters']['usergroups'] ?? [],
            'devMode' => $devMode['enabled'] ?? false,
        ];

        return $this->view('Hampel\Nesletters:Groups\Edit', 'newsletters_groups_edit', $viewParams);
    }

    public function actionAdd(ParameterBag $params)
    {
        $group = $this->em()->create(Group::class);
        return $this->groupAddEdit($group);
    }

    public function actionEdit(ParameterBag $params)
    {
        $group = $this->assertGroupExists($params->group_id);
        return $this->groupAddEdit($group);
    }

    protected function groupSaveProcess(Group $group)
    {
        $form = $this->formAction();

        $input = $this->filter([
            'name' => 'str',
            'description' => 'str',
            'type' => 'str',
            'builder_id' => 'str',
            'parameters' => 'json-array'
        ]);

        $form->basicEntitySave($group, $input);

        $form->complete(function (FormAction $form) use ($group)
        {
            if ($group->builder_id)
            {
                $group->updateSubscribers();
            }
            else
            {
                //  TODO: handle other group types
            }
        });

        return $form;
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params->group_id)
        {
            $group = $this->assertGroupExists($params->group_id);
        }
        else
        {
            $group = $this->em()->create(Group::class);
        }

        $this->groupSaveProcess($group)->run();

        return $this->redirect($this->buildLink('newsletters/groups') . $this->buildLinkHash($group->group_id));

    }

    public function actionDelete(ParameterBag $params)
    {
        $group = $this->assertGroupExists($params->group_id);

        /** @var DeletePlugin $plugin */
        $plugin = $this->plugin(DeletePlugin::class);
        return $plugin->actionDelete(
            $group,
            $this->buildLink('newsletters/groups/delete', $group),
            $this->buildLink('newsletters/groups/edit', $group),
            $this->buildLink('newsletters/groups'),
            $group->name
        );
    }

    public function actionSubscribers(ParameterBag $params)
    {
        $this->setSectionContext('newslettersGroups');

        $group = $this->assertGroupExists($params->group_id);
        $subscriptions = $group->Subscriptions->filter(function (Subscription $subscription): bool
        {
            return $subscription->Subscriber->status == 'active';
        });

        $viewParams = [
            'group' => $group,
            'subscriptions' => $subscriptions,
            'totalSubscriptions' => $subscriptions->count(),
        ];

        return $this->view('Hampel\Nesletters:Groups\Subscriptions', 'newsletters_groups_subscriptions', $viewParams);

    }

    // ----------------------------------------------------------------

    protected function assertGroupExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists(Group::class, $id, $with, $phraseKey);
    }

}
