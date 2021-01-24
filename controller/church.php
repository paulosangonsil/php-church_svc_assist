<?php

require_once 'includes.inc';

/**
 * @author Administrator
 *
 */
class Church extends \Abstract_TbRec {
    const   COL_NAME_ID         = 0;
    const   COL_TSTAMP          = 1;
    const   COL_NAME            = 2;
    const   COL_ADDRESS         = 3;
    const   COL_EMAIL           = 4;
    const   COL_TEL             = 5;
    const   COL_INVITEDEMAIL    = 6;
    const   COL_MAXSEATS        = 7;

    const   TB_NAME_CHURCH  = 0;

    /*Array<string>*/
    const   COL_NAMES   = array("id", "tstamp", "name", "address", "email", "tel", "invitedemail", "maxseats");
    const   TB_NAMES    = array(TB_PREFIX . "church");

    protected /*int*/ $_timestamp;
    protected /*string*/ $_name;
    protected /*string*/ $_address;
    protected /*string*/ $_email;
    protected /*string*/ $_tel;
    protected /*string*/ $_invemail;
    protected /*int*/ $_maxseats;

    /**
     */
    public function __construct($connId = UNDEFINED) {
        parent::__construct($connId, Abstract_TbRec::_getDefaultDBObj() );

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
            die ( "Church: connection error with the DB $resConn" );
        }

        $resQuery = FALSE;

        if ($this->get_id() != UNDEFINED) {
            $resQuery = Abstract_TbRec::_getDefaultDBObj()->query( Church::TB_NAMES[Church::TB_NAME_CHURCH],
                "*", array(Church::COL_NAMES[Church::COL_NAME_ID] => $this->get_id() ) );
        }
        else {
            $newRec = TRUE;

            $this->set_timestamp( new DateTime() );
        }

        if (! $newRec) {
            if ($resQuery) {
                $queryResObj = Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult();

                if ($queryResObj != FALSE) {
                    $currObj = current($queryResObj);

                    $this->set_name($currObj[Church::COL_NAMES[Church::COL_NAME]]);
                    $this->set_address($currObj[Church::COL_NAMES[Church::COL_ADDRESS]]);
                    $this->set_email($currObj[Church::COL_NAMES[Church::COL_EMAIL]]);
                    $this->set_invitedemail($currObj[Church::COL_NAMES[Church::COL_INVITEDEMAIL]]);
                    $this->set_tel($currObj[Church::COL_NAMES[Church::COL_TEL]]);
                    $this->set_maxseats($currObj[Church::COL_NAMES[Church::COL_MAXSEATS]]);

                    $this->set_timestamp( DateTime::createFromFormat(Church::TIMESTAMP_FORMAT_DB,
                        $currObj[Church::COL_NAMES[Church::COL_TSTAMP]]) );
                }
            }
            else {
                die("Church: invalid identification");
            }
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::idsToDelete()
     */
    protected function idsToDelete($listIds) {
        // TODO - Insert your code here
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::store()
     */
    public function store(): bool {
        $mtdRet = FALSE;

        $strTmp = $this->get_name();

        if ( ($strTmp != NULL) && (strlen($strTmp) > 3) ) {
            $valuesMap  = array();

            $valuesMap[Church::COL_NAMES[Church::COL_NAME]] = $strTmp;

            $strTmp = $this->get_address();

            if ($strTmp != NULL) {
                $valuesMap[Church::COL_NAMES[Church::COL_ADDRESS]] = $strTmp;
            }

            $strTmp = $this->get_email();

            if ($strTmp != NULL) {
                $valuesMap[Church::COL_NAMES[Church::COL_EMAIL]] = $strTmp;
            }

            $strTmp = $this->get_invitedemail();

            if ($strTmp != NULL) {
                $valuesMap[Church::COL_NAMES[Church::COL_INVITEDEMAIL]] = $strTmp;
            }

            $strTmp = $this->get_tel();

            if ($strTmp != NULL) {
                $valuesMap[Church::COL_NAMES[Church::COL_TEL]] = $strTmp;
            }

            $strTmp = $this->get_maxseats();

            if ($strTmp != NULL) {
                $valuesMap[Church::COL_NAMES[Church::COL_MAXSEATS]] = $strTmp;
            }

            if ( $this->get_id() != UNDEFINED ) {
                $condsMap = array();

                $condsMap[Church::COL_NAMES[Church::COL_NAME_ID]] = $this->get_id();

                $mtdRet = Abstract_TbRec::_getDefaultDBObj()->
                    update(Church::TB_NAMES[Church::TB_NAME_CHURCH], $valuesMap, $condsMap);
            }
            else {
                $valuesMap[Church::COL_NAMES[Church::COL_NAME_ID]] = 0;

                $valuesMap[Church::COL_NAMES[Church::COL_NAME_TSTAMP]] =
                    $this->get_timestamp()->format(Church::TIMESTAMP_FORMAT_DB);

                $mtdRet = Abstract_TbRec::_getDefaultDBObj()->
                    insert(Church::TB_NAMES[Church::TB_NAME_CHURCH], $valuesMap);

                // Refresh the object data's
                if ($mtdRet) {
                    $this->_init();
                }
            }
        }

        return $mtdRet;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::listAll()
     */
    static public function listAll($cond = NULL, $offsetPage = 0) {
        $mtdRet = NULL;

        if ( Abstract_TbRec::_getDefaultDBObj()->query( Church::TB_NAMES[Church::TB_NAME_CHURCH],
            Church::COL_NAMES[Church::COL_NAME_ID]) ) {
                $mtdRet = array();

                foreach (Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult() as $itemValue) {
                    $mtdRet[] = new Church( $itemValue[Church::COL_NAMES[Church::COL_NAME_ID]]);
                }
            }

        return $mtdRet;
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
    public function get_timestamp(){
        return $this->_timestamp;
    }

    /**
     * _timestamp
     * @param int $_timestamp
     * @return Church
     */
    public function set_timestamp($_timestamp){
        $this->_timestamp = $_timestamp;
        return $this;
    }

    /**
     * _name
     * @return string
     */
    public function get_name(){
        return $this->_name;
    }

    /**
     * _name
     * @param string $_name
     * @return Church
     */
    public function set_name($_name){
        $this->_name = $_name;
        return $this;
    }

    /**
     * _address
     * @return string
     */
    public function get_address(){
        return $this->_address;
    }

    /**
     * _address
     * @param string $_address
     * @return Church
     */
    public function set_address($_address){
        $this->_address = $_address;
        return $this;
    }

    /**
     * _invemail
     * @return string
     */
    public function get_invitedemail(){
        return $this->_invemail;
    }

    /**
     * _invemail
     * @param string $_email
     * @return Church
     */
    public function set_invitedemail($_email){
        $this->_invemail = $_email;
        return $this;
    }

    /**
     * _email
     * @return string
     */
    public function get_email(){
        return $this->_email;
    }

    /**
     * _email
     * @param string $_email
     * @return Church
     */
    public function set_email($_email){
        $this->_email = $_email;
        return $this;
    }

    /**
     * _tel
     * @return string
     */
    public function get_tel(){
        return $this->_tel;
    }

    /**
     * _tel
     * @param string $_string
     * @return Church
     */
    public function set_tel($_tel){
        $this->_tel = $_tel;
        return $this;
    }

    /**
     * _maxseats
     * @return int
     */
    public function get_maxseats(): int {
        return $this->_maxseats;
    }

    /**
     * _maxseats
     * @param int $_maxseats
     * @return Church
     */
    public function set_maxseats($_maxseats){
        $this->_maxseats = $_maxseats;
        return $this;
    }
}
