<?php

namespace ixTheo\Controller;

class RecordController extends \VuFind\Controller\RecordController
{

    function processSubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        var_dump($post);
        $results = $this->loadRecord()->subscribe($post, $user);

        if ($results == null) {
            return $this->createViewModel();
        }

        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }

    function processUnsubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $this->loadRecord()->unsubscribe($post, $user);

        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }

    function subscribeAction()
    {
        // Process form submission:
        if ($this->params()->fromPost('action') == 'subscribe') {
            return $this->processSubscribe();
        } else if ($this->params()->fromPost('action') == 'unsubscribe') {
            return $this->processUnsubscribe();
        }

        // Retrieve user object and force login if necessary:
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $driver = $this->loadRecord();
        $table = $driver->getDbTable('Subscription');
        $recordId = $driver->getUniqueId();
        $userId = $user->id;

        $infoText = $this->forward()->dispatch('StaticPage', array(
            'action' => 'staticPage',
            'page' => 'SubscriptionInfoText'
        ));
        return $this->createViewModel(["subscription" => !($table->findExisting($userId, $recordId)), "infoText" => $infoText]);
    }


    function processPDASubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        var_dump($post);
        $results = $this->loadRecord()->pdasubscribe($post, $user);

        if ($results == null) {
            return $this->createViewModel();
        }

        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }



    function processPDAUnsubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $this->loadRecord()->unsubscribe($post, $user);

        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }


    function pdasubscribeAction()
    {
        // Process form submission:
        if ($this->params()->fromPost('action') == 'pdasubscribe') {
            return $this->processPDASubscribe();
        } else if ($this->params()->fromPost('action') == 'pdaunsubscribe') {
            return $this->processPDAUnsubscribe();
        }

        // Retrieve user object and force login if necessary:
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $driver = $this->loadRecord();
        $table = $driver->getDbTable('PDASubscription');
        $recordId = $driver->getUniqueId();
        $userId = $user->id;

        $infoText = $this->forward()->dispatch('StaticPage', array(
            'action' => 'staticPage',
            'page' => 'PDASubscriptionInfoText'
        ));
        return $this->createViewModel(["pdasubscription" => !($table->findExisting($userId, $recordId)), "infoText" => $infoText]);
    }
}
