<?php
require_once '../../model/includes.inc';

$strIDUsr = _checkLogonCust();

if ($strIDUsr == NULL) {
    //die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . " result = procedencia invalida" );
    $strLoc = 'Location: ./';
    header($strLoc);
}

const REC_SELECTED = "1";

const FLD_CHURCH = "church_id";
const FLD_USR_NAME = "usr_name";
const FLD_ACT_OPT = "act_opt_";

$fldChurch = $_SESSION[_SESSION_CHURCH];

$forceExit = TRUE;

$warnMsg = NULL;

if ( ! empty($fldChurch) && ( is_numeric($fldChurch) ) ) {
    $currMembers = new Member();

    $currMembers->set_church(new Church($fldChurch));

    $listMembers = $currMembers->getRecs();

    $idToRem = [];

    foreach($listMembers as $itemKey => $itemValue) {
        $optAct = Utils\General::getHTTPVar(FLD_ACT_OPT . $itemValue->get_id());

        $remItem = FALSE;

        if (! empty($optAct)) {
            $idToRem[] = $itemValue->get_id();

            unset($listMembers[$itemKey]);
        }
    }

    if (count($idToRem)) {
        $currMembers->idsToDelete($idToRem);
    }

    if (count($listMembers)) {
        $forceExit = FALSE;
    }
}

/*if ($forceExit) {
    die('System internal error');
}*/
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Listado de miembro</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../w3-2012.css">
        <script type="text/javascript">
            function validateForm(subscr) {
                var objFrm = document.forms["mainForm"];
                var memberList = [<?php $fstItem = TRUE; foreach ($listMembers as $currMember) { if ($fstItem) {$fstItem = FALSE;} else {echo ', ';} echo $currMember->get_id(); } ?>];
                var checkCnt = 0;

                for (cntItem = 0; cntItem < memberList.length; cntItem++) {
                    currMember = document.getElementById("<?php echo FLD_ACT_OPT; ?>" + memberList[cntItem]);
                    if (currMember.checked) {
                        checkCnt++;
                    }
                }

                if (checkCnt > 0) {
                    objFrm.submit();
                }
            }
        </script>
    </head>
    <body>
<?php include 'menu.php' ?>
        <form name="mainForm" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
            <div class="w3-container">
                <p>
                    <table class="w3-table-all w3-large">
                        <tr>
                            <th>&nbsp;</th>
                            <th>Nombre completo</th>
                            <th>RUT</th>
                            <th>Tel&eacute;fono</th>
                            <th>Correo electr&oacute;nico</th>
                            <th>Contrase&ntilde;a</th>
                            <th>&Uacute;ltima solicitaci&oacute;n</th>
                        </tr>
<?php
foreach ($listMembers as $currMember) {
?>
                        <tr>
                            <td><input <?php if ($currMember->isAdm()) { echo 'disabled'; } ?> type="checkbox" id="<?php echo FLD_ACT_OPT . $currMember->get_id(); ?>" name="<?php echo FLD_ACT_OPT . $currMember->get_id(); ?>"></td>
                            <td><?php echo $currMember->get_name() . ' ' . $currMember->get_surname(); ?></td>
                            <td><?php $fldCnt = $currMember->get_socialsec(); echo ($fldCnt == NULL) ? '&nbsp;' : $fldCnt; ?></td>
                            <td><?php $fldCnt = $currMember->get_tel(); echo ($fldCnt == NULL) ? '&nbsp;' : $fldCnt; ?></td>
                            <td><?php $fldCnt = $currMember->get_email(); echo ($fldCnt == NULL) ? '&nbsp;' : $fldCnt; ?></td>
                            <td><?php $fldCnt = $currMember->get_passwd(); echo ($fldCnt == NULL) ? '&nbsp;' : $fldCnt; ?></td>
                            <td><?php echo $currMember->get_timestamp()->format(Days::TIMESTAMP_FORMAT_DB); ?></td>
                        </tr>
<?php
}
?>
                    </table>
                </p>
            </div>
            <div class="w3-container w3-center">
                <input class="w3-button w3-blue" type="button" onclick="validateForm()" value="Borrar miembros seleccionados">
            </div>
      </form>
<?php
$strCSSClass = "w3-pale-red";

if ($warnMsg != NULL) {
    if ($msgType == MSG_TYPE_WARN) {
        $strCSSClass = "w3-orange";
    }
    else if ($msgType == MSG_TYPE_SUCCESS) {
        $strCSSClass = "w3-green";
    }
?>
        <div class="w3-panel <?php echo $strCSSClass ?> w3-border">
            <p><?php echo $warnMsg ?></p>
        </div>
<?php } ?>
    </body>
</html>
