<?php

return array(
    'ixTheo' =>
        array(
            'search_backend' =>
                array(
                    'Solr' => 'ixTheo\\Search\\Factory\\SolrDefaultBackendFactory',
                ),
        ),
    'controllers' =>
        array(
            'invokables' =>
                array(
                    'BibleRangeSearch' => 'ixTheo\\Controller\\Search\\BibleRangeSearchController',
                    'KeywordChainSearch' => 'ixTheo\\Controller\\Search\\KeywordChainSearchController',
                    'Pipeline' => 'ixTheo\\Controller\\Pipeline',
                ),
        ),
    'router' =>
        array(
            'routes' =>
                array(
                    'biblerangesearch-home' =>
                        array(
                            'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
                            'options' =>
                                array(
                                    'route' => '/BibleRangeSearch/Home',
                                    'defaults' =>
                                        array(
                                            'controller' => 'BibleRangeSearch',
                                            'action' => 'Home',
                                        ),
                                ),
                        ),
                    'keywordchainsearch-home' =>
                        array(
                            'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
                            'options' =>
                                array(
                                    'route' => '/KeywordChainSearch/Home',
                                    'defaults' =>
                                        array(
                                            'controller' => 'KeywordChainSearch',
                                            'action' => 'Home',
                                        ),
                                ),
                        ),
                    'keywordchainsearch-results' =>
                        array(
                            'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
                            'options' =>
                                array(
                                    'route' => '/KeywordChainSearch/Results',
                                    'defaults' =>
                                        array(
                                            'controller' => 'KeywordChainSearch',
                                            'action' => 'Results',
                                        ),
                                ),
                        ),
                    'keywordchainsearch-search' =>
                        array(
                            'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
                            'options' =>
                                array(
                                    'route' => '/KeywordChainSearch/Search',
                                    'defaults' =>
                                        array(
                                            'controller' => 'KeywordChainSearch',
                                            'action' => 'Search',
                                        ),
                                ),
                        ),
                    'pipeline-home' =>
                        array(
                            'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
                            'options' =>
                                array(
                                    'route' => '/Pipeline/Home',
                                    'defaults' =>
                                        array(
                                            'controller' => 'Pipeline',
                                            'action' => 'Home',
                                        ),
                                ),
                        ),
                ),
        ),
    'vufind' =>
        array(
            'plugin_managers' =>
                array(
                    'recorddriver' =>
                        array(
                            'factories' =>
                                array(
                                    'solrmarc' => 'ixTheo\\RecordDriver\\Factory::getSolrMarc',
                                ),
                        ),
                    /*      'search_params' =>
                             array (
                               'factories' =>
                                  array(
                                    'Solr' => function ($sm) {
                                       $options = $sm->getServiceLocator()->get('VuFind\SearchOptionsPluginManager')->get('Solr');
                                          return new \ixTheo\Controller\Search\Params(
                                            clone($options), $sm->getServiceLocator()->get('VuFind\Config')
                                          );
                                     }
                                 ),
                            ),*/
                    /*      'search_options' =>
                             array (
                               'factories' =>
                                  array(
                                    'Solr' =>  function ($sm) {
                                         return new \ixTheo\Controller\Search\Options(
                                            $sm->getServiceLocator()->get('VuFind\Config')
                                 );
                                    }
                               ),
                            ), */
                ),
        ),
);
