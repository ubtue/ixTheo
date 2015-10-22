<?php

namespace ixTheo\Controller\Search;

class KeywordChainSearchController extends \VuFind\Controller\AbstractBase
{

   // Try to implement KWC based on the Browse Controller

   /**
    * VuFind configuration
    *
    * @var \Zend\Config\Config
    */
    protected $config;

    /**
    * Current browse mode
    *
    * @var string
    */
    protected $currentAction = null;


    protected function setCurrentAction($name)
    {
        $this->currentAction = $name;
    }

    /**
     * Get the name of the current action.
     *
     * @return string
     */
    protected function getCurrentAction()
    {
        return $this->currentAction;
    }





  protected function performSearch($view)
    {
        // Remove disabled facets
        $facets = $view->categoryList;
        foreach ($this->disabledFacets as $facet) {
            unset($facets[$facet]);
        }
        $view->categoryList = $facets;

        // SEARCH (Tag does its own search)
        if ($this->params()->fromQuery('query')
            && $this->getCurrentAction() != 'Tag'
        ) {
            $results = $this->getFacetList(
                $this->params()->fromQuery('facet_field'),
                $this->params()->fromQuery('query_field'),
                'count', $this->params()->fromQuery('query')
            );
            $resultList = [];
            foreach ($results as $result) {
                $resultList[] = [
                    'displayText' => $result['displayText'],
                    'value' => $result['value'],
                    'count' => $result['count']
                ];
            }
            // Don't make a second filter if it would be the same facet
            $view->paramTitle
                = ($this->params()->fromQuery('query_field') != $this->getCategory())
                ? 'filter[]=' . $this->params()->fromQuery('query_field') . ':'
                    . urlencode($this->params()->fromQuery('query')) . '&'
                : '';
            switch($this->getCurrentAction()) {
            case 'LCC':
                $view->paramTitle .= 'filter[]=callnumber-subject:';
                break;
            case 'Dewey':
                $view->paramTitle .= 'filter[]=dewey-ones:';
                break;
            default:
                $view->paramTitle .= 'filter[]=' . $this->getCategory() . ':';
            }
            $view->paramTitle = str_replace(
                '+AND+',
                '&filter[]=',
                $view->paramTitle
            );
            $view->resultList = $resultList;
        }
//        $view->setTemplate('browse/home');
        return $view;
    }


  /**
     * Get a list of items from a facet.
     *
     * @param string $facet    which facet we're searching in
     * @param string $category which subfacet the search applies to
     * @param string $sort     how are we ranking these? || 'index'
     * @param string $query    is there a specific query? No = wildcard
     *
     * @return array           Array indexed by value with text of displayText and
     * count
     */
    protected function getFacetList($facet, $category = null,
        $sort = 'count', $query = '[* TO *]'
    ) {
        $results = $this->getServiceLocator()
            ->get('VuFind\SearchResultsPluginManager')->get('Solr');
        $params = $results->getParams();
        $params->addFacet($facet);
        if ($category != null) {
            $query = $category . ':' . $query;
        } else {
            $query = $facet . ':' . $query;
        }
        $params->setOverrideQuery($query);
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        // Get limit from config
//        $params->setFacetLimit($this->config->Browse->result_limit);
        $params->setFacetLimit(100);
        $params->setLimit(1);
        // Facet prefix
        if ($this->params()->fromQuery('facet_prefix')) {
            $params->setFacetPrefix($this->params()->fromQuery('facet_prefix'));
        }
        $params->setFacetSort($sort);
        $result = $results->getFacetList();
        if (isset($result[$facet])) {
            // Sort facets alphabetically if configured to do so:
            if (isset($this->config->Browse->alphabetical_order)
                && $this->config->Browse->alphabetical_order
            ) {
                $callback = function ($a, $b) {
                    return strcoll($a['displayText'], $b['displayText']);
                };
                usort($result[$facet]['list'], $callback);
            }
            return $result[$facet]['list'];
        } else {
            return [];
        }
    }


    /**
     * Helper class that adds quotes around the values of an array
     *
     * @param array $array Two-dimensional array where each entry has a value param
     *
     * @return array       Array indexed by value with text of displayText and count
     */
    protected function quoteValues($array)
    {
        foreach ($array as $i => $result) {
            $result['value'] = '"' . $result['value'] . '"';
            $array[$i] = $result;
        }
        return $array;
    }


  /**
     * Get Keywordchains as facets
     *
     * @param string $facet    which facet we're searching in
     * @param string $category which subfacet the search applies to
     * @param string $sort     how are we ranking these? || 'index'
     * @param string $query    is there a specific query? No = wildcard
     *
     * @return array           Array indexed by value with text of displayText and
     * count
     */    

    protected function getKeywordChainAsFacets($facet, $category = null,
                                               $sort = 'index', $query = '*'
    ){
        $results = $this->getServiceLocator()
                  ->get('VuFind\SearchResultsPluginManager')->get('Solr'); 

        $params = $results->getParams();
        $params->addFacet($facet);
        if ($category != null) {
            $query = $category . ':' . $query;
        } else {
            $query = $facet . ':' . $query;
        }

        $query='{!keywordChainParser}' . ':' . 'Gesch*';
        
        $params->setOverrideQuery($query);
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        $params->setFacetLimit(-1);
        $params->setLimit(0);
        $params->setOverrideQuery($query);
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        $params->setFacetPrefix($this->params()->fromQuery('facet_prefix'));
        $params->setFacetSort($sort);
        $result = $results->getFacetList();

        if (isset($result[$facet])) {
            return $result[$facet]['list'];
        } else {
            return [];
        }

     }

 
    /**
     * Create a new ViewModel.
     *
     * @param array $params Parameters to pass to ViewModel constructor.
     *
     * @return \Zend\View\Model\ViewModel
     */

    protected function createViewModel($params = null)
    {
        $view = parent::createViewModel($params);

        // Set the current action.
        $currentAction = $this->getCurrentAction();
        if (!empty($currentAction)) {
            $view->currentAction = $currentAction;
        }


        $results = $this->getKeywordChainAsFacets('key_word_chains');

        $resultList = [];
        foreach ($results as $result) {
            $resultList[] = [
               'displayText' => $result['displayText'],
               'value' => $result['value'],
               'count' => $result['count']
            ];
        }

	$view->resultList = $resultList;


//	$this->performSearch($view);
	
        return $view;
    }


     /**
	  *
	  * @return \Zend\View\Model\ViewModel
	  */
	 public function homeAction()
	 {
		return $this->createViewModel();
	 }

	
}
?>
