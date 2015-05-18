<?php

namespace ixTheo\Search\Factory;

use VuFind\Search\Factory\SolrDefaultBackendFactory;
use ixTheo\Search\Backend\Solr\IxTheoQueryBuilder;
use VuFindSearch\Backend\Solr\LuceneSyntaxHelper;

class IxTheoSolrDefaultBackendFactory extends SolrDefaultBackendFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create the query builder.
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        $specs   = $this->loadSpecs();
        $config = $this->config->get('config');
        $defaultDismax = isset($config->Index->default_dismax_handler)
                       ? $config->Index->default_dismax_handler : 'dismax';
        $builder = new IxTheoQueryBuilder($specs, $defaultDismax);

        // Configure builder:
        $search = $this->config->get($this->searchConfig);
        $caseSensitiveBooleans
            = isset($search->General->case_sensitive_bools)
            ? $search->General->case_sensitive_bools : true;
        $caseSensitiveRanges
            = isset($search->General->case_sensitive_ranges)
            ? $search->General->case_sensitive_ranges : true;
        $helper = new LuceneSyntaxHelper(
            $caseSensitiveBooleans, $caseSensitiveRanges
        );
        $builder->setLuceneHelper($helper);
        return $builder;
    }
}