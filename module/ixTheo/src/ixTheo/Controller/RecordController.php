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
                     'country' => $ixtheoUserRow->country,
                     'user_type' => $ixtheoUserRow->user_type ];
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
        $userType = $userDataRaw['user_type'];
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData($userType);
        $recipientData = $this->getPDAInstitutionRecipientData($userType);
        $emailSubject = "PDA Bestellung";
        $addressForDispatch = $post['addressfield'];
        $emailMessage = "Benutzer:\n" .  implode("\n", $userData) . "\n\n" .
                        "Versandaddresse:\n" . $addressForDispatch . "\n\n" .
                        "Titel:\n" . $this->getBookInformation() . "\n\n" .
                        "Benutzer Typ:\n" . $userType;
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
    function getPDASenderData($realm) {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('config');
        $site = isset($config->Site) ? $config->Site : null;
        $pda_sender = 'pda_sender_' . $realm;
        $pda_sender_name = 'pda_sender_name';
        $senderEmail = isset($site->$pda_sender) ? $site->$pda_sender : null;
        $senderName = isset($site->$pda_sender_name) ? $site->$pda_sender_name : null;
        return ['email' => $senderEmail, 'name' => $senderName ];
    }

    function getPDAInstitutionRecipientData($realm) {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('config');
        $site = isset($config->Site) ? $config->Site : null;
        $pda_email = 'pda_email_' . $realm;
        $email = isset($site->$pda_email) ? $site->$pda_email : null;
        $name = "UB Tübingen PDA";
        return ['email' => $email, 'name' => $name];
    }


    function getPDAClosing($realm) {
        $salutation = ($realm === 'relbib') ? $this->translate("Your Relbib Team") : $this->translate("Your IxTheo Team");
        return $this->translate("Kind Regards") . "\n\n" . $salutation;
    }

    function sendPDAUserNotificationEmail($post, $user, $data) {
        $userDataRaw = $this->getUserData($user->id);
        $userType = $userDataRaw['user_type'];
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData($userType);
        $recipientEmail = $userData[1];
        $recipientName = $userData[0];
        $emailSubject = $this->translate("Your PDA Order");
        $postalAddress = $this->translate("You provided the following address") . ":\n" . $post['addressfield'] . "\n\n";
        $bookInformation = $this->translate("Book Information") . ":\n" . $this->getBookInformation() . "\n\n";
        $opening = $this->translate("Dear") . " " . $userData[0] . ",\n\n" . $this->translate("you triggered a PDA order") . ".\n";
        $renderer = $this->getViewRenderer();
        $infoText = $renderer->render($this->forward()->dispatch('StaticPage', array(
            'action' => 'staticPage',
            'page' => 'PDASubscriptionMailInfoText'
        )));
        $emailMessage = $opening . $bookInformation . $postalAddress . $infoText . "\n\n" . $this->getPDAClosing($userType);
        $this->sendEmail($recipientEmail, $recipientName, $senderData['email'], $senderData['name'], $emailSubject, $emailMessage); 
    }

    /*
     * Send unsubscribe notification to library
     */
    function sendPDAUnsubscribeEmail($user) {
        $userDataRaw = $this->getUserData($user->id);
        $userType = $userDataRaw['user_type'];
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData($userType);
        $emailSubject = "PDA Abbestellung";
        $recipientData = $this->getPDAInstitutionRecipientData($userType);
        $emailMessage = "Abbestellung: " . $this->getBookInformation() . "\n\n" .
                         "für: " . $userData[0] . "(" . $userData[1] . ")" . " [Benutzertyp: " . $userType . "]";
        $this->sendEmail($recipientData['email'], $recipientData['name'], $senderData['email'], $senderData['name'], $emailSubject, $emailMessage);
    }

    /*
     * Send unsubscribe notification to user
     */
    function sendPDAUserUnsubscribeEmail($user) {
        $userDataRaw = $this->getUserData($user->id);
        $userType = $userDataRaw['user_type'];
        $userData = $this->formatUserData($userDataRaw);
        $senderData = $this->getPDASenderData($userType);
        $emailSubject = $this->translate("Cancellation of your PDA Order");
        $recipientName = $userData[0];
        $recipientEmail = $userData[1];
        $opening = $this->translate("Dear") . " " . $userData[0] . ",\n\n" . $this->translate("you cancelled a PDA order") . ":\n";
        $emailMessage = $opening .  $this->getBookInformation() . "\n\n" . $this->getPDAClosing($userType);
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
