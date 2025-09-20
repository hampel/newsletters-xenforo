<?php namespace Hampel\Newsletters\Filterer;

use XF\Filterer\AbstractFilterer;
use XF\Mvc\Entity\Finder;

class Subscriber extends AbstractFilterer
{
    protected $defaultOrder = 'signup_date';
    protected $defaultDirection = 'desc';

    protected $validSorts = [
        'signup_date' => true,
        'email' => true,
    ];

    protected function getFinderType(): string
    {
        return 'Hampel\Newsletters:Subscriber';
    }

    protected function initFinder(Finder $finder, array $setupData)
    {
        $finder
            ->setDefaultOrder($this->defaultOrder, $this->defaultDirection);
    }

    protected function getFilterTypeMap(): array
    {
        return [
            'status' => 'str',
            'source' => 'str',
            'order' => 'str',
            'direction' => 'str',
        ];
    }

    protected function getLookupTypeList(): array
    {
        return [
            'order',
        ];
    }

    protected function onFinalize()
    {
        $finder = $this->finder;

        $sorts = $this->validSorts;
        $order = $this->rawFilters['order'] ?? null;
        $direction = $this->rawFilters['direction'] ?? null;

        if ($order && isset($sorts[$order]))
        {
            if (!in_array($direction, ['asc', 'desc']))
            {
                $direction = 'desc';
            }

            $defaultOrder = $this->defaultOrder;
            $defaultDirection = $this->defaultDirection;

            if ($order != $defaultOrder || $direction != $defaultDirection)
            {
                if ($sorts[$order] === true)
                {
                    $finder->order($order, $direction);
                }
                else
                {
                    $finder->order($sorts[$order], $direction);
                }

                $this->addLinkParam('order', $order);
                $this->addLinkParam('direction', $direction);
                $this->addDisplayValue('order', $order . '_' . $direction);
            }
        }
    }

    protected function applyFilter(string $filterName, &$value, &$displayValue): bool
    {
        /** @var \Hampel\Newsletters\Entity\Subscriber $finder */
        $finder = $this->finder;

        switch ($filterName)
        {
            case 'status':
                if (!$value)
                {
                    return false;
                }

                $displayValue = $this->app()->getContentTypePhrase($value);
//                $finder->where('status', $value);
                $finder->whereIf($value != 'all', ['status', $value], []);
                return true;

            case 'source':
                if (!$value)
                {
                    return false;
                }

                $displayValue = $this->app()->getContentTypePhrase($value);
                $finder->where('source', $value);
                return true;

        }

        return false;
    }
}
