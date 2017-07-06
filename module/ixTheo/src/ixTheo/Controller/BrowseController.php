<?php

namespace ixTheo\Controller;
use VuFind\Exception\Forbidden as ForbiddenException;

class BrowseController extends \VuFind\Controller\BrowseController
{
    /**
     * Browse ixTheo notations
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function ixTheoClassificationAction()
    {
        $categoryList = [
            'alphabetical' => 'By Categories',
            'lcc' => 'By Call Number',
            'topic' => 'By Topic',
            'genre' => 'By Genre',
            'region' => 'By Region',
            'era' => 'By Era',
            'publisher' => 'By Publisher'
        ];

        return $this->performBrowse('IxTheo-Classification', $categoryList, true);
    }

    public function relBibClassificationAction()
    {
        $categoryList = [
            'alphabetical' => 'By Categories',
        ];

        return $this->performBrowse('RelBib-Classification', $categoryList, true);
    }
}
