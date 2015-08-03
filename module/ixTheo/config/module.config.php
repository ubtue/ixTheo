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
      'Keywordchains' => 'ixTheo\\Controller\\KeyWordChainsController',
    ),
  ),
);

$staticRoutes = array (
    'Keywordchains/Home'
);

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addStaticRoutes($config, $staticRoutes);

return $config;