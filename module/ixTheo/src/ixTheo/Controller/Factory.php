<?php

namespace ixTheo\Controller;

use Zend\ServiceManager\ServiceManager;

class Factory extends \VuFind\Controller\Factory
{
    public static function getRecordController(ServiceManager $sm)
    {
        return new RecordController(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config')
        );
    }
}