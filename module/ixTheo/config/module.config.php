<?php

return array (
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
      'KeywordChainSearch' => 'ixTheo\\Controller\\Search\\KeywordChainSearchController',
    ),
  ),
  'router' => 
  array (
    'routes' => 
    array (
      'biblerangesearch-home' => 
      array (
        'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
        'options' => 
        array (
          'route' => '/BibleRangeSearch/Home',
          'defaults' => 
          array (
            'controller' => 'BibleRangeSearch',
            'action' => 'Home',
          ),
        ),
      ),
      'keywordchainsearch-home' => 
      array (
        'type' => 'Zend\\Mvc\\Router\\Http\\Literal',
        'options' => 
        array (
          'route' => '/KeywordChainSearch/Home',
          'defaults' => 
          array (
            'controller' => 'KeywordChainSearch',
            'action' => 'Home',
          ),
        ),
      ),
    ),
  ),
  'vufind' => 
  array (
    'plugin_managers' => 
    array (
      'recorddriver' => 
      array (
        'factories' => 
        array (
          'solrmarc' => 'ixTheo\\RecordDriver\\Factory::getSolrMarc',
        ),
      ),
    ),
  ),
);