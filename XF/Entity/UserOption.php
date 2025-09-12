<?php namespace Hampel\Newsletters\XF\Entity;

class UserOption extends XFCP_UserOption
{
    protected function _postSave()
    {
        parent::_postSave();

        if ($this->isUpdate() && $this->isChanged('receive_admin_email'))
        {
            $this->User->rebuildNewsletterSubscriber();
        }
    }

}
