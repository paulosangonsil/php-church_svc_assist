<?php
require_once '../../model/includes.inc';

$strIDUsr = _checkLogonCust();

if ($strIDUsr == NULL) {
    //die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . " result = procedencia invalida" );
    $strLoc = 'Location: ./';
    header($strLoc);
}

const FLD_MEMBER_ID = 'memberid';
const FLD_ACT_OPT = "act_opt_";
const FLD_REFTIME = 'reftime';
const FLD_ACTION = "prop_action";

const TS_DISP_FMT = 'YmdHi';

const ACTION_SUBSCRIBE = "1";
const ACTION_UNSUBSCRIBE = "2";

$fldChurch = $_SESSION[_SESSION_CHURCH];
$fldRefTime = Utils\General::getHTTPVar(FLD_REFTIME);

if ( (! empty($fldRefTime) ) && ( is_numeric($fldRefTime) ) ) {
    $dtObj = DateTime::createFromFormat(TS_DISP_FMT, $fldRefTime);

    if ($dtObj !== FALSE) {
        $fldRefTime = $dtObj->getTimestamp();
    }
}

if (empty($fldRefTime)) {
    $fldRefTime = time();
}

if ( ! empty($fldChurch) && ( is_numeric($fldChurch) ) ) {
    $selChurch = new Church($fldChurch);

    $nextSvc = Days::nextSvcDate($fldRefTime);

    $theService = Days::getWeekDayCode($nextSvc);

    $dtObj = new DateTime();
    $dtObj->setTimestamp($nextSvc);

    $currAttendants = Days::getDayInfos($theService, $selChurch, $dtObj);

    $currMembers = new Member();

    $currMembers->set_church(new Church($fldChurch));

    $listMembers = $currMembers->getRecs();

    $fldOper = Utils\General::getHTTPVar(FLD_ACTION);

    if ( ( ! empty($fldOper) ) && (is_numeric($fldOper) ) ) {
        if ($fldOper == ACTION_UNSUBSCRIBE) {
            $idToRem = [];

            foreach($listMembers as $itemKey => $itemValue) {
                $optAct = Utils\General::getHTTPVar(FLD_ACT_OPT . $itemValue->get_id());

                $remItem = FALSE;

                if (! empty($optAct)) {
                    foreach ($currAttendants as $dayKey => $currDay) {
                        if ($currDay->get_member()->get_id() == $itemValue->get_id()) {
                            $idToRem[] = $currDay->get_id();

                            unset($currAttendants[$dayKey]);

                            break;
                        }
                    }
                }
            }

            if (count($idToRem)) {
                (new Days())->idsToDelete($idToRem);
            }
        }
        else if ($fldOper == ACTION_SUBSCRIBE) {
            $fldMemberId = Utils\General::getHTTPVar(FLD_MEMBER_ID);

            if ( ( ! empty($fldMemberId) ) && ( is_numeric($fldMemberId) ) ) {
                $theMember = new Member($fldMemberId);

                if ($theMember->get_name() != NULL) {
                    $newSubs = new Days();

                    $newSubs->set_member($theMember);

                    $newSubs->set_servicedate($fldRefTime);

                    $newSubs->set_service( Days::getWeekDayCode($fldRefTime) );

                    if ($newSubs->store()) {
                        $currAttendants = Days::getDayInfos($theService, $selChurch, $dtObj);
                    }
                    /*else {
                    }*/
                }
            }
        }
    }

    foreach ($currAttendants as $currDay) {
        foreach($listMembers as $itemKey => $theMember) {
            $usrEmail = $theMember->get_email();

            $isGenInvited = ( ($usrEmail != NULL) &&
                (strcmp($usrEmail, $theMember->get_church()->get_invitedemail()) == 0) );

            if (! $isGenInvited) {
                if ($currDay->get_member()->get_id() == $theMember->get_id()) {
                    unset($listMembers[$itemKey]);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Reserva de participaci&oacute;n</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../w3-2012.css">
        <script type="text/javascript" src="../common.js"></script>
        <script type="text/javascript">
<?php
$fstItem = TRUE;

$jsMemberList = '';

foreach ($currAttendants as $currDayInfo) {
    if ($fstItem) {
        $fstItem = FALSE;
    }
    else {
        $jsMemberList .= ', ';
    }

    $jsMemberList .= $currDayInfo->get_member()->get_id();
}
?>
            function validateFormDel() {
                var objFrm = document.forms["mainFormDel"];
                var memberList = [<?php echo $jsMemberList ?>];
                var checkCnt = 0;

                for (cntItem = 0; cntItem < memberList.length; cntItem++) {
                    for (elemCnt = 0; elemCnt < objFrm.elements.length; elemCnt++) {
                        if (objFrm.elements[elemCnt].name == "<?php echo FLD_ACT_OPT; ?>" + memberList[cntItem]) {
                            if (objFrm.elements[elemCnt].checked) {
                                checkCnt++;
                            }
                        }
                    }
                }

                if (checkCnt > 0) {
                    objFrm.submit();
                }
            }

            function validateFormInsert() {
                var objFrm = document.forms["mainFormInsert"];

                objFrm.submit();
            }
        </script>
    </head>
    <body>
<?php include 'menu.php' ?>
<?php
    $futureSvcs = [];

    $actTStamp = $nextSvc;

    $dtObjAux = new DateTime();
    $dtObjAux->setTimestamp($actTStamp);
    // Force be one hour after the service time
    $actTStamp = $dtObjAux->add(new DateInterval('PT1H'))->getTimestamp();

    for ($iter = 0; $iter < 2; $iter++) {
        $tstampAux = Days::nextSvcDate($actTStamp);

        $svcCodeAux = Days::getWeekDayCode($tstampAux);

        $dtObjAux->setTimestamp($tstampAux);

        $futureSvcs[] = array(PAG_RESERVATION . '?' . FLD_REFTIME . '=' . $dtObjAux->format(TS_DISP_FMT),
            $dtObjAux->format(Days::DATE_FORMAT_DB) . ' (' . Days::getWeekDayName($svcCodeAux) . ')');

        // Force be one hour after the service time
        $actTStamp = $dtObjAux->add(new DateInterval('PT1H'))->getTimestamp();
    }

    $weekDay = Days::getWeekDayName($theService);

    if (Days::isTheServiceToday($theService)) {
        $weekDay .= ', hoy d&iacute;a';
    }
?>
        <form name="mainFormDel" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
            <input type="hidden" id="<?php echo FLD_ACTION; ?>" name="<?php echo FLD_ACTION; ?>" value="<?php echo ACTION_UNSUBSCRIBE; ?>">
            <input type="hidden" id="<?php echo FLD_REFTIME; ?>" name="<?php echo FLD_REFTIME; ?>" value="<?php echo $dtObj->format(TS_DISP_FMT);; ?>">
            <div class="w3-container">
                <p>Elija el d&iacute;a del culto: <a href="<?php echo $futureSvcs[0][0] ?>"><?php echo $futureSvcs[0][1] ?></a> | <a href="<?php echo $futureSvcs[1][0] ?>"><?php echo $futureSvcs[1][1] ?></a></p>
                <p>Estos son los miembros registrados para el culto del d&iacute;a <?php echo $dtObj->format(Days::DATE_FORMAT_DB) . ' (' . $weekDay . ')' ?></p>
                <table class="w3-table-all">
                    <tr>
                        <th>&nbsp;</th>
                        <th>D&iacute;a del Registro</th>
                        <th>Hora del Registro</th>
                        <th>Nombre</th>
                    </tr>
<?php
foreach ($currAttendants as $currDayInfo) {
    $tstamp = $currDayInfo->get_timestamp();

    $currMember = $currDayInfo->get_member();
?>
                    <tr>
                        <td><input type="checkbox" id="<?php echo FLD_ACT_OPT . $currMember->get_id(); ?>" name="<?php echo FLD_ACT_OPT . $currMember->get_id(); ?>"></td>
                        <td><?php echo $tstamp->format('Y/m/d'); ?></td>
                        <td><?php echo $tstamp->format('H:i'); ?></td>
                        <td><?php echo $currMember->get_name() . ' ' . $currMember->get_surname(); ?></td>
                    </tr>
<?php
}
?>
                </table>
            </div>
            <div class="w3-container w3-center">
                <p><input class="w3-button w3-red" type="button" onclick="validateFormDel();" value="Quitar de la lista"></p>
            </div>
        </form>
<?php
if (count($currAttendants) < $selChurch->get_maxseats()) {
?>
        <form name="mainFormInsert" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
            <input type="hidden" id="<?php echo FLD_ACTION; ?>" name="<?php echo FLD_ACTION; ?>" value="<?php echo ACTION_SUBSCRIBE; ?>">
            <input type="hidden" id="<?php echo FLD_REFTIME; ?>" name="<?php echo FLD_REFTIME; ?>" value="<?php echo $dtObj->format(TS_DISP_FMT); ?>">
            <div class="w3-container">
                <p>Elija el miembro para la reserva:</p>
                <p>
                    <select class="w3-select w3-border" id="<?php echo FLD_MEMBER_ID ?>" name="<?php echo FLD_MEMBER_ID ?>">
<?php
foreach ($listMembers as $currMember) {
?>
                        <option value="<?php echo $currMember->get_id() ?>"><?php echo $currMember->get_name() . ' ' . $currMember->get_surname(); ?></option>
<?php
}
?>
                    </select>
                </p>
            </div>
            <div class="w3-container w3-center">
                <p><input class="w3-button w3-blue" type="button" onclick="validateFormInsert();" value="Reservar"></p>
            </div>
<?php
}
?>
        </form>
    </body>
</html>
