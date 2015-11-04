<?php

namespace ixTheo\Controller\Search;

class KeywordChainSearchController extends \VuFind\Controller\AbstractSearch
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
    * Attach Wildcard to each part of the query string
    * 
    *
    */
    
    protected function appendWildcard($query){

	return preg_replace('~(\w+)~', '$1*', $query);

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
                  ->get('VuFind\SearchResultsPluginManager')->get('KeywordChainSearch'); 
        $params = $results->getParams();
        $params->addFacet($facet);
	$query = $this->appendWildcard($query);


// TEST ONLY

	// We are trying to adjust params and options to match our specific requirements 
	// for KWC

	$options = $params->getOptions();

	// Set the search base to KWC
	// Set the type
	// Disable advanced search
	// Disable Search Type selection



// END TEST ONLY


	
	// Make sure we do not get intractable response times 
	// due to huge result list
	
	if($query == '')
		return [];


	$query = ($query != '') ? $query : '*';

	$query = 'key_word_chain_bag' . ':' . "(" . $query . ")";
        
        $params->setOverrideQuery($query);
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        $params->setFacetLimit(-1);
        $params->setLimit(0);
        $params->setFacetPrefix($this->params()->fromQuery('facet_prefix'));
        $params->setFacetSort($sort);
        $params->setFacetOffset(($params->getPage() - 1) * $params->getLimit());
        $params->setFacetLimit($params->getLimit() ? $params->getLimit() : 20);

	$results->setParams($params);

	return $results;

     }

 
    /**
     * Create a new ViewModel.
     *
     * @param array $params Parameters to pass to ViewModel constructor.
     *
     * @return \Zend\View\Model\ViewModel
     */

    protected function createViewModel($params_ext = null)
    {

	$facet = 'key_word_chains';

	$query = $this->getRequest()->getQuery()->get('lookfor');

        $results = $this->getKeywordChainAsFacets($facet, null, 'index', $query);

	$params = (!empty($results)) ? $results->getParams() : [];
	
	$this->resultScroller()->init($results);

// TEST ONLY
	// Does retrieving facet before make the difference ???

	//if (!empty($results)){
	//  $tmp = $results->getFacetList();
	//}

// END TEST ONLY

	if (!empty($results)){
       //   $view = parent::createViewModel(['params' => $params, 'results' => $results->getResults()]);	
          $view = parent::createViewModel(['params' => $params, 'results' => $results]);	
        }
        else{
	  $view = parent::createViewModel(['params' => $params]);
        }
//        $view = parent::createViewModel();

	$result_facets = (!empty($results)) ? $results->getFacetList() : [];


        $resultList = [];
        foreach ($result_facets[$facet]['list'] as $result) {
            $resultList[] = [
               'displayText' => $result['displayText'],
               'value' => $result['value'],
               'count' => $result['count']
            ];
        }

	$view->resultList = $resultList;

	
        return $view;
    }


     /**
	  *
	  * @return \Zend\View\Model\ViewModel
	  */
	 public function homeAction()
	 {

	    $params = $this->getServiceLocator()->get('VuFind\SearchParamsPluginManager')->get('KeywordChainSearch');
	    $options = $this->getServiceLocator()->get('VuFind\SearchOptionsPluginManager')->get('KeywordChainSearch');
	    $params->setOptions($options);
	
            return parent::createViewModel(['params' => $params]);
	 }


	public function resultsAction(){

           $this->searchClassId = 'KeywordChainSearch';
	   return $this->createViewModel();
	}



	public function searchAction(){

           $this->searchClassId = 'KeywordChainSearch';
//	   return parent::resultsAction();
	  $this->forwardTo('KeywordChainSearch', 'Results');

	}


     /**
     * Is the result scroller active?
     *
     * @return bool
     */
    protected function resultScrollerActive()
    {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('config');
        return (isset($config->Record->next_prev_navigation)
            && $config->Record->next_prev_navigation);
    }
	
}
?>
