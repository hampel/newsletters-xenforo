<?php namespace Hampel\Newsletters\Finder;

use XF\Mvc\Entity\Finder;

class GroupFinder extends Finder
{
    public function addon(string $addon_id)
    {
        $this->where('type', 'programmatic');
        $this->where('addon_id', $addon_id);

        return $this;
    }
}
