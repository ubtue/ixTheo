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
            'autocomplete' => [
                'factories' => [
                    'solr' => 'ixTheo\Autocomplete\Factory::getSolr',
                ],
            ],
            'db_table' => [
                'invokables' => [
                    'IxTheoUser' => 'ixTheo\Db\Table\IxTheoUser',
                    'pdasubscription' => 'ixTheo\Db\Table\PDASubscription',
                    'subscription' => 'ixTheo\Db\Table\Subscription',

                ],
            ],
            'recorddriver' => [
                'factories' => [
                    'solrdefault' => 'ixTheo\RecordDriver\Factory::getSolrDefault',
                    'solrmarc' => 'ixTheo\RecordDriver\Factory::getSolrMarc',
                ],
            ],
            'search_backend' => [
            	'factories' => [
                    'Solr' => 'ixTheo\Search\Factory\IxTheoSolrDefaultBackendFactory',
                ],
            ],
            'search_options' => [
                'factories' => [
                    'KeywordChainSearch' => 'ixTheo\Search\Options\Factory::getKeywordChainSearch',
                    'PDASubscriptions' => 'ixTheo\Search\Options\Factory::getPDASubscriptions',
                    'Subscriptions' => 'ixTheo\Search\Options\Factory::getSubscriptions',
                ],
            ],
            'search_params' => [
                'abstract_factories' => ['ixTheo\Search\Params\PluginFactory'],
            ],
            'search_results' => [
                'factories' => [
                    'KeywordChainSearch' => 'ixTheo\Search\Results\Factory::getKeywordChainSearch',
                    'pdasubscriptions' => 'ixTheo\Search\Results\Factory::getPDASubscriptions',
                    'Subscriptions' => 'ixTheo\Search\Results\Factory::getSubscriptions',
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
            'alphabrowse' => 'ixTheo\Controller\AlphabrowseController',
            'BibleRangeSearch' => 'ixTheo\Controller\Search\BibleRangeSearchController',
            'feedback' => 'ixTheo\Controller\FeedbackController',
            'KeywordChainSearch' => 'ixTheo\Controller\Search\KeywordChainSearchController',
            'MyResearch' => 'ixTheo\Controller\MyResearchController',
            'Pipeline' => 'ixTheo\Controller\Pipeline',
            'search' => 'ixTheo\Controller\SearchController',
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

$recordRoutes = [
    // needs to be registered again even if already registered in parent module,
    // for the nonTabRecordActions added in \ixTheo\Route\RouteGenerator
    'record' => 'Record',
];
$dynamicRoutes = [];
$staticRoutes = [
    'Browse/IxTheo-Classification',
    'Browse/Publisher',
    'Browse/RelBib-Classification',
    'Biblerangesearch/Home',
    'Keywordchainsearch/Home',
    'Keywordchainsearch/Results',
    'Keywordchainsearch/Search',
    'MyResearch/Subscriptions',
    'MyResearch/DeleteSubscription',
    'MyResearch/PDASubscriptions',
    'MyResearch/DeletePDASubscription',
    'Pipeline/Home',
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

$routeGenerator = new \ixTheo\Route\RouteGenerator();
$routeGenerator->addRecordRoutes($config, $recordRoutes);
$routeGenerator->addDynamicRoutes($config, $dynamicRoutes);
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
