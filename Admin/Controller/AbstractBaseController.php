<?php namespace Hampel\Newsletters\Admin\Controller;

use Hampel\Newsletters\Repository\NewsletterRepository;
use Hampel\Newsletters\Traits\RepositoryAwareTrait;
use XF\Admin\Controller\AbstractController;

class AbstractBaseController extends AbstractController
{
    use RepositoryAwareTrait;

    protected function init()
    {
        $this->setRepository($this->repository(NewsletterRepository::class));
    }
}
