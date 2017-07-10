<?php
namespace Ixtheo\Module\Config;

$config = [
    'vufind' => [
        'plugin_managers' => [
            'auth' => [
                'invokables' => [
                    'database' => 'ixTheo\Auth\Database',
                ],
            ],
            'db_table' => [
                'invokables' => [
                    'IxTheoUser' => 'ixTheo\Db\Table\IxTheoUser',
                    'subscription' => 'ixTheo\Db\Table\Subscription',
                    'pdasubscription' => 'ixTheo\Db\Table\PDASubscription',
                ],
            ],
            'recorddriver' => [
                'factories' => [
                    'solrmarc' => 'ixTheo\RecordDriver\Factory::getSolrMarc',
                ],
            ],
            'search_results' => [
                'factories' => [
                    'subscriptions' => 'ixTheo\Search\Results\Factory::getSubscriptions',
                    'pdasubscriptions' => 'ixTheo\Search\Results\Factory::getPDASubscriptions'
                ],
            ],
            'search_backend' => [
            	'factories' => [
                    'Solr' => 'ixTheo\Search\Factory\IxTheoSolrDefaultBackendFactory',
                ],
            ],
        ],
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrMarc' => [
                'tabs' => [
                    // Disable certain tabs (overwrite value with null)
                    'Excerpt' => null,
                    'HierarchyTree' => null,
                    'Holdings' => null,
                    'Map' => null,
                    'Preview' => null,
                    'Reviews' => null,
                    'Similar' => null,
                    'TOC' => null,
                    'UserComments' => null,
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            'browse' => 'ixTheo\Controller\Factory::getBrowseController',
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
            'subscriptions' => 'ixTheo\Controller\Plugin\Subscriptions',
            'pdasubscriptions' => 'ixTheo\Controller\Plugin\PDASubscriptions',
        ]
    ],
    'service_manager' => [
        'factories' => [
            'VuFind\Mailer' => 'ixTheo\Mailer\Factory',
        ],
    ],
];

$recordRoutes = [];
$dynamicRoutes = [];
$staticRoutes = [
    'Browse/IxTheo-Classification',
    'Browse/Publisher',
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
];

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
