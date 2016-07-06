<?php

$config = array(
    'ixTheo' =>
        array(
            'search_backend' =>
                array(
                    'Solr' => 'ixTheo\Search\Factory\IxTheoSolrDefaultBackendFactory',
                ),
        ),
    'vufind' => [
        'plugin_managers' => [
            'recorddriver' => [
                'factories' => [
                    'solrmarc' => 'ixTheo\RecordDriver\Factory::getSolrMarc',
                ],
            ],
            'search_results' => [
                'factories' => [
                    'subscriptions' => 'VuFind\Search\Results\Factory::getSubscriptions',
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'record' => 'ixTheo\Controller\Factory::getRecordController',
        ],
        'invokables' => [
            'BibleRangeSearch' => 'ixTheo\Controller\Search\BibleRangeSearchController',
            'KeywordChainSearch' => 'ixTheo\Controller\Search\KeywordChainSearchController',
            'Pipeline' => 'ixTheo\Controller\Pipeline',
            'MyResearch' => 'ixTheo\Controller\MyResearchController',
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'subscriptions' => 'VuFind\Controller\Plugin\Subscriptions',
        ]
    ],
);

$recordRoutes = array();
$dynamicRoutes = array();
$staticRoutes = array(
    'Browse/IxTheo-Classification',
    'Biblerangesearch/Home',
    'Keywordchainsearch/Home',
    'Keywordchainsearch/Results',
    'Keywordchainsearch/Search',
    'Pipeline/Home',
    'MyResearch/Subscriptions',
    'MyResearch/DeleteSubscription',
);

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;