<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Filterer\Subscriber as SubscriberFilterer;
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

        $page = $this->filterPage();
        $perPage = 20;

        $filterer = $this->setupSubscriberFilterer();
        $finder = $filterer->apply()->limitByPage($page, $perPage);

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

        return $this->view('Hampel\Nesletters:Subscriber\List', 'newsletters_subscribers_list', $viewParams);
    }

    protected function setupSubscriberFilterer(): SubscriberFilterer
    {
        /** @var SubscriberFilterer $filterer */
        $filterer = $this->app->filterer(SubscriberFilterer::class);
        $filterer->addFilters($this->request, $this->filter('_skipFilter', 'str'));

        return $filterer;
    }
}
