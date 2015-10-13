<?php

namespace ixTheo\Controller\Search;

class KeywordChainSearchController extends \VuFind\Controller\AbstractBase
{
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
