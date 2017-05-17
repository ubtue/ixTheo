<?php
namespace ixTheo\Mailer;

use VuFind\Mailer\Mailer;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class IxTheoMailer extends \VuFind\Mailer\Mailer implements ServiceLocatorAwareInterface {
    
    use ServiceLocatorAwareTrait;
    
    /**
     * Send an email message, append custom footer to body
     *
     * @param string|Address|AddressList $to      Recipient email address (or
     * delimited list)
     * @param string|Address             $from    Sender name and email address
     * @param string                     $subject Subject line for message
     * @param string                     $body    Message body
     * @param string                     $cc      CC recipient (null for none)
     *
     * @throws MailException
     * @return void
     */
    public function send($to, $from, $subject, $body, $cc = null)
    {
        $config = $this->getServiceLocator()->get('VuFind\Config')->get('config');
        $email_enquiry = $config->Site->email_enquiry;
        
        if ($email_enquiry != null) {
            $footer = 'If you have questions regarding this service please contact' . PHP_EOL . $email_enquiry;
            $body .= PHP_EOL . '--' . PHP_EOL . $footer;
        }
        
        parent::send($to, $from, $subject, $body, $cc);
    }
}
