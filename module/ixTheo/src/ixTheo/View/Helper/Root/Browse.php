<?php
namespace ixTheo\View\Helper\Root;

class Browse extends \VuFind\View\Helper\Root\Browse
{
    /**
     * Get the Solr field associated with a particular browse action.
     *
     * @param string $action Browse action
     * @param string $backup Backup browse action if no match is found for $action
     *
     * @return string
     */
    public function getSolrField($action, $backup = null)
    {
        $action = strToLower($action);
        $backup = strToLower($backup);
        switch($action) {
        case 'dewey':
            return 'dewey-hundreds';
        case 'lcc':
            return 'callnumber-first';
        case 'author':
            return 'author_facet';
        case 'topic':
            return 'topic_facet';
        case 'genre':
            return 'genre_facet';
        case 'region':
            return 'geographic_facet';
        case 'era':
            return 'era_facet';
        case 'ixtheo-classification':
            return 'ixtheo_notation_facet';
        }
        if ($backup == null) {
            return $action;
        }
        return $this->getSolrField($backup);
    }
}
