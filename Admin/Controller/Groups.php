<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Entity\Group;
use Hampel\Newsletters\Service\AbstractGroupUpdaterService;
use Hampel\Newsletters\Service\UsergroupGroupUpdaterService;
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

        $viewParams = [
            'groups' => $groups->groupBy('type'),
            'total' => $groups->count(),
            'typeOptions' => $this->repo->getGroupTypes(),
        ];

        return $this->view('Hampel\Nesletters:Groups\List', 'newsletters_groups_list', $viewParams);
    }

    protected function groupAddEdit(Group $group)
    {
        $userGroupRepo = $this->app->repository(UserGroupRepository::class);
        $userGroups = $userGroupRepo->getUserGroupTitlePairs();

        $viewParams = [
            'group' => $group,
            'typeOptions' => $this->repo->getGroupTypes(),
            'userGroups' => $userGroups,
        ];

        return $this->view('Hampel\Nesletters:Groups\Add', 'newsletters_groups_add', $viewParams);
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
            'type' => 'string',
            'criteria' => 'array',
        ]);

        $form->validate(function (FormAction $form) use ($input)
        {
            if (empty($input['type']))
            {
                $form->logError(\XF::phrase('newsletters_please_select_a_group_type'), 'type');
            }

            if ($input['type'] == 'usergroup' && empty($input['criteria']['usergroups']))
            {
                $form->logError(\XF::phrase('newsletters_please_select_at_least_one_usergroup'), 'criteria[usergroups]');
            }

            if ($input['type'] == 'programmatic')
            {
                \XF::dump($input);
                if (empty($input['criteria']['class']))
                {
                    $form->logError(\XF::phrase('newsletters_please_enter_updater_class'), 'criteria[class]');
                }
                else
                {
                    if (!class_exists($input['criteria']['class']) || !is_subclass_of($input['criteria']['class'], AbstractGroupUpdaterService::class))
                    {
                        $form->logError(\XF::phrase('newsletters_invalid_updater_class'), 'criteria[class]');
                    }
                }
            }
        });

        $form->basicEntitySave($group, $input);

        $updateService = $this->service(UsergroupGroupUpdaterService::class);

        $form->complete(function (FormAction $form) use ($group, $updateService)
        {
            if ($group->type == 'usergroup')
            {
                $updateService->setGroup($group);
                $updateService->updateGroupMembers();
            }
            else
            {
                // TODO: handle other group types
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

    // ----------------------------------------------------------------

    protected function assertGroupExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists(Group::class, $id, $with, $phraseKey);
    }

}
