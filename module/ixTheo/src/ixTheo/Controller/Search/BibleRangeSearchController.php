<?php
namespace ixTheo\Controller\Search;

class BibleRangeSearchController extends \VuFind\Controller\AbstractSearch
{
    /**
     * Home action
     *
     * @return mixed
     */
    public function homeAction()
    {

        $query = $this->getRequest()->getQuery();
        $query->set('type', 'BibleRangeSearch');
        // Default case -- standard behavior.
        return parent::resultsAction();
    }
}

