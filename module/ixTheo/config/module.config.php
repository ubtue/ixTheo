<?php

$config = array(
    'ixTheo' =>
        array(
            'search_backend' =>
                array(
                    'Solr' => 'ixTheo\Search\Factory\IxTheoSolrDefaultBackendFactory',
                ),
        ),
    'controllers' =>
        array(
            'invokables' =>
                array(
                    'BibleRangeSearch' => 'ixTheo\Controller\Search\BibleRangeSearchController',
                    'KeywordChainSearch' => 'ixTheo\Controller\Search\KeywordChainSearchController',
                    'Pipeline' => 'ixTheo\Controller\Pipeline',
                    'my-research' => 'ixTheo\Controller\MyResearchController',
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
                                    'solrmarc' => 'ixTheo\RecordDriver\Factory::getSolrMarc',
                                ),
                        ),
                ),
        ),
);

$recordRoutes = array();
$dynamicRoutes = array();
$staticRoutes = array(
    'Browse/IxTheo-Classification',
    'Biblerangesearch/Home',
    'Keywordchainsearch/Home',
    'Keywordchainsearch/Results',
    'Keywordchainsearch/Search',
    'Pipeline/Home'
);

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;