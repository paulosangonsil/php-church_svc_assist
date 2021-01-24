<?php
require_once '../model/includes.inc';

/**
 * @author Administrator
 *
 */
class RecoverPasswd extends \Abstract_TbRec {
    const   COL_MEMBER  = 0;
    const   COL_TSTAMP  = 1;

    const   TB_NAME_RECOVER_PASSWD  = 0;

    /*Array<string>*/
    const   COL_NAMES   = array("member", "tstamp");
    const   TB_NAMES    = array(TB_PREFIX . "recover_passwd");

    protected /*int*/ $_timestamp;
    protected /*int*/ $_objMember;

    /**
     */
    public function __construct($member) {
        parent::__construct($member->get_id(), Abstract_TbRec::_getDefaultDBObj() );

        $this->set_member($member);

        $this->_init();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::_init()
     */
    protected function _init() {
        $newRec = FALSE;

        $resConn = Abstract_TbRec::_getDefaultDBObj()->get_creationException();

        if ( is_numeric($resConn) && intval($resConn) ) {
            die ( "RecoverPasswd: connection error with the DB $resConn" );
        }

        $resQuery = FALSE;

        if ($this->get_member()->get_id() != UNDEFINED) {
            $resQuery = Abstract_TbRec::_getDefaultDBObj()->query( RecoverPasswd::TB_NAMES[RecoverPasswd::TB_NAME_RECOVER_PASSWD],
                "*", array(RecoverPasswd::COL_NAMES[RecoverPasswd::COL_MEMBER] => $this->get_member()->get_id() ) );
        }
        else {
            $newRec = TRUE;

            $this->set_timestamp( new DateTime() );
        }

        if (! $newRec) {
            if ($resQuery) {
                $queryResObj = Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult();

                if ($queryResObj != FALSE) {
                    $this->set_id($this->get_member()->get_id());

                    $currObj = current($queryResObj);

                    $this->set_timestamp( DateTime::createFromFormat(RecoverPasswd::TIMESTAMP_FORMAT_DB,
                        $currObj[RecoverPasswd::COL_NAMES[RecoverPasswd::COL_TSTAMP]]) );
                }
            }
            else {
                die("RecoverPasswd: invalid identification");
            }
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::idsToDelete()
     */
    protected function idsToDelete($listIds) {
        $condList = array(RecoverPasswd::COL_NAMES[RecoverPasswd::COL_MEMBER] => $listIds);

        return Abstract_TbRec::_getDefaultDBObj()->
            delete(RecoverPasswd::TB_NAMES[RecoverPasswd::TB_NAME_RECOVER_PASSWD], $condList);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::store()
     */
    public function store(): bool {
        $mtdRet = FALSE;

        if ( $this->get_id() != UNDEFINED ) {
            $condsMap = array();

            $condsMap[RecoverPasswd::COL_NAMES[RecoverPasswd::COL_MEMBER]] = $this->get_id();

            $valuesMap[RecoverPasswd::COL_NAMES[RecoverPasswd::COL_NAME_TSTAMP]] =
                ( new DateTime() )->format(RecoverPasswd::TIMESTAMP_FORMAT_DB);

            $mtdRet = Abstract_TbRec::_getDefaultDBObj()->
                update(RecoverPasswd::TB_NAMES[RecoverPasswd::TB_NAME_RECOVER_PASSWD], $valuesMap, $condsMap);
        }
        else {
            $valuesMap[RecoverPasswd::COL_NAMES[RecoverPasswd::COL_MEMBER]] = 0;

            $valuesMap[RecoverPasswd::COL_NAMES[RecoverPasswd::COL_NAME_TSTAMP]] = $this->get_timestamp();

            $mtdRet = Abstract_TbRec::_getDefaultDBObj()->
                insert(RecoverPasswd::TB_NAMES[RecoverPasswd::TB_NAME_RECOVER_PASSWD], $valuesMap);

            // Refresh the object data's
            if ($mtdRet) {
                $this->_init();
            }
        }

        return $mtdRet;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::listAll()
     */
    protected function listAll($cond = NULL, $offsetPage = NULL) {
        // TODO - Insert your code here
    }

    /**
     */
    function __destruct() {
        // TODO - Insert your code here
    }

    /**
     * _timestamp
     * @return int
     */
    public function get_timestamp(): DateTime {
        return $this->_timestamp;
    }

    /**
     * _timestamp
     * @param int $_timestamp
     * @return RecoverPasswd
     */
    public function set_timestamp($_timestamp){
        $this->_timestamp = $_timestamp;
        return $this;
    }

    /**
     * _objMember
     * @return Member
     */
    public function get_member(): int {
        return $this->_objMember;
    }

    /**
     * _objMember
     * @param Member $_objMember
     * @return RecoverPasswd
     */
    public function set_member($_objMember){
        $this->_objMember = $_objMember;
        return $this;
    }
}
