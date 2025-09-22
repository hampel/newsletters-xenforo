<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Entity\MailingList;
use XF\ControllerPlugin\DeletePlugin;
use XF\Mvc\ParameterBag;

class MailingLists extends AbstractBaseController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('newsletters');
    }

    public function actionAdminSection()
    {
        return $this->plugin('XF:AdminSection')->actionView('newslettersMailingLists');
    }

    public function actionIndex(ParameterBag $params)
    {
        $this->setSectionContext('newslettersMailingLists');

        if ($params->list_id)
        {
            $list = $this->assertListExists($params->list_id);

            return $this->redirect($this->buildLink('newsletters/mailing-lists/edit', $list));
        }

        $lists = $this->repo->getLists();

        $viewParams = [
            'lists' => $lists,
            'totalLists' => $lists->count(),
        ];

        return $this->view('Hampel\Nesletters:MailingLists\List', 'newsletters_mailinglists_list', $viewParams);
    }

    protected function listAddEdit(MailingList $list)
    {
        $groups = $this->repo->getGroupTitlePairs();

        $viewParams = [
            'list' => $list,
            'groups' => $groups,
        ];

        return $this->view('Hampel\Newsletters:MailingLists\Edit', 'newsletters_mailinglists_edit', $viewParams);
    }

    public function actionAdd(ParameterBag $params)
    {
        $list = $this->em()->create(MailingList::class);
        return $this->listAddEdit($list);
    }

    public function actionEdit(ParameterBag $params)
    {
        $list = $this->assertListExists($params->list_id);
        return $this->listAddEdit($list);
    }

    protected function listSaveProcess(MailingList $list)
    {
        $form = $this->formAction();

        $input = $this->filter([
            'name' => 'str',
            'description' => 'str',
            'group_ids' => 'array-uint',
        ]);

        $form->basicEntitySave($list, $input);

        return $form;
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params->list_id)
        {
            $list = $this->assertListExists($params->list_id);
        }
        else
        {
            $list = $this->em()->create(MailingList::class);
        }

        $this->listSaveProcess($list)->run();

        return $this->redirect($this->buildLink('newsletters/mailing-lists') . $this->buildLinkHash($list->list_id));

    }

    public function actionDelete(ParameterBag $params)
    {
        $list = $this->assertListExists($params->list_id);

        /** @var DeletePlugin $plugin */
        $plugin = $this->plugin(DeletePlugin::class);
        return $plugin->actionDelete(
            $list,
            $this->buildLink('newsletters/mailing-lists/delete', $list),
            $this->buildLink('newsletters/mailing-lists/edit', $list),
            $this->buildLink('newsletters/mailing-lists'),
            $list->name
        );
    }

    // ----------------------------------------------------------------

    protected function assertListExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists(MailingList::class, $id, $with, $phraseKey);
    }

}
