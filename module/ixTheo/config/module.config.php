<?php
namespace ixTheo\Module\Configuration;

$config = [
	'ixTheo' => [
		 'search_backend' => [
		 		  'Solr' => 'ixTheo\Search\Factory\SolrDefaultBackendFactory'
		 ]
	]
];

return $config;