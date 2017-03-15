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


    /*
     * Generic Mail send function
     */

    function sendEmail($recipientEmail, $recipientName, $senderEmail, $senderName, $emailSubject, $emailMessage) {
        try {
            $mailer = $this->getServiceLocator()->get('VuFind\Mailer');
            $mailer->send(
                 new Address($recipientEmail, $recipientName),
                 new Address($senderEmail, $senderName),
                 $emailSubject, $emailMessage
             );
        } catch (MailException $e) {
            $this->flashMessenger()->addMessage($e->getMessage(), 'Error sending email');
        }
    }


    /*
     * Send notification to library
     */

    function sendPDANotificationEmail($post, $user, $data) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $recipientData = $this->getPDAInstitutionRecipientData();
        $emailSubject = "PDA Bestellung";
        $addressForDispatch = $post['addressfield'];
        $emailMessage = "Benutzer:\n" .  implode("\n", $userData) . "\n\n" .
                         "Versandaddresse:\n" . $addressForDispatch . "\n\n" .
                         "Titel:\n" . $this->getBookInformation();
        $this->sendEmail($recipientData['email'], $recipientData['name'], $senderData['email'], $senderData['name'], $emailSubject, $emailMessage);
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


    /*
     * Get sender Mail addresses from site configuration
     */

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
        $recipientEmail = $userData[1];
        $recipientName = $userData[0];
        $emailSubject = $this->translate("Your PDA Order");
        $postalAddress = $this->translate("You provided the following address") . ":\n" . $post['addressfield'] . "\n\n";
        $bookInformation = $this->translate("Book Information") . ":\n" . $this->getBookInformation() . "\n\n";
        $opening = $this->translate("Dear") . " " . $userData[0] . ",\n\n" . $this->translate("you triggered a PDA order") . ".\n";
        $closing = $this->translate("Kind Regards") . "\n\n" . $this->translate("Your IxTheo Team");
        $renderer = $this->getViewRenderer();
        $infoText = $renderer->render($this->forward()->dispatch('StaticPage', array(
            'action' => 'staticPage',
            'page' => 'PDASubscriptionMailInfoText'
        )));
        $emailMessage = $opening . $userDataText . $bookInformation . $postalAddress . $infoText . "\n\n" . $closing;

        $this->sendEmail($recipientEmail, $recipientName, $senderData['email'], $senderData['name'], $emailSubject, $emailMessage); 
    }


    /*
     * Send unsubscribe notification to library
     */

    function sendPDAUnsubscribeEmail($user) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $emailSubject = "PDA Abbestellung";
        $recipientData = $this->getPDAInstitutionRecipientData();
        $emailMessage = "Abbestellung: " . $this->getBookInformation() . "\n\n" .
                         "für: " . $userData[0] . "(" . $userData[1] . ")";
        $this->sendEmail($recipientData['email'], $recipientData['name'], $senderData['email'], $senderData['name'], $emailSubject, $emailMessage);
    }


    /*
     * Send unsubscribe notification to user
     */

    function sendPDAUserUnsubscribeEmail($user) {
        $userDataRaw = $this->getUserData($user->id);
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData();
        $emailSubject = $this->translate("Cancellation of your PDA Order");
        $recipientName = $userData[0];
        $recipientEmail = $userData[1];
        $opening = $this->translate("Dear") . " " . $userData[0] . ",\n\n" . $this->translate("you cancelled a PDA order") . ":\n";
        $closing = $this->translate("Kind Regards") . "\n\n" .  $this->translate("Your IxTheo Team"); 
        $emailMessage = $opening .  $this->getBookInformation() . "\n\n" . $closing;
        $this->sendEmail($recipientEmail, $recipientName, $senderData['email'], $senderData['name'], $emailSubject, $emailMessage);
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
