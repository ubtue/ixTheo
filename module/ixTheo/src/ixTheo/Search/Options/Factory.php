<?php

namespace ixTheo\Search\Options;
use Zend\ServiceManager\ServiceManager;

class Factory extends \VuFind\Search\Options\Factory
{
    /**
     * Factory for Solr results object.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Solr
     */
    public static function getKeywordChainSearch(ServiceManager $sm)
    {
        $config = $sm->getServiceLocator()->get('VuFind\Config');
        return new \ixTheo\Search\KeywordChainSearch\Options($config);
    }
}
