<?php
require_once '../../model/includes.inc';

$strIDUsr = _checkLogonCust();

if ($strIDUsr == NULL) {
    //die ("Error" . " File: " . __FILE__ . " on line: " . __LINE__ . " result = procedencia invalida" );
    $strLoc = 'Location: ./';
    header($strLoc);
}

const ACTION_VALIDATE = "1";
const ACTION_DISCARD = "2";

const FLD_CHURCH = "church_id";
const FLD_USR_NAME = "usr_name";
const FLD_ACT_OPT = "act_opt_";

$fldChurch = $_SESSION[_SESSION_CHURCH];

$forceExit = TRUE;

$warnMsg = NULL;
$msgType = MSG_TYPE_ERROR;

if ( ! empty($fldChurch) && ( is_numeric($fldChurch) ) ) {
    $proposals = new Proposal();

    $proposals->set_church(new Church($fldChurch));

    $listProp = $proposals->getRecs();

    $idToRem = [];

    foreach($listProp as $itemKey => $itemValue) {
        $optAct = Utils\General::getHTTPVar(FLD_ACT_OPT . $itemValue->get_id());

        $remItem = FALSE;

        if (! empty($optAct)) {
            if ($optAct == ACTION_VALIDATE) {
                $remItem = TRUE;

                $newMember = new Member();
                $newMember->copyFrom($itemValue);

                if ($newMember->store()) {
                    $warnMsg = 'Acciones realizadas exitosamente';
                    $msgType = MSG_TYPE_SUCCESS;
                }
                else {
                    $warnMsg = 'Hubo error en la realizaci&oacute;n de las acciones';
                }
            }
            else if ($optAct == ACTION_DISCARD) {
                $remItem = TRUE;
            }

            if ($remItem) {
                $idToRem[] = $itemValue->get_id();

                unset($listProp[$itemKey]);
            }
        }
    }

    if (count($idToRem)) {
        $proposals->idsToDelete($idToRem);
    }

    if (count($listProp)) {
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
        <title>Validaci&oacute;n de registro de miembro</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../w3-2012.css">
        <script type="text/javascript">
            function validateForm(subscr) {
                var objFrm = document.forms["mainForm"];

                objFrm.submit();
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
                            <th>Validar</th>
                            <th>Descartar</th>
                            <th>Fecha solicitaci&oacute;n</th>
                            <th>Nombre completo</th>
                            <th>Correo eletr&oacute;nico</th>
                            <th>Tel&eacute;fono</th>
                        </tr>
<?php
foreach ($listProp as $proposal) {
?>
                        <tr>
                            <td><input type="radio" id="<?php echo FLD_ACT_OPT . $proposal->get_id(); ?>" name="<?php echo FLD_ACT_OPT . $proposal->get_id(); ?>" value="<?php echo ACTION_VALIDATE ?>" checked></td>
                            <td><input type="radio" id="<?php echo FLD_ACT_OPT . $proposal->get_id(); ?>" name="<?php echo FLD_ACT_OPT . $proposal->get_id(); ?>" value="<?php echo ACTION_DISCARD ?>"></td>
                            <td><?php echo $proposal->get_timestamp()->format(Days::TIMESTAMP_FORMAT_DB); ?></td>
                            <td><?php echo $proposal->get_name() . ' ' . $proposal->get_surname(); ?></td>
                            <td><?php $fldCnt = $proposal->get_email(); echo ($fldCnt == NULL) ? '&nbsp;' : $fldCnt; ?></td>
                            <td><?php $fldCnt = $proposal->get_tel(); echo ($fldCnt == NULL) ? '&nbsp;' : $fldCnt; ?></td>
                        </tr>
<?php
}
?>
                    </table>
                </p>
            </div>
            <div class="w3-container w3-center">
                <input class="w3-button w3-blue" type="button" onclick="validateForm()" value="Aplicar acciones">
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
