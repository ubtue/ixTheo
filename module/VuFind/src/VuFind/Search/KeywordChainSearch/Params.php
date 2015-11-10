<?

//namespace ixTheo\Controller\Search;
namespace VuFind\Search\KeywordChainSearch;

class Params extends \VuFind\Search\Solr\Params {

  public function __construct($options, \VuFind\Config\PluginManager $configLoader){

	parent::__construct($options, $configLoader);

  }


   protected function initBasicSearch($request)
    {
        // If no lookfor parameter was found, we have no search terms to
        // add to our array!
        if (is_null($lookfor = $request->get('lookfor'))) {
            return false;
        }

        $this->setBasicSearch($lookfor, 'keywordChainSearch');
        return true;
    }



}

?>




