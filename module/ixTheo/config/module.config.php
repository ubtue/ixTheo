<?php

$config = array (
  'ixTheo' => 
  array (
    'search_backend' => 
    array (
      'Solr' => 'ixTheo\\Search\\Factory\\SolrDefaultBackendFactory',
    ),
  ),
  'controllers' => 
  array (
    'invokables' => 
    array (
      'BibleRangeSearch' => 'ixTheo\\Controller\\Search\\BibleRangeSearchController',
      'KeywordChainSearch' => 'ixTheo\\Controller\\Search\\KeywordChainSearchController'
    ),
  ),
);

$staticRoutes = array (
    'BibleRangeSearch/Home',
    'KeywordChainSearch/Home'
);

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;
