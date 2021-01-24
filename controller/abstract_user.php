<?php
require_once 'includes.inc';

/**
 * @author Administrator
 *
 */
abstract class Abstract_User extends \Abstract_TbRec {
    public const   COL_NAME_ID         = 0;
    public const   COL_CHURCH          = 1;
    public const   COL_TSTAMP          = 2;
    public const   COL_SOCIAL_SEC_ID   = 3;
    public const   COL_EMAIL           = 4;
    public const   COL_TEL             = 5;
    public const   COL_NAME            = 6;
    public const   COL_SURNAME         = 7;

    public const   TB_NAME_ABSTRACT  = 0;

    /*Array<string>*/
    public  $COL_NAMES   = array("id", "church", "tstamp", "socialsec", "email", "tel", "name", "surname");

    public  $TB_NAMES;

    protected /*bool*/ $_newRec = TRUE;
    protected /*int*/ $_church;
    protected /*int*/ $_timestamp;
    protected /*int*/ $_socialsec;
    protected /*string*/ $_name;
    protected /*string*/ $_surname;
    protected /*string*/ $_email;
    protected /*string*/ $_tel;

    /**
     */
    public function __construct($connId = UNDEFINED) {
        parent::__construct($connId, self::_getDefaultDBObj() );

        $this->_init();
    }

    protected function _initSpecific01($queryResArr) {
    }

    protected function _loadCond(): bool {
        $resQuery = FALSE;

        if ($this->get_id() != UNDEFINED) {
            $resQuery = self::_getDefaultDBObj()->query( $this->TB_NAMES[Abstract_User::TB_NAME_ABSTRACT],
                "*", array($this->COL_NAMES[Abstract_User::COL_NAME_ID] => $this->get_id() ) );
        }

        if (! $resQuery) {
            $srchVals = array([Abstract_User::COL_SOCIAL_SEC_ID, $this->get_socialsec()],
                [Abstract_User::COL_EMAIL, $this->get_email()],
                [Abstract_User::COL_TEL, $this->get_tel()]);

            for ($iter = 0; $iter < sizeof($srchVals); $iter++) {
                if ($srchVals[$iter][1] != NULL) {
                    $condVals = array($this->COL_NAMES[$srchVals[$iter][0]] => $srchVals[$iter][1],
                        $this->COL_NAMES[Abstract_User::COL_CHURCH] => $this->get_church()->get_id());

                    $resQuery = self::_getDefaultDBObj()->query($this->TB_NAMES[Abstract_User::TB_NAME_ABSTRACT],
                        "*", $condVals);
                }

                if ($resQuery) {
                    break;
                }
            }
        }

        return $resQuery;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_User::_init()
     */
    protected function _init() {
        if (! $this->_newRec) {
            return;
        }

        $resConn = self::_getDefaultDBObj()->get_creationException();

        if ( is_numeric($resConn) && intval($resConn) ) {
            die ( "Abstract_User: connection error with the DB $resConn" );
        }

        $resQuery = $this->_loadCond();

        if ($resQuery) {
            $queryResObj = self::_getDefaultDBObj()->getLastQueryResult();

            if ($queryResObj != FALSE) {
                $this->_newRec = FALSE;

                $currObj = current($queryResObj);

                $this->set_id($currObj[$this->COL_NAMES[Abstract_User::COL_NAME_ID]]);
                $this->set_church(new Church($currObj[$this->COL_NAMES[Abstract_User::COL_CHURCH]]));
                $this->set_socialsec($currObj[$this->COL_NAMES[Abstract_User::COL_SOCIAL_SEC_ID]]);
                $this->set_name($currObj[$this->COL_NAMES[Abstract_User::COL_NAME]]);
                $this->set_surname($currObj[$this->COL_NAMES[Abstract_User::COL_SURNAME]]);
                $this->set_email($currObj[$this->COL_NAMES[Abstract_User::COL_EMAIL]]);
                $this->set_tel($currObj[$this->COL_NAMES[Abstract_User::COL_TEL]]);

                $this->set_timestamp( DateTime::createFromFormat(Abstract_User::TIMESTAMP_FORMAT_DB,
                    $currObj[$this->COL_NAMES[Abstract_User::COL_TSTAMP]]) );

                $this->_initSpecific01($currObj);
            }
        }
        else {
            $this->set_timestamp( new DateTime() );
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_User::idsToDelete()
     */
    public function idsToDelete($listIds) {
        $condList = array($this->COL_NAMES[Abstract_User::COL_NAME_ID] => $listIds);

        return Abstract_TbRec::_getDefaultDBObj()->
            delete($this->TB_NAMES[Abstract_User::TB_NAME_ABSTRACT], $condList);
    }

    protected function _isEmailValid(): bool {
        $mtdRet = TRUE;

        //$checkVal = $this->get_email();

        return $mtdRet;
    }

    protected function _isTelValid(): bool {
        $mtdRet = TRUE;

        //$checkVal = $this->get_tel();

        return $mtdRet;
    }

    protected function _isSocialSecValid(): bool {
        $mtdRet = TRUE;

        //$checkVal = $this->get_socialsec();

        return $mtdRet;
    }

    protected function _storeAlertNew(): bool {
        return FALSE;
    }

    protected function _storeSpecific01(/*array*/ &$valuesMap): bool {
        return FALSE;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_User::store()
     */
    public function store(): bool {
        $mtdRet = TRUE;

        if ($this->get_tel() != NULL) {
            $mtdRet = $this->_isTelValid();
        }

        if ( $mtdRet && ($this->get_socialsec() != NULL) ) {
            $mtdRet = $this->_isSocialSecValid();
        }

        if ( $mtdRet && ($this->get_email() != NULL) ) {
            $mtdRet = $this->_isEmailValid();
        }

        if ($mtdRet) {
            $strTmp = $this->get_name();

            $mtdRet = ( ($strTmp != NULL) && (strlen($strTmp) > 2) );
        }

        if ($mtdRet) {
            $strTmp = $this->get_surname();

            $mtdRet = ( ($strTmp != NULL) && (strlen($strTmp) > 2) );
        }

        if ($mtdRet) {
            $valuesMap  = array();

            $this->_storeSpecific01($valuesMap);

            $strTmp = $this->get_tel();

            if ($strTmp != NULL) {
                $valuesMap[$this->COL_NAMES[Abstract_User::COL_TEL]] = $strTmp;
            }

            $strTmp = $this->get_socialsec();

            if ($strTmp != NULL) {
                $valuesMap[$this->COL_NAMES[Abstract_User::COL_SOCIAL_SEC_ID]] = $strTmp;
            }

            $strTmp = $this->get_email();

            if ($strTmp != NULL) {
                $valuesMap[$this->COL_NAMES[Abstract_User::COL_EMAIL]] = $strTmp;
            }

            $valuesMap[$this->COL_NAMES[Abstract_User::COL_NAME]] = $this->get_name();

            $valuesMap[$this->COL_NAMES[Abstract_User::COL_SURNAME]] = $this->get_surname();

            $valuesMap[$this->COL_NAMES[Abstract_User::COL_CHURCH]] = $this->get_church()->get_id();

            if ( $this->get_id() != UNDEFINED ) {
                $condsMap = array();

                $condsMap[$this->COL_NAMES[Abstract_User::COL_NAME_ID]] = $this->get_id();

                $mtdRet = self::_getDefaultDBObj()->
                    update($this->TB_NAMES[Abstract_User::TB_NAME_ABSTRACT], $valuesMap, $condsMap);
            }
            else {
                $valuesMap[$this->COL_NAMES[Abstract_User::COL_NAME_ID]] = 0;

                $valuesMap[$this->COL_NAMES[Abstract_User::COL_TSTAMP]] =
                    $this->get_timestamp()->format(Abstract_User::TIMESTAMP_FORMAT_DB);

                $mtdRet = self::_getDefaultDBObj()->
                    insert($this->TB_NAMES[Abstract_User::TB_NAME_ABSTRACT], $valuesMap);

                // Refresh the object data's
                if ($mtdRet) {
                    $this->_init();

                    $this->_storeAlertNew();
                }
            }
        }

        return $mtdRet;
    }

    public function getRecs($offsetPage = NULL): array {
        $class = get_called_class();

        $mtdRet = array();

        $cond = array( $this->COL_NAMES[Abstract_User::COL_CHURCH] => $this->get_church()->get_id() );

        $order = array($this->COL_NAMES[Abstract_User::COL_NAME] => Abstract_TbRec::LIST_ORDER_ASC);

        if ( self::_getDefaultDBObj()->query( $this->TB_NAMES[Abstract_User::TB_NAME_ABSTRACT],
            "*", $cond, $order, $offsetPage )) {
            foreach (Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult() as $itemValue) {
                $mtdRet[] = new $class( $itemValue[$this->COL_NAMES[Abstract_User::COL_NAME_ID]]);
            }
        }

        return $mtdRet;
    }
    
    /**
     * (non-PHPdoc)
     *
     * @see Abstract_User::listAll()
     */
    static protected function listAll($cond = NULL, $offsetPage = NULL) {
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
     * @return Abstract_User
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
     * @return Abstract_User
     */
    public function set_name($_name){
        $this->_name = ucwords($_name);
        return $this;
    }

    /**
     * _surname
     * @return string
     */
    public function get_surname(){
        return $this->_surname;
    }

    /**
     * _surname
     * @param string $_surname
     * @return Abstract_User
     */
    public function set_surname($_surname){
        $this->_surname = ucwords($_surname);
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
     * @return Abstract_User
     */
    public function set_email($_email){
        $this->_email = $_email;

        $this->_init();

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
     * @param string $_tel
     * @return Abstract_User
     */
    public function set_tel($_tel){
        $this->_tel = $_tel;

        $this->_init();

        return $this;
    }

    /**
     * _church
     * @return Church
     */
    public function get_church(): Church {
        return $this->_church;
    }

    /**
     * _church
     * @param int $_church
     * @return Abstract_User
     */
    public function set_church($_church){
        $this->_church = $_church;
        return $this;
    }

    /**
     * _socialsec
     * @return int
     */
    public function get_socialsec(){
        return $this->_socialsec;
    }

    /**
     * _socialsec
     * @param int $_socialsec
     * @return Abstract_User
     */
    public function set_socialsec($_socialsec){
        $this->_socialsec = $_socialsec;

        $this->_init();

        return $this;
    }

    public function copyFrom($srcObj) {
        if ($srcObj instanceof Abstract_User) {
            $this->set_church($srcObj->get_church());
            $this->set_name($srcObj->get_name());
            $this->set_surname($srcObj->get_surname());
            $this->set_tel($srcObj->get_tel());
            $this->set_email($srcObj->get_email());
            $this->set_socialsec($srcObj->get_socialsec());
        }
    }
}
