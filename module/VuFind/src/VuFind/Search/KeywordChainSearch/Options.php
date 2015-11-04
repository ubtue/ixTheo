<?

namespace VuFind\Search\KeywordChainSearch;


class Options extends  \VuFind\Search\Solr\Options{

   public function __construct (\VuFind\Config\PluginManager $configLoader){

	parent::__construct($configLoader);

   }

   public function getSearchAction(){

	return 'keywordchainsearch-search';

   }


   public function getSearchHomeAction(){

        return 'keywordchainsearch-home';
   }


  public function getAdvancedSearchAction(){

	return false;
  }


  // We only get Facets from Solr, so our resultLimit is
  // set to 0. Thus, we have to set this manually

  public function getVisibleSearchResultLimit(){

        return 20;

  }

}



?>
