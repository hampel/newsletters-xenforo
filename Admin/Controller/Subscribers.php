<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Entity\Subscriber;
use Hampel\Newsletters\Filterer\Subscriber as SubscriberFilterer;
use XF\ControllerPlugin\DeletePlugin;
use XF\Mvc\ParameterBag;

class Subscribers extends AbstractBaseController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('newsletters');
    }

    public function actionAdminSection()
    {
        return $this->plugin('XF:AdminSection')->actionView('newslettersSubscribers');
    }

    public function actionIndex(ParameterBag $params)
    {
        $this->setSectionContext('newslettersSubscribers');

        if ($params->subscriber_id)
        {
            $subscriber = $this->assertSubscriberExists($params->subscriber_id);

            return $this->redirect($this->buildLink('newsletters/subscribers/edit', $subscriber));
        }

        $page = $this->filterPage();
        $perPage = 20;

        $filterer = $this->setupSubscriberFilterer();
        $finder = $filterer->apply()->limitByPage($page, $perPage);

        $filter = $this->filter('_xfFilter', [
            'text' => 'str',
            'prefix' => 'bool'
        ]);
        if (strlen($filter['text']))
        {
            $conditions = [
                ['email', 'LIKE', $finder->escapeLike($filter['text'], $filter['prefix'] ? '?%' : '%?%')],
                ['User.username', 'LIKE', $finder->escapeLike($filter['text'], $filter['prefix'] ? '?%' : '%?%')],
                ['description', 'LIKE', $finder->escapeLike($filter['text'], $filter['prefix'] ? '?%' : '%?%')],
            ];
            $finder->whereOr($conditions);
        }

        $linkParams = $filterer->getLinkParams();

        $total = $finder->total();
        $this->assertValidPage($page, $perPage, $total, 'newsletters/subscribers');

        $viewParams = [
            'subscribers' => $finder->fetch(),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,

            'linkParams' => $linkParams,

            'statusOptions' => $this->repo->getStatusOptions(),
        ];

        return $this->view('Hampel\Newsletters:Subscriber\List', 'newsletters_subscribers_list', $viewParams);
    }

    public function actionEdit(ParameterBag $params)
    {
        $subscriber = $this->assertSubscriberExists($params->subscriber_id);

        $viewParams = [
            'subscriber' => $subscriber,
            'statusOptions' => $this->repo->getStatusOptions(),
        ];

        return $this->view('Hampel\Newsletters:Subscriber\Edit', 'newsletters_subscribers_edit', $viewParams);
    }

    protected function subscriberSaveProcess(Subscriber $subscriber)
    {
        $form = $this->formAction();

        $input = $this->filter([
            'status' => 'str',
        ]);

        $form->basicEntitySave($subscriber, $input);

        return $form;
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params->subscriber_id)
        {
            $subscriber = $this->assertSubscriberExists($params->subscriber_id);
        }
        else
        {
            $subscriber = $this->em()->create(Subscriber::class);
        }

        $this->subscriberSaveProcess($subscriber)->run();

        return $this->redirect($this->buildLink('newsletters/subscribers') . $this->buildLinkHash($subscriber->subscriber_id));

    }

    public function actionDelete(ParameterBag $params)
    {
        $subscriber = $this->assertSubscriberExists($params->subscriber_id);

        /** @var DeletePlugin $plugin */
        $plugin = $this->plugin(DeletePlugin::class);
        return $plugin->actionDelete(
            $subscriber,
            $this->buildLink('newsletters/subscribers/delete', $subscriber),
            $this->buildLink('newsletters/subscribers/edit', $subscriber),
            $this->buildLink('newsletters/subscribers'),
            $subscriber->email
        );
    }

    // ----------------------------------------------------------------

    protected function assertSubscriberExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists(Subscriber::class, $id, $with, $phraseKey);
    }

    protected function setupSubscriberFilterer(): SubscriberFilterer
    {
        /** @var SubscriberFilterer $filterer */
        $filterer = $this->app->filterer(SubscriberFilterer::class);
        $filterer->addFilters($this->request, $this->filter('_skipFilter', 'str'));

        return $filterer;
    }
}
