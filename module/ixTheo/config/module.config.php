<?php
namespace Ixtheo;

$config = array(
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
                    'pdasubscriptions' => 'VuFind\Search\Results\Factory::getPDASubscriptions'
                ],
            ],
            'search_backend' => [
            	'factories' => [
                    'Solr' => 'ixTheo\Search\Factory\IxTheoSolrDefaultBackendFactory',
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
            'StaticPage' => 'ixTheo\Controller\StaticPageController',
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'subscriptions' => 'VuFind\Controller\Plugin\Subscriptions',
            'pdasubscriptions' => 'VuFind\Controller\Plugin\PDASubscriptions',
        ]
    ],
);

$recordRoutes = array();
$dynamicRoutes = array();
$staticRoutes = array(
    'Browse/IxTheo-Classification',
    'Browse/RelBib-Classification',
    'Biblerangesearch/Home',
    'Keywordchainsearch/Home',
    'Keywordchainsearch/Results',
    'Keywordchainsearch/Search',
    'Pipeline/Home',
    'MyResearch/Subscriptions',
    'MyResearch/DeleteSubscription',
    'MyResearch/PDASubscriptions',
    'MyResearch/DeletePDASubscription'
);

$config['router']['routes']['static-page'] = [
    'type'    => 'Zend\Mvc\Router\Http\Segment',
    'options' => [
        'route'    => "/:page",
        'constraints' => [
            'page'     => '[a-zA-Z][a-zA-Z0-9_-]*',
        ],
        'defaults' => [
            'controller' => 'StaticPage',
            'action'     => 'staticPage',
        ]
    ]
];

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
