<?php
    $includePaths = _genIncludePath(['controller', 'php-common', 'php-common/model', 'view', 'utils', 'model', 'model/cmds']);

    set_include_path (get_include_path () . PATH_SEPARATOR . $includePaths);

    const UNDEFINED   = -1;

    const DB_USERNAME   = "psgsilva";
    const DB_USERPASS   = "psgsilva";
    const DB_HOSTNAME   = "localhost";
    const DB_NAME       = "church_svc_assist";
    const TB_PREFIX     = '';

    const _STR_IN_CMD   = "in";

    const _OPER_LOGOFF    = "5";

    const FLD_OPER          = "cmpOper";
    const FLD_USRDATEZN     = "dateZone";
    const FLD_USRNAME       = "txtUsrName";
    const FLD_USRPWD        = "txtUserPass";
    const FLD_CHURCH        = "txtChurch";

    const _SESSION_LOGIN_NAME = 'usrname';
    const _SESSION_LOGIN_PWD  = 'usrpwd';
    const _SESSION_DATEZONE   = 'datezone';
    const _SESSION_CHURCH     = 'churchid';

    const PAG_RECOVER_PASSWD    = "recpasswd.php";
    const PAG_NEW_MEMBER        = "newmember.php";
    const PAG_REPORT            = "report.php";
    const PAG_LIST_MEMBERS      = "listmembers.php";
    const PAG_VALIDATE_PROPS    = "validateprop.php";
    const PAG_NEW_PROP          = "newprop.php";
    const PAG_RESERVATION       = "reservation.php";

    const MSG_TYPE_ERROR = 1;
    const MSG_TYPE_WARN = 2;
    const MSG_TYPE_SUCCESS = 3;

    session_start();

    require_once 'abstract_dbaccess.php';
    require_once 'abstract_tbrec.php';
    require_once 'mysql_pdodriver.php';
    require_once 'abstract_user.php';
    require_once 'church.php';
    require_once 'member.php';
    require_once 'proposal.php';
    require_once 'days.php';
    require_once 'utils.php';

    function _strset($str, $char, $count) {
        $mtdRet = $str;

        for ($iCnt = 0; $iCnt < $count; $iCnt++) {
            $mtdRet .= $char;
        }

        return $mtdRet;
    }

    function _genIncludePath($folderNames): string {
        $MAXDEPTH = 3;

        $mtdRet = '';

        foreach ($folderNames as $folderName) {
            for ($iCnt = 0; $iCnt < $MAXDEPTH + 1; $iCnt++) {
                $mtdRet .= PATH_SEPARATOR;

                if ($iCnt == 0) {
                    $mtdRet .= './';
                }
                else {
                    $mtdRet = _strset($mtdRet, '../', $iCnt);
                }

                $mtdRet .= $folderName;
            }
        }

        return $mtdRet;
    }

    /**
     * Validates the entered user credentials, case it's valid.
     * @return String NULL|The logged user name.
     */
    function /*String*/ _checkLogonCust() {
        $selChurch = $usrId   = NULL;

        if ( ! array_key_exists(_SESSION_LOGIN_NAME, $_SESSION) ) {
            $usrId   = (isset ($_POST[FLD_USRNAME]) ) ? $_POST[FLD_USRNAME] : NULL;
            $usrPasswd = (isset ($_POST[FLD_USRPWD]) ) ? $_POST[FLD_USRPWD] : NULL;
            $selChurch = (isset ($_POST[FLD_CHURCH]) ) ? $_POST[FLD_CHURCH] : NULL;
        }
        else {
            $usrId   = $_SESSION[_SESSION_LOGIN_NAME];
            $usrPasswd = $_SESSION[_SESSION_LOGIN_PWD];
            $selChurch = $_SESSION[_SESSION_CHURCH];
        }

        // Verificar se ja' estamos processando o login ...
        if ($usrId && $usrPasswd) {
            // Conectando, escolhendo o banco de dados
            $objUsr = new Member();

            $objUsr->set_church(new Church($selChurch));

            $objUsr->set_email($usrId);

            // Tem alguma coisa errada: tenta de novo, peao!
            if ( (strcmp($objUsr->get_passwd(), $usrPasswd) == 0) && $objUsr->isAdm() ) {
                $_SESSION[_SESSION_LOGIN_NAME] = $usrId;
                $_SESSION[_SESSION_LOGIN_PWD] = $usrPasswd;
                $_SESSION[_SESSION_CHURCH] = $objUsr->get_church()->get_id();
            }
            else {
                $usrId = NULL;
            }
        }

        return $usrId;
    }

    /**
     * Logs off the current validated user.
     * @return boolean TRUE if there was a logged user.
     * @return boolean FALSE if there wasn't a logged user.
     */
    function /*boolean*/ _logOffCust() {
        $fRet = FALSE;

        if (isset($_SESSION[_SESSION_LOGIN_NAME])) {
            unset($_SESSION[_SESSION_LOGIN_NAME]);
            unset($_SESSION[_SESSION_LOGIN_PWD]);
            unset($_SESSION[_SESSION_CHURCH]);

            $fRet = TRUE;
        }

        return $fRet;
    }

    if (Utils\General::getHTTPVar(FLD_USRDATEZN) != NULL) {
        $timezone_offset_minutes = Utils\General::getHTTPVar(FLD_USRDATEZN);

        $timezone_offset_minutes = $timezone_offset_minutes == 0 ? 0 : -$timezone_offset_minutes;

        $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);

        $_SESSION[_SESSION_DATEZONE] = $timezone_name;
    }

    date_default_timezone_set($_SESSION[_SESSION_DATEZONE]);
