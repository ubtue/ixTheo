<?php

namespace ixTheo\Controller\Search;

class KeywordChainSearchController extends \VuFind\Controller\AbstractSearch
{

   // Try to implement KWC based on the Browse Controller


   /**
     * Constructor
     */
    public function __construct()
    {
        $this->searchClassId = 'KeywordChainSearch';
        parent::__construct();
    }



   /**
    * VuFind configuration
    *
    * @var \Zend\Config\Config
    */
    protected $config;


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
                                               $sort = 'index', $query = '*', $request
        ){
	$results = $this->getResultsManager()->get($this->searchClassId);
        $params = $results->getParams();
        $params->addFacet($facet);
//	$query = $this->appendWildcard($query);

	$options = $params->getOptions();
	
//	if($query == '')
//		return [];


	$lookfor = $request->get('lookfor');

//	if (substr($lookfor, -1) != '*'){
//	   $lookfor = $this->appendWildcard($lookfor);
//	}

	$request->set('lookfor', $lookfor);
	
	$request->set('type', 'KeywordChainSearch');

	$params->initFromRequest($request);
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        //$params->setLimit(30);
        //$params->setFacetPrefix($this->params()->fromQuery('facet_prefix'));
        $params->setFacetSort($sort);
        $params->setFacetOffset(($params->getPage() - 1) * $params->getLimit());
        $params->setFacetLimit(-1);
	$results->setParams($params);

	return $results;

     }





    protected function configureKeywordChainSearch($request, $sort){

	$facet = 'key_word_chains';
      
        $results = $this->getResultsManager()->get($this->searchClassId);
        $params = $results->getParams();
        $params->addFacet($facet);

        $options = $params->getOptions();

        $lookfor = $request->get('lookfor');

	
//	if (substr($lookfor, -1) != '*'){
//          $lookfor = $this->appendWildcard($lookfor);
//        }

        $request->set('lookfor', $lookfor);

        $request->set('type', 'KeywordChainSearch');
        $params->initFromRequest($request);
        $params->getOptions()->disableHighlighting();
        $params->getOptions()->spellcheckEnabled(false);
        $params->setFacetSort($sort);
        $params->setFacetOffset(($params->getPage() - 1) * $params->getLimit());
        $params->setFacetLimit(-1);
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

    }


     /**
       *
       * @return \Zend\View\Model\ViewModel
       */
     public function homeAction(){

	  $params = $this->getServiceLocator()->get('VuFind\SearchParamsPluginManager')->get('KeywordChainSearch');
          return parent::createViewModel(['params' => $params]);
    }




     public function resultsAction(){

	    $facet = 'key_word_chains';

 	    $request =  new \Zend\Stdlib\Parameters(
              $this->getRequest()->getQuery()->toArray()
              + $this->getRequest()->getPost()->toArray()
            );

	     $query = $this->getRequest()->getQuery()->get('lookfor');

             $results = $this->configureKeywordChainSearch($request, 'index');

	     $params = (!empty($results)) ? $results->getParams() : [];
	

	     if (!empty($results)){
               $view = parent::createViewModel(['params' => $params, 'results' => $results]);	
              }
             else{
	       $view = parent::createViewModel(['params' => $params]);
             }

	     $result_facets = (!empty($results)) ? $results->getFacetList() : [];

             return $view;
	}



	public function searchAction(){

	  $this->forwardTo('KeywordChainSearch', 'Results');

	}
}
?>
