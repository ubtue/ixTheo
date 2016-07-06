<?php
namespace VuFind\Db\Table;
use VuFind\Exception\LoginRequired as LoginRequiredException,
    VuFind\Exception\RecordMissing as RecordMissingException,
    Zend\Db\Sql\Expression;
class Subscription extends Gateway implements \VuFind\Db\Table\DbTableAwareInterface
{
    use \VuFind\Db\Table\DbTableAwareTrait;

    /**
     * Session container for last list information.
     *
     * @var \Zend\Session\Container
     */
    protected $session;

    /**
     * Constructor
     *
     * @param \Zend\Session\Container $session Session container (must use same
     * namespace as container provided to \VuFind\View\Helper\Root\UserList).
     */
    public function __construct()
    {
        parent::__construct('ixtheo_journal_subscriptions', 'VuFind\Db\Row\Subscription');
    }

    public function getNew($userId, $recordId) {
        $row = $this->createRow();
        $row->id = $userId;
        $row->journal_control_number = $recordId;
        $row->last_issue_date = date('Y-m-d\TH:i:s\Z');
        return $row;
    }

    public function findExisting($userId, $recordId) {
        return $this->select(['id' => $userId, 'journal_control_number' => $recordId])->current();
    }

    public function subscribe($userId, $recordId) {
        $row = $this->getNew($userId, $recordId);
        $row->save();
        return $row->id;
    }

    public function unsubscribe($userId, $recordId) {
        return $this->delete(['id' => $userId, 'journal_control_number' => $recordId]);
    }

    public function getAll($userId) {
        return $this->select(['id' => $userId]);
    }
}