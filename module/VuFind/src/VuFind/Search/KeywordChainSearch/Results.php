<?php
/**
 * Author aspect of the Search Multi-class (Results)
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * Based on Search_Author by Demian Katz
 *
 * @category VuFind2
 * @package  Search_KeywordChainSearch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
namespace VuFind\Search\KeywordChainSearch;

use VuFind\Search\Solr\Results as SolrResults;

/**
 *  Search Options
 *
 * @category VuFind2
 * @package  Search_KeywordChainSearch
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org   Main Site
 */
class Results extends SolrResults
{
    /**
     * Constructor
     *
     * @param \VuFind\Search\Base\Params $params Object representing user search
     * parameters.
     */
    public function __construct($params)
    {
        // Call parent constructor:
        parent::__construct($params);
    }

    /**
     * Is the current search saved in the database?
     *
     * @return bool
     */
    public function isSavedSearch()
    {
        return false;
    }

    protected function performSearch()
    {
        $query = $this->getParams()->getQuery();
        $params = $this->getParams()->getBackendParameters();

        $offset = ($this->getParams()->getPage() - 1) * $this->getParams()->getLimit();
        $limit = $this->getParams()->getLimit();

        $params->set("facet.offset", $offset);
        $params->set("facet.limit", $limit);

        // Perform the search:
        $collection = $this->getSearchService()->search($this->backendId, $query, 0, 0, $params);

        $this->responseFacets = $collection->getFacets();

        // Generate language extension and remove language subcode 
        $lang =  implode('', $params->get("lang"));
        $lang_ext = $lang ? "_" . split("-", $lang)[0] : "";

        $facet = 'key_word_chains_sorted' . $lang_ext;
        $facet_count = $facet . '-count';

        // Get the facets from which we will build our results:
        $facets = $this->getFacetList([$facet => null]);
        $count =  $this->getFacetList([$facet_count => null]);
        if (isset($facets[$facet])) {
            $this->resultTotal = $count[$facet_count]['list'][0]['count'];
            $this->results = $facets[$facet]['list'];
        }
    }

}
