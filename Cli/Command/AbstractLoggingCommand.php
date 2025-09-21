<?php namespace Hampel\Newsletters\Cli\Command;

use Hampel\Newsletters\Repository\NewsletterRepository;
use Hampel\Newsletters\Traits\LogConsoleTrait;
use Hampel\Newsletters\Traits\RepositoryAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use XF\Cli\Command\AbstractCommand;
use XF\PrintableException;

abstract class AbstractLoggingCommand extends AbstractCommand
{
    use LogConsoleTrait;
    use RepositoryAwareTrait;

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // set up our logging
        $this->output = $output;
        $this->setLogger(\XF::app()->container('newsletters.log'));
        $this->setContext(['command' => $this->getName()]);
        $this->setRepository(\XF::repository(NewsletterRepository::class));
    }

    public function fail(\Throwable|string|null $exception = null)
    {
        if (is_string($exception))
        {
            $this->logConsole('error', $exception);
            throw new PrintableException($exception);
        }
        elseif ($exception instanceof \Throwable)
        {
            $this->logConsole('error', $exception->getMessage());
            throw new PrintableException($exception->getMessage());
        }
    }
}
