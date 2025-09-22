<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Entity\GroupBuilder;
use XF\ControllerPlugin\DeletePlugin;
use XF\Mvc\ParameterBag;
use XF\Repository\AddOnRepository;

class GroupBuilders extends AbstractBaseController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('newsletters');
    }

    public function actionAdminSection()
    {
        return $this->plugin('XF:AdminSection')->actionView('newslettersGroupBuilders');
    }

    public function actionIndex(ParameterBag $params)
    {
        $this->setSectionContext('newslettersGroupBuilders');

        if ($params->builder_id)
        {
            $builder = $this->assertBuilderExists($params->builder_id);

            return $this->redirect($this->buildLink('newsletters/group-builders/edit', $builder));
        }

        $builders = $this->repo->getBuilders();

        $addOns = $this->repository(AddOnRepository::class)->findActiveAddOnsForList()->fetch();

        $viewParams = [
            'builders' => $builders->groupBy('addon_id'),
            'totalBuilders' => $builders->count(),
            'addOns' => $addOns,
        ];

        return $this->view('Hampel\Newsletters:GroupBuilders\List', 'newsletters_groupbuilders_list', $viewParams);
    }

    protected function builderAddEdit(GroupBuilder $builder)
    {
        $viewParams = [
            'builder' => $builder,
        ];

        return $this->view('Hampel\Newsletters:GroupBuilders\Edit', 'newsletters_groupbuilders_edit', $viewParams);
    }

    public function actionAdd(ParameterBag $params)
    {
        $builder = $this->em()->create(GroupBuilder::class);
        return $this->builderAddEdit($builder);
    }

    public function actionEdit(ParameterBag $params)
    {
        $builder = $this->assertBuilderExists($params->builder_id);
        return $this->builderAddEdit($builder);
    }

    protected function builderSaveProcess(GroupBuilder $builder)
    {
        $form = $this->formAction();

        $input = $this->filter([
            'name' => 'str',
            'class' => 'str',
            'addon_id' => 'str',
        ]);
        // important: if this is an update, we won't have a builder_id, so set the default to the original value so it won't have changed
        $input['builder_id'] = $this->filter('builder_id', 'str', $builder->builder_id);

        $form->basicEntitySave($builder, $input);

        return $form;
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params->builder_id)
        {
            $builder = $this->assertBuilderExists($params->builder_id);
        }
        else
        {
            $builder = $this->em()->create(GroupBuilder::class);
        }

        $this->builderSaveProcess($builder)->run();

        return $this->redirect($this->buildLink('newsletters/group-builders') . $this->buildLinkHash($builder->builder_id));

    }

    public function actionDelete(ParameterBag $params)
    {
        $builder = $this->assertBuilderExists($params->builder_id);

        /** @var DeletePlugin $plugin */
        $plugin = $this->plugin(DeletePlugin::class);
        return $plugin->actionDelete(
            $builder,
            $this->buildLink('newsletters/group-builders/delete', $builder),
            $this->buildLink('newsletters/group-builders/edit', $builder),
            $this->buildLink('newsletters/group-builders'),
            $builder->name
        );
    }

    // ----------------------------------------------------------------

    protected function assertBuilderExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists(GroupBuilder::class, $id, $with, $phraseKey);
    }

}
