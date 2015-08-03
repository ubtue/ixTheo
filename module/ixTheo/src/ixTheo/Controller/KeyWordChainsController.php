<?php
namespace ixTheo\Controller;

class KeyWordChainsController extends \VuFind\Controller\AbstractSearch
{
    /**
     * Home action
     *
     * @return mixed
     */
    public function homeAction()
    {
        // return $this->resultsAction();
        return $this->createViewModel();
    }
}

