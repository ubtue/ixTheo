<?php
namespace VuFind\Db\Table;
class IxTheoUser extends Gateway implements \VuFind\Db\Table\DbTableAwareInterface
{
    use \VuFind\Db\Table\DbTableAwareTrait;

    protected $session;

    public function __construct()
    {
        parent::__construct('ixtheo_user', 'VuFind\Db\Row\IxTheoUser');
    }

    public function getNew($userId)
    {
        $row = $this->createRow();
        $row->id = $userId;
        return $row;
    }
}