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

    function getUserData($userId) {
       $userTable = $this->loadRecord()->getDbTable('User');
       $select = $userTable->getSql()->select()->where(['id' => $userId]);

       $userRow = $userTable->selectWith($select)->current();
       $ixtheoUserTable = $this->loadRecord()->getDbTable('IxTheoUser');
       $ixtheoSelect = $ixtheoUserTable->getSql()->select()->where(['id' => $userId]);
       $ixtheoUserRow = $ixtheoUserTable->selectWith($ixtheoSelect)->current();
       $userData = [ 'title' => $ixtheoUserRow->title != "Other" ? $ixtheoUserRow->title . " " : "",
                     'firstname' => $userRow->firstname,
                     'lastname' =>  $userRow->lastname,
                     'email' => $userRow->email,
                     'country' => $ixtheoUserRow->country ];
       return $userData;
    }


    function formatUserData($userData) {
       return [ ($userData['title'] != "" ? $userData['title'] . " " : "") . $userData['firstname'] . " " . $userData['lastname'],
                $userData['email'],
                $userData['country']
              ];
    }


    function sendPDAEmail($recipient_email, $recipient_name, $sender_email, $sender_name, $email_subject, $email_message) {
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


    function sendPDANotificationEmail($post, $user, $data) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $recipientData = $this->getPDAInstitutionRecipientData();
        $email_subject = "PDA Bestellung";
        $address_for_dispatch = $post['addressfield'];
        $email_message = "Benutzer:\n" .  implode("\n", $userData) . "\n\n" .
                         "Versandaddresse:\n" . $address_for_dispatch . "\n\n" .
                         "Titel:\n" . $this->getBookInformation();
        $this->sendPDAEmail($recipientData['email'], $recipientData['name'], $senderData['email'], $senderData['name'], $email_subject, $email_message);
    }


    function getBookInformation() {
        $driver = $this->loadRecord();
        $year = $driver->getPublicationDates()[0];
        $isbn = $driver->getISBNs()[0];
        return $driver->getAuthorsAsString() . ": " .
               $driver->getTitle() . " " .
               ($year != "" ? "(" . $year. ")" : "") . " " .
               ($isbn != "" ? "ISBN: " . $isbn : "");
    }


    function getPDASenderData() {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('config');
        $site = isset($config->Site) ? $config->Site : null;
        $senderEmail = isset($site->pda_sender) ? $site->pda_sender : null;
        $senderName = isset($site->pda_sender_name) ? $site->pda_sender_name : null;
        return ['email' => $senderEmail, 'name' => $senderName ];
    }


    function getPDAInstitutionRecipientData() {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('config');
        $site = isset($config->Site) ? $config->Site : null;
        $email = isset($site->pda_email) ?  $site->pda_email : null;
        $name = "UB Tübingen PDA";
        return ['email' => $email, 'name' => $name];
    }


    function sendPDAUserNotificationEmail($post, $user, $data) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $recipient_email = $userData[1];
        $recipient_name = $userData[0];
        $current_lang = $this->serviceLocator->get('Vufind\Translator')->getLocale();
        $email_subject = "Ihre PDA Bestellung";

/*        $infoText = $this->render($this->forward()->dispatch('StaticPage', array(
            'action' => 'staticPage',
            'page' => 'PDASubscriptionInfoText'
        )));*/
/*        $postal_address = $this->transEsc("You provided the following address") . ":\n" . $post['addressfield'] . "\n\n";
        $userDataText =  $this->transEsc("The personal information about you is") . ":\n" . implode("\n", $userData) . "\n\n";
        $bookInformation = $this->transEsc("Book Information") . ":\n" . implode(", ", array_diff_key($data, [0, 1])) . "\n\n";*/

        $postal_address = "You provided the following address" . ":\n" . $post['addressfield'] . "\n\n";
        $userDataText =  "The personal information about you is" . ":\n" . implode("\n", $userData) . "\n\n";
        $bookInformation = "Book Information" . ":\n" . $this->getBookInformation() . "\n\n";
        $email_message = $userDataText . $postal_address . $bookInformation;
        $this->sendPDAEmail($recipient_email, $recipient_name, $senderData['email'], $senderData['name'], $email_subject, $email_message); 
    }


    function SendPDAUnsubscribeEmail($user) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $email_subject = "Abbestellung PDA-Auftrag";
        $recipientData = $this->getPDAInstitutionRecipientData();
        $email_message = "Abbestellung: " . $this->getBookInformation() . "\n\n" .
                         "für: " . $userData[0] . "(" . $userData[1] . ")";
        $this->sendPDAEmail($recipientData['email'], $recipientData['name'], $senderData['email'], $senderData['name'], $email_subject, $email_message);
    }


    function SendPDAUserUnsubscribeEmail($user) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $email_subject = "Abbestellung Ihres PDA-Auftrags";
        $recipient_name = $userData[0];
        $recipient_email = $userData[1];
        $email_message = "Unsubscribe: " . $this->getBookInformation();
        $this->sendPDAEmail($recipient_email, $recipient_name, $senderData['email'], $senderData['name'], $email_subject, $email_message);
    }


    function processPDASubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $results = $this->loadRecord()->pdaSubscribe($post, $user, $data);
        if ($results == null) {
            return $this->createViewModel();
        }
        $this->sendPDANotificationEmail($post, $user, $data);
        $this->sendPDAUserNotificationEmail($post, $user, $data);
        $this->flashMessenger()->addMessage("Success", 'success');
        return $this->redirectToRecord();
    }


    function processPDAUnsubscribe()
    {
        if (!($user = $this->getUser())) {
            return $this->forceLogin();
        }
        $post = $this->getRequest()->getPost()->toArray();
        $this->loadRecord()->pdaUnsubscribe($post, $user);
        $this->flashMessenger()->addMessage("Success", 'success');
        $this->sendPDAUnsubscribeEmail($user);
        $this->sendPDAUserUnsubscribeEmail($user);
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
        $fields = $driver->fields;
        $bookDescription = $driver->getAuthorsAsString() . ": " .
                           $driver->getTitle() .  ($driver->getYear() != "" ? "(" . $driver->getYear() . ")" : "") .
                           ", ISBN: " . $driver->getISBNs()[0];
        return $this->createViewModel(["pdasubscription" => !($table->findExisting($userId, $recordId)), "infoText" => $infoText,
                                       "bookDescription" => $bookDescription]);
    }
}
