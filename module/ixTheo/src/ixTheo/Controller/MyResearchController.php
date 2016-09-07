<?php

namespace ixTheo\Controller;
use VuFind\Search\RecommendListener,
    VuFind\Exception\ListPermission as ListPermissionException;

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
        var_dump("confirmDeleteSubscription");

    }
    function performDeleteSubscription($id, $deleteSource) {
        // Force login:
        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }

        // Load/check incoming parameters:
        if (empty($id)) {
            throw new \Exception('Cannot delete empty ID!');
        }

        $table = $this->getTable('Subscription');
        $table->unsubscribe($user->id, $id);
        return true;
    }

    function DeleteSubscriptionAction() {
        var_dump("DeleteSubscriptionAction");
    }


    public function profileAction($request)
    {

        $user = $this->getUser();
        if (!$user) {
            return $this->forceLogin();
        }
        $table = $this->getTable('IxTheoUser');
        $ixTheoUser = $table->get($user->id);;

        if (!empty($this->getRequest()->getPost("submit"))) {
            $this->updateProfile($this->getRequest(), $user, $ixTheoUser);
        }
        $view = $this->createViewModel();
        $view->user= $user;
        $view->ixTheoUser = $ixTheoUser;
        $view->request = $this->mergePostDataWithUserData($this->getRequest()->getPost(), $user, $ixTheoUser);
        return $view;
    }

    private function updateProfile($request, $user, $ixTheoUser) {
        $params = [
            'firstname' => '', 'lastname' => '',
            'title' => '', 'institution' => '', 'country' => '',
            'language' => '', 'sex' => ''
        ];
        foreach ($params as $param => $default) {
            $params[$param] = $request->getPost()->get($param, $default);
        }
        $this->getAuthManager()->getAuth()->createOrUpdateIxTheoUser($params, $user, $ixTheoUser);
    }

    private function mergePostDataWithUserData($post, $user, $ixTheoUser) {
        $fields = ['email', 'username', 'sex', 'title', 'firstname', 'lastname', 'institution', 'country'];
        foreach ($fields as $field) {
            if (!$post->$field) {
                $post->$field = $user->offsetExists($field) ? $user->$field : $ixTheoUser->$field;
            }
        }
        if (!$post->language) {
            $post->language = $ixTheoUser->language ?: $this->layout()->userLang;
        }
        return $post;
    }
}
