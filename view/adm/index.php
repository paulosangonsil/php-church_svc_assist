<?php
require_once '../../model/includes.inc';

$_isToLogOff = Utils\General::getHTTPVar(FLD_OPER);

$_doneLogOff = FALSE;

if ($_isToLogOff == _OPER_LOGOFF) {
    $_doneLogOff = _logOffCust();
}

if (! $_doneLogOff) {
    $usrId = _checkLogonCust();

    // Encriptar o ID do usr
    if ($usrId != NULL) {
        $usrId = urlencode ($usrId);

        $strLoc = 'Location: ' . PAG_RESERVATION . '?' . FLD_USRNAME . "=$usrId";
        header($strLoc);
    }
    else {
        _logOffCust();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Acceso administrativo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../w3-2012.css">
        <script type="text/javascript" src="../common.js"></script>
        <script type="text/javascript">
            window.onload = updateValue;

            function updateValue() {
                var findField = document.getElementById('<?php echo FLD_USRDATEZN; ?>');
                findField.value = ( new Date() ).getTimezoneOffset();
            }

            function validateForm(subscr) {
                var objFrm = document.forms["mainForm"];
                var fldUsrId = objFrm["<?php echo FLD_USRNAME; ?>"].value.trim();
                var fldUsrPasswd = objFrm["<?php echo FLD_USRPWD; ?>"].value;
                var validInfos = false;
                var wrnMsg = '';

                validInfos = validateEmail(fldUsrId);

                if (validInfos) {
                    validInfos = ( (fldUsrPasswd.length > 3) && (fldUsrPasswd.length < 30) );
                }
                else {
                    wrnMsg = "E-mail no valido!";
                }

                if (validInfos) {
                    objFrm["<?php echo FLD_USRNAME; ?>"].value = fldUsrId;

                    objFrm.submit();
                }
                else {
                    if (wrnMsg.length == 0) {
                        wrnMsg = "Contrasena no valida!";
                    }
                }

                if (wrnMsg.length > 0) {
                    alert(wrnMsg);
                }
            }
        </script>
    </head>
    <body>
        <p>
            <form name="mainForm" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
                <input type="hidden" name="<?php echo FLD_USRDATEZN ?>" id="<?php echo FLD_USRDATEZN ?>" value="" >
                <div class="w3-container">
                    <p>
                        <select class="w3-select" id="<?php echo FLD_CHURCH; ?>" name="<?php echo FLD_CHURCH; ?>">
<?php foreach (Church::listAll() as $church) { ?>
                            <option value="<?php echo $church->get_id() ?>"><?php echo $church->get_name() ?></option>
<?php } ?>
                        </select>
                    </p>
                </div>
                <div class="w3-container">
                    <input class="w3-input w3-border" type="text" id="<?php echo FLD_USRNAME; ?>" name="<?php echo FLD_USRNAME; ?>" placeholder="Usuario"><br>
                </div>
                <div class="w3-container">
                    <input class="w3-input w3-border" type="password" id="<?php echo FLD_USRPWD; ?>" name="<?php echo FLD_USRPWD; ?>" placeholder="Contrase&ntilde;a"><br>
                </div>
                <div class="w3-container w3-center">
                    <p><input class="w3-button w3-blue" type="button" onclick="validateForm()" value="Acceder"> |
                            <a href="../<?php echo PAG_NEW_PROP ?>">Volver al inicio</a></p>
                </div>
            </form>
        </p>
    </body>
</html>
