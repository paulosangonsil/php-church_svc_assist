<?php
require_once '../../model/includes.inc';

$strIDUsr = _checkLogonCust();

if ($strIDUsr == NULL) {
    //die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . " result = procedencia invalida" );
    $strLoc = 'Location: ./';
    header($strLoc);
}

const FLD_REFTIME = 'reftime';
const TS_DISP_FMT = 'YmdHi';

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

    count($currAttendants) == 0;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Reporte de participantes</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../w3-2012.css">
    </head>
    <body>
<?php include 'menu.php' ?>
<?php
    $prevSvcs = [];

    $actTStamp = $fldRefTime;

    $dtObjAux = new DateTime();

    for ($iter = 0; $iter < 2; $iter++) {
        $tstampAux = Days::prevSvcDate($actTStamp);

        $svcCodeAux = Days::getWeekDayCode($tstampAux);

        $dtObjAux->setTimestamp($tstampAux);

        $prevSvcs[] = array(PAG_REPORT . '?' . FLD_REFTIME . '=' . $dtObjAux->format(TS_DISP_FMT),
                            $dtObjAux->format(Days::DATE_FORMAT_DB) . ' (' . Days::getWeekDayName($svcCodeAux) . ')');

        $actTStamp = $tstampAux;
    }

    $weekDay = Days::getWeekDayName($theService);

    if (Days::isTheServiceToday($theService)) {
        $weekDay .= ', hoy d&iacute;a';
    }
?>
        <div class="w3-container">
            <p>Elija el d&iacute;a del culto: <a href="<?php echo $prevSvcs[1][0] ?>"><?php echo $prevSvcs[1][1] ?></a> | <a href="<?php echo $prevSvcs[0][0] ?>"><?php echo $prevSvcs[0][1] ?></a></p>
            <p>Listado de participaci&oacute;n para el culto del d&iacute;a <?php echo $dtObj->format(Days::DATE_FORMAT_DB) . ' (' . $weekDay . ')' ?>:</p>
        </div>
        <form name="mainForm" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
            <div class="w3-container">
                <table class="w3-table-all w3-large">
                    <tr>
                        <th>D&iacute;a del Registro</th>
                        <th>Hora del Registro</th>
                        <th>Nombre</th>
                    </tr>
<?php
foreach ($currAttendants as $currMember) {
    $tstamp = $currMember->get_timestamp();
?>
                    <tr>
                        <td><?php echo $tstamp->format('Y/m/d'); ?></td>
                        <td><?php echo $tstamp->format('H:i'); ?></td>
                        <td><?php echo $currMember->get_member()->get_name() . ' ' . $currMember->get_member()->get_surname(); ?></td>
                    </tr>
<?php
}
?>
                </table>
            </div>
        </form>
    </body>
</html>
