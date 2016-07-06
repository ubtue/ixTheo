<?php

namespace ixTheo\Controller;
use VuFind\Search\RecommendListener;

class MyResearchController extends \VuFind\Controller\MyResearchController
{
    function subscriptionsAction()
    {
        // Fail if lists are disabled:
        if (!$this->listsEnabled()) {
            throw new \Exception('Lists disabled');
        }

        // Check for "delete item" request; parameter may be in GET or POST depending
        // on calling context.
        $deleteId = $this->params()->fromPost(
            'delete', $this->params()->fromQuery('delete')
        );
        if ($deleteId) {
            $deleteSource = $this->params()->fromPost(
                'source',
                $this->params()->fromQuery('source', DEFAULT_SEARCH_BACKEND)
            );
            // If the user already confirmed the operation, perform the delete now;
            // otherwise prompt for confirmation:
            $confirm = $this->params()->fromPost(
                'confirm', $this->params()->fromQuery('confirm')
            );
            if ($confirm) {
                $success = $this->performDeleteSubscription($deleteId, $deleteSource);
                if ($success !== true) {
                    return $success;
                }
            } else {
                return $this->confirmDeleteSubscription($deleteId, $deleteSource);
            }
        }

        // If we got this far, we just need to display the subscriptions:
        try {
            $runner = $this->getServiceLocator()->get('VuFind\SearchRunner');

            // We want to merge together GET, POST and route parameters to
            // initialize our search object:
            $request = $this->getRequest()->getQuery()->toArray()
                + $this->getRequest()->getPost()->toArray()
                + ['id' => $this->params()->fromRoute('id')];

            // Set up listener for recommendations:
            $rManager = $this->getServiceLocator()->get('VuFind\RecommendPluginManager');
            $setupCallback = function ($runner, $params, $searchId) use ($rManager) {
                $listener = new RecommendListener($rManager, $searchId);
                $listener->setConfig(
                    $params->getOptions()->getRecommendationSettings()
                );
                $listener->attach($runner->getEventManager()->getSharedManager());
            };

            $results = $runner->run($request, 'Subscriptions', $setupCallback);
            return $this->createViewModel(
                ['params' => $results->getParams(), 'results' => $results]
            );
        } catch (ListPermissionException $e) {
            if (!$this->getUser()) {
                return $this->forceLogin();
            }
            throw $e;
        }
    }

    function confirmDeleteSubscription($deleteId, $deleteSource) {

    }
    function performDeleteSubscription($deleteId, $deleteSource) {

    }

    function DeleteSubscriptionAction() {

    }
}
