<?php namespace Hampel\Newsletters\Traits;

use Hampel\Newsletters\Repository\NewsletterRepository;

trait RepositoryAwareTrait
{
    /**
     * Newsletter repository
     *
     * @var NewsletterRepository|null
     */
    protected ?NewsletterRepository $repo = null;

    /**
     * Set up our API subcontainer
     *
     * @param NewsletterRepository $repo
     * @return void
     */
    public function setRepository(NewsletterRepository $repo)
    {
        $this->repo = $repo;
    }
}
