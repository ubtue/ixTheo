<?php
namespace ixTheo\Controller\Search;

class BibleRangeSearchController extends \VuFind\Controller\AbstractSearch {
    /**
     * Home action
     *
     * @return mixed
     */
    public function homeAction() {
        // standard behavior of showing results.
        return parent::resultsAction();
    }
}
