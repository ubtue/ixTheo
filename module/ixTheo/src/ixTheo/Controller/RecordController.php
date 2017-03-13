<?php

namespace ixTheo\Controller;
use Zend\Mail\Address;

class RecordController extends \VuFind\Controller\RecordController
{

    function processSubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $results = $this->loadRecord()->subscribe($post, $user);

        if ($results == null) 
            return $this->createViewModel();

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


    function getUserData($userId, &$userData) {

       $userTable = $this->loadRecord()->getDbTable('User');
       $select = $userTable->getSql()->select()->where(['id' => $userId]);
       $userRow = $userTable->selectWith($select)->current();

       $ixtheoUserTable = $this->loadRecord()->getDbTable('IxTheoUser');
       $ixtheoSelect = $ixtheoUserTable->getSql()->select()->where(['id' => $userId]);
       $ixtheoUserRow = $ixtheoUserTable->selectWith($ixtheoSelect)->current();
       $ixtheoUserData = [ $ixtheoUserRow->title != "Other" ? $ixtheoUserRow->title : "", $ixtheoUserRow->country];
       $userData = [ $ixtheoUserRow->title != "Other" ? $ixtheoUserRow->title . " " : "" . 
                     $userRow->firstname . " " .  $userRow->lastname, $userRow->email,
                     $ixtheoUserRow->country ];
    }


    function sendPDANotificationEmail($post, $user, $data) {

        $this->getUserData($user->id, $userData);

        $recipient_email = "johannes.riedl@uni-tuebingen.de";
        $recipient_name = "Test";
        $sender_email = "ixtheo-noreply@uni-tuebingen.de";
        $sender_name = "PDA Mail Agent";
        $email_subject = "PDA Bestellung";
        $address_for_dispatch = $post['addressfield'];
        $title = $this->loadRecord()->getDbTable('PDASubscription');

        $email_message = "Benutzer:\n" .  implode("\n", $userData) . "\n\n" . 
                         "Versandaddresse:\n" . $address_for_dispatch . "\n\n" .
                         "Titel:\n" . implode(", ", array_diff_key($data, [0, 1]));

        try {
        $mailer = $this->getServiceLocator()->get('VuFind\Mailer');
            $mailer->send(
                 new Address($recipient_email, $recipient_name),
                 new Address($sender_email, $sender_name),
                 $email_subject, $email_message
             );
        } catch (MailException $e) {
            $this->flashMessenger()->addMessage($e->getMessage(), 'Error sending email');
        }
    }

    function processPDASubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $results = $this->loadRecord()->pdasubscribe($post, $user, $data);
        if ($results == null) {
            return $this->createViewModel();
        }
        $this->sendPDANotificationEmail($post, $user, $data);
        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }

    function processPDAUnsubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $this->loadRecord()->pdaunsubscribe($post, $user);
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
