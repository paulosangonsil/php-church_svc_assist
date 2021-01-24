<?php
require_once 'includes.inc';

/**
 * @author Administrator
 *
 */
class Days extends \Abstract_TbRec {
    const   COL_NAME_ID         = 0;
    const   COL_SERVICE         = 1;
    const   COL_SERVICE_DATE    = 2;
    const   COL_MEMBER          = 3;
    const   COL_TSTAMP          = 4;

    const   TB_NAME_DAYS  = 0;

    /*Array<string>*/
    const   COL_NAMES   = array("id", "service", "servicedate", "member", "tstamp");
    const   TB_NAMES    = array(TB_PREFIX . "assist_days");

    const SERVICE_DAYS_1ST_SVC = 0;
    const SERVICE_DAYS_2ND_SVC = 1;
    const SERVICE_DAYS_3RD_SVC = 2;
    const SERVICE_DAYS_4TH_SVC = 3;
    const SERVICE_DAYS_5TH_SVC = 4;
    const SERVICE_DAYS_6TH_SVC = 5;
    const SERVICE_DAYS_7TH_SVC = 6;
    const SERVICE_DAYS_8TH_SVC = 7;

    const SERVICE_DAYS = [Days::SERVICE_DAYS_1ST_SVC => "domingo ma&ntilde;ana",
        Days::SERVICE_DAYS_2ND_SVC => "domingo tarde",
        Days::SERVICE_DAYS_3RD_SVC => "lunes",
        Days::SERVICE_DAYS_4TH_SVC => "martes",
        Days::SERVICE_DAYS_5TH_SVC => "mi&eacute;rcoles",
        Days::SERVICE_DAYS_6TH_SVC => "jueves",
        Days::SERVICE_DAYS_7TH_SVC => "viernes",
        Days::SERVICE_DAYS_8TH_SVC  => "s&aacute;bado"];

    protected /*int*/ $_service;
    protected /*int*/ $_serviceDate;
    protected /*int*/ $_timestamp;
    protected /*int*/ $_objMember;

    /**
     */
    public function __construct($id = UNDEFINED) {
        parent::__construct($id, Abstract_TbRec::_getDefaultDBObj() );

        $this->_init();
    }

    /**
     */
    function __destruct() {
        // TODO - Insert your code here
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
            die ( "Days: connection error with the DB $resConn" );
        }

        $resQuery = FALSE;

        $this->set_timestamp( new DateTime() );

        if ($this->get_id() != UNDEFINED) {
            $resQuery = Abstract_TbRec::_getDefaultDBObj()->query( Days::TB_NAMES[Days::TB_NAME_DAYS],
                "*", array(Days::COL_NAMES[Days::COL_NAME_ID] => $this->get_id() ) );
        }
        else {
            $newRec = TRUE;
        }

        if (! $newRec) {
            if ($resQuery) {
                $queryResObj = Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult();

                if ($queryResObj != FALSE) {
                    $currObj = current($queryResObj);

                    $this->set_timestamp( DateTime::createFromFormat(Days::TIMESTAMP_FORMAT_DB,
                        $currObj[Days::COL_NAMES[Days::COL_TSTAMP]]) );

                    $this->set_member( new Member($currObj[Days::COL_NAMES[Days::COL_MEMBER]]) );

                    $this->set_service($currObj[Days::COL_NAMES[Days::COL_SERVICE]]);

                    $this->set_servicedate( DateTime::createFromFormat(Days::TIMESTAMP_FORMAT_DB,
                        $currObj[Days::COL_NAMES[Days::COL_SERVICE_DATE]]));
                }
            }
            /*else {
                die("Days: invalid identification");
            }*/
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::idsToDelete()
     */
    public function idsToDelete($listIds) {
        $condList = array(Days::COL_NAMES[Days::COL_NAME_ID] => $listIds);

        return Abstract_TbRec::_getDefaultDBObj()->
            delete(Days::TB_NAMES[Days::TB_NAME_DAYS], $condList);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Abstract_TbRec::store()
     */
    public function store(): bool {
        $mtdRet = FALSE;

        $valuesMap[Days::COL_NAMES[Days::COL_MEMBER]] = $this->get_member()->get_id();
        $valuesMap[Days::COL_NAMES[Days::COL_SERVICE]] = $this->get_service();

        $dtObj = new DateTime();
        $dtObj->setTimestamp($this->get_servicedate());

        $valuesMap[Days::COL_NAMES[Days::COL_SERVICE_DATE]] = $dtObj->format(Days::TIMESTAMP_FORMAT_DB);

        if ( $this->get_id() != UNDEFINED ) {
            $condsMap = array();

            $condsMap[Days::COL_NAMES[Days::COL_NAME_ID]] = $this->get_id();

            $valuesMap[Days::COL_NAMES[Days::COL_TSTAMP]] =
                ( new DateTime() )->format(Days::TIMESTAMP_FORMAT_DB);

            $mtdRet = Abstract_TbRec::_getDefaultDBObj()->
                update(Days::TB_NAMES[Days::TB_NAME_DAYS], $valuesMap, $condsMap);
        }
        else {
            $valuesMap[Days::COL_NAMES[Days::COL_NAME_ID]] = 0;

            $valuesMap[Days::COL_NAMES[Days::COL_TSTAMP]] =
                $this->get_timestamp()->format(Days::TIMESTAMP_FORMAT_DB);

            $mtdRet = Abstract_TbRec::_getDefaultDBObj()->
                insert(Days::TB_NAMES[Days::TB_NAME_DAYS], $valuesMap);

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
     * @return  Array
     */
    static protected /*Array<Object>*/ function listAll ($cond = NULL, $offsetPage = NULL) {
    }

    static public /*Array<Days>*/ function getDayInfos($theService, $theChurch, $tstamp, $addConds = NULL) {
        $newMember = new Member();

        $mtdRet = NULL;

        $churchId = $theChurch->get_id();

        $strQuery = "SELECT `" . Days::TB_NAMES[Days::TB_NAME_DAYS] . "`.* FROM `" . Days::TB_NAMES[Days::TB_NAME_DAYS] . "` WHERE " . Days::COL_NAMES[Days::COL_MEMBER] . " IN " .
                        "(SELECT `" . $newMember->COL_NAMES[Member::COL_NAME_ID] . "` FROM `" . $newMember->TB_NAMES[Member::TB_NAME_ABSTRACT] . "` WHERE " .
                        "`" . $newMember->TB_NAMES[Member::TB_NAME_ABSTRACT] . "`.`" . $newMember->COL_NAMES[Member::COL_CHURCH] . "` = $churchId AND `" .
                        Days::TB_NAMES[Days::TB_NAME_DAYS] . "`.`" . Days::COL_NAMES[Days::COL_SERVICE] . "` = $theService AND `" .
                        Days::COL_NAMES[Days::COL_SERVICE_DATE] . "` = '" . $tstamp->format(Days::TIMESTAMP_FORMAT_DB) . "'";

        if (! empty($addConds) ) {
            $strValues = "";

            $firstIter = TRUE;

            foreach ($addConds as $itemKey => $itemValue) {
                $strValues .= " AND `$itemKey`=";

                if ( is_numeric ($itemValue) ) {
                    $strValues .= "$itemValue";
                }
                else {
                    $strValues .= "'$itemValue'";
                }
            }

            if ($strValues != NULL) {
                $strQuery .= $strValues;
            }
        }

        $strQuery .= ')';

        if ( Abstract_TbRec::_getDefaultDBObj()->rawquery($strQuery) ) {
            $mtdRet = array();

            foreach (Abstract_TbRec::_getDefaultDBObj()->getLastQueryResult() as $itemValue) {
                $mtdRet[] = new Days( $itemValue[Days::COL_NAMES[Days::COL_NAME_ID]]);
            }
        }

        return $mtdRet;
    }

    static public /*int*/ function nextSvcDate($theActualDate): int {
        $dtInfos = getdate($theActualDate);

        $mtdRet = mktime(19, 30, 0, $dtInfos['mon'],
            $dtInfos['mday'], $dtInfos['year']);

        $dtIntervFrm = NULL;

        $svcWasNotInThisDay = ($dtInfos['hours'] > 19);

        switch ($dtInfos['wday']) {
            // sunday
            case 0: {
                /* Svc at 11h
                 if ($dtInfos['hours'] < 11) {
                 $mtdRet = mktime(11, 0, 0, $dtInfos['mon'],
                 $dtInfos['mday'], $dtInfos['year']);
                 }
                 // Svc at 18h
                 else if ( ($dtInfos['hours'] > 11) && ($dtInfos['hours'] < 20) ) {
                 $mtdRet = mktime(18, 0, 0, $dtInfos['mon'],
                 $dtInfos['mday'], $dtInfos['year']);
                 }
                 // Svc at monday
                 else*/ {
                // Monday
                $dtIntervFrm = "P1D";
            }

            break;
            }

            // monday
            case 1: {
                // Svc at wednesday
                if ($svcWasNotInThisDay) {
                    $dtIntervFrm = "P2D";
                }

                break;
            }

            // tuesday
            case 2: {
                // Svc at wednesday
                $dtIntervFrm = "P1D";

                break;
            }

            // wednesday
            case 3:
                // thursday
            case 4: {
                // Svc at the next day
                if ($svcWasNotInThisDay) {
                    $dtIntervFrm = "P1D";
                }

                break;
            }

            // friday
            case 5: {
                // Svc at monday
                if ($svcWasNotInThisDay) {
                    $dtIntervFrm = "P3D";
                }

                break;
            }

            // saturday
            default:
            case 7: {
                // Svc at monday
                $dtIntervFrm = "P2D";

                break;
            }
        }

        if ($dtIntervFrm != NULL) {
            $objDt = new DateTime();

            $objDt->setTimestamp($mtdRet);

            $mtdRet = $objDt->add(new DateInterval($dtIntervFrm))->getTimestamp();

            $objDt->setTimestamp($mtdRet);
        }

        return $mtdRet;
    }

    static public /*int*/ function prevSvcDate($theActualDate): int {
        $dtInfos = getdate($theActualDate);

        $mtdRet = mktime(19, 30, 0, $dtInfos['mon'],
                    $dtInfos['mday'], $dtInfos['year']);

        $dtIntervFrm = NULL;

        $svcWasNotInThisDay = ($dtInfos['hours'] < 20);

        switch ($dtInfos['wday']) {
            // sunday
            case 0: {
                // Friday
                $dtIntervFrm = "P2D";

                break;
            }

            // monday
            case 1: {
                if ($svcWasNotInThisDay) {
                    // friday
                    $dtIntervFrm = "P3D";
                }

                break;
            }

            // tuesday
            case 2: {
                // Svc at monday
                $dtIntervFrm = "P1D";

                break;
            }

            // wednesday
            case 3: {
                if ($svcWasNotInThisDay) {
                    $dtIntervFrm = "P2D";
                }

                break;
            }

            // thursday
            case 4:
            // friday
            case 5:
            // saturday
            case 7:
            default: {
                if ($svcWasNotInThisDay) {
                    $dtIntervFrm = "P1D";
                }

                break;
            }
        }

        if ($dtIntervFrm != NULL) {
            $objDt = new DateTime();

            $objDt->setTimestamp($mtdRet);

            $mtdRet = $objDt->sub(new DateInterval($dtIntervFrm))->getTimestamp();

            $objDt->setTimestamp($mtdRet);
        }

        return $mtdRet;
    }

    static public function isTheServiceToday($dayCode): bool {
        $dtInfos = getdate(time());

        if ($dayCode < Days::SERVICE_DAYS_3RD_SVC) {
            $dayCode = 0;
        }
        else {
            $dayCode -= 1;
        }

        return $dtInfos['wday'] == $dayCode;
    }

    static public /*int*/ function getWeekDayCode($theDate): int {
        $mtdRet = UNDEFINED;

        $dtInfos = getdate($theDate);

        switch ($dtInfos['wday']) {
            case 0: {
                /*if ($dtInfos['hours'] < 18) {
                    $mtdRet = Days::SERVICE_DAYS_1ST_SVC;
                }
                // Svc at 18h
                else {
                    $mtdRet = Days::SERVICE_DAYS_2ND_SVC;
                }*/

                $mtdRet = Days::SERVICE_DAYS_3RD_SVC;

                break;
            }

            default: {
                $mtdRet = $dtInfos['wday'] + 1;

                break;
            }
        }

        return $mtdRet;
    }

    static public /*int*/ function getWeekDayName($theDayCode): string {
        $mtdRet = '';

        if ( ($theDayCode >= Days::SERVICE_DAYS_1ST_SVC) &&
                ($theDayCode <= Days::SERVICE_DAYS_8TH_SVC) ) {
            $mtdRet = Days::SERVICE_DAYS[$theDayCode];
        }

        return $mtdRet;
    }

    /**
     * _timestamp
     * @return DateTime
     */
    public function get_timestamp(): DateTime {
        return $this->_timestamp;
    }

    /**
     * _timestamp
     * @param int $_timestamp
     * @return Days
     */
    public function set_timestamp($_timestamp){
        $this->_timestamp = $_timestamp;
        return $this;
    }

    /**
     * _objMember
     * @return Member
     */
    public function get_member(){
        return $this->_objMember;
    }

    /**
     * _objMember
     * @param Member $_objMember
     * @return Days
     */
    public function set_member($_objMember){
        $this->_objMember = $_objMember;
        return $this;
    }

    /**
     * _service
     * @return int
     */
    public function get_service(){
        return $this->_service;
    }

    /**
     * _service
     * @param int $_service
     * @return Days
     */
    public function set_service($_service){
        $this->_service = $_service;
        return $this;
    }

    /**
     * _serviceDate
     * @return int
     */
    public function get_servicedate(){
        return $this->_serviceDate;
    }

    /**
     * _serviceDate
     * @param int $date
     * @return Days
     */
    public function set_servicedate($date){
        $this->_serviceDate = $date;
        return $this;
    }
}
