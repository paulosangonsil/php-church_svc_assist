<?php
require_once '../model/includes.inc';

const LOGIN_KEY_SOCIAL_SEC = [1, "RUT"];
const LOGIN_KEY_EMAIL = [2, "E-mail"];
const LOGIN_KEY_TEL = [3, "Telef&oacute;no"];

const ACTION_SUBSCRIBE = "1";
const ACTION_UNSUBSCRIBE = "2";

const FLD_ACTION = "prop_action";
const FLD_LOGIN_KEY = "method_id";
const FLD_KEY_VALUE = "method_value";
const FLD_PASSWD = "passwd";

$selChurch = UNDEFINED;

$fldOper = Utils\General::getHTTPVar(FLD_ACTION);
$fldChurch = Utils\General::getHTTPVar(FLD_CHURCH);
$fldLoginMtd = Utils\General::getHTTPVar(FLD_LOGIN_KEY);
$fldLoginVl = Utils\General::getHTTPVar(FLD_KEY_VALUE);
$fldPasswd = Utils\General::getHTTPVar(FLD_PASSWD);

$warnMsg = NULL;

$msgType = MSG_TYPE_ERROR;

if ( ( ! empty($fldOper) && is_numeric($fldOper) ) &&
    ( ! empty($fldChurch) && is_numeric($fldChurch) ) &&
    ( ! empty($fldLoginMtd) && is_numeric($fldLoginMtd) ) &&
    ( ! empty($fldLoginVl) ) &&
    ( ! empty($fldPasswd) ) ) {
    $theMember = new Member();

    $selChurch = new Church($fldChurch);

    $theMember->set_church($selChurch);

    switch ($fldLoginMtd) {
        case LOGIN_KEY_SOCIAL_SEC[0]: {
            $theMember->set_socialsec($fldLoginVl);

            break;
        }

        case LOGIN_KEY_EMAIL[0]: {
            $theMember->set_email($fldLoginVl);

            break;
        }

        case LOGIN_KEY_TEL[0]: {
            $theMember->set_tel($fldLoginVl);

            break;
        }
    }

    if (! empty($theMember->get_name())) {
        if ( ! strcmp($theMember->get_passwd(), $fldPasswd) ) {
            $newSubs = new Days();

            $nextSvc = Days::nextSvcDate($newSubs->get_timestamp()->getTimestamp()/*->format(Abstract_User::TIMESTAMP_FORMAT_DB)*/);

            $theService = Days::getWeekDayCode($nextSvc);

            $addCond = array($theMember->COL_NAMES[Member::COL_NAME_ID] => $theMember->get_id());

            $dtObj = new DateTime();
            $dtObj->setTimestamp($nextSvc);

            $theresRec = Days::getDayInfos($theService, $theMember->get_church(), $dtObj, $addCond);

            if ($fldOper == ACTION_SUBSCRIBE) {
                if ( ($theresRec != NULL) && (count($theresRec) > 0) ) {
                    $usrEmail = $theMember->get_email();

                    $isGenInvited = ( ($usrEmail != NULL) &&
                        (strcmp($usrEmail, $theMember->get_church()->get_invitedemail()) == 0) );

                    if (! $isGenInvited) {
                        $warnMsg = 'Usted ya est&aacute; inscrito para el culto del d&iacute;a ' . Days::getWeekDayName($theService);
                        $msgType = MSG_TYPE_WARN;
                    }
                }

                if ($warnMsg == NULL) {
                    $newSubs->set_member($theMember);

                    $newSubs->set_servicedate($nextSvc);

                    $newSubs->set_service($theService);

                    if ($newSubs->store()) {
                        $warnMsg = '&iexcl;Gloria a Dios! Est&aacute;s inscrito para el culto del d&iacute;a ' . Days::getWeekDayName($theService);
                        $msgType = MSG_TYPE_SUCCESS;
                    }
                    else {
                        $warnMsg = 'Hubo un error en el intento de registrar su solicitaci&oacute;n';
                    }
                }
            }
            else if ($fldOper == ACTION_UNSUBSCRIBE) {
                if ( ($theresRec != NULL) && (count($theresRec)) ) {
                    if ($newSubs->idsToDelete(array($theresRec[0]->get_id()))) {
                        $warnMsg = 'Solicitaci&oacute;n ejecutada con &eacute;xito: usted ya no est&aacute; m&aacute;s en la lista para el culto del d&iacute;a ' . Days::getWeekDayName($theService) . '. Gracias por avisar';
                        $msgType = MSG_TYPE_SUCCESS;
                    }
                    else {
                        $warnMsg = 'Error en el intento de quitar su participaci&oacute; del culto del d&iacute;a ' . Days::getWeekDayName($theService);
                    }
                }
                else {
                    $warnMsg = 'Usted <b>no</b> est&aacute; inscrito para el culto del d&iacute;a ' . Days::getWeekDayName($theService);
                }
            }
        }
        else {
            $warnMsg = 'Contrase&ntilde;a no corresponde';
        }
    }
    else {
        $warnMsg = 'A&uacute;n no est&aacute;s inscrito en el sistema. Haga clic <a href="' . PAG_NEW_MEMBER . '">aqu&iacute;</a> para hacerlo';
        $msgType = MSG_TYPE_WARN;
    }
    //Que Dios le bendiga por tener paciencia de esperar – le avisaremos por correo caso haya desistencia de alguno hermano
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Inscripci&oacute;n de participaci&oacute;n</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="w3-2012.css">
        <script type="text/javascript" src="common.js"></script>
        <script type="text/javascript">
            window.onload = updateValue;

            function updateValue() {
                var findField = document.getElementById('<?php echo FLD_USRDATEZN; ?>');
                findField.value = ( new Date() ).getTimezoneOffset();
            }

            function validateForm(subscr) {
                const LOGIN_KEY_SOCIAL_SEC = <?php echo LOGIN_KEY_SOCIAL_SEC[0] ?>;
                const LOGIN_KEY_EMAIL = <?php echo LOGIN_KEY_EMAIL[0] ?>;
                const LOGIN_KEY_TEL = <?php echo LOGIN_KEY_TEL[0] ?>;

                var objFrm = document.forms["mainForm"];
                var loginKey = objFrm["<?php echo FLD_LOGIN_KEY; ?>"].value;
                var loginKeyValue = objFrm["<?php echo FLD_KEY_VALUE; ?>"].value.trim();
                var loginPasswd = objFrm["<?php echo FLD_PASSWD; ?>"].value;
                var validInfos = false;

                if (loginKey == LOGIN_KEY_SOCIAL_SEC) {
                    validInfos = validaRut(loginKeyValue);
                }
                else if (loginKey == LOGIN_KEY_EMAIL) {
                    validInfos = validateEmail(loginKeyValue);
                }
                else {
                    if (loginKeyValue.length != 0) {
                        loginKeyValue = formatTelNmb(loginKeyValue);

                        validInfos = validateTel(loginKeyValue);
                    }
                }

                if (validInfos) {
                    if (loginPasswd.length > 5) {
                        objFrm["<?php echo FLD_ACTION; ?>"].value = subscr;
                        objFrm["<?php echo FLD_KEY_VALUE; ?>"].value = loginKeyValue;

                        objFrm.submit();
                    }
                    else {
                        alert("La contrasena digitada no esta en el estandar");
                    }
                }
                else {
                    alert("La informacion digitada no es valida!");
                }
            }
        </script>
    </head>
    <body>
      <form name="mainForm" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
        <input type="hidden" name="<?php echo FLD_USRDATEZN ?>" id="<?php echo FLD_USRDATEZN ?>" value="" >
        <div class="w3-container">
          <h2>Hola, bienvenido a la p&aacute;gina de inscripci&oacute;n de participaci&oacute;n en los cultos de la <b>Iglesia Latina Pr&iacute;ncipe de Paz</b></h2>
<?php
$churchs = Church::listAll();

if ($selChurch == UNDEFINED) {
    $selChurch = $churchs[0];
}

$dtObj = new DateTime();

$nextSvc = Days::nextSvcDate($dtObj->getTimestamp());

$dtObj->setTimestamp($nextSvc);

$theService = Days::getWeekDayCode($nextSvc);

$currAttendants = Days::getDayInfos($theService, $selChurch, $dtObj);

$currUsedSeats = 0;

if ($currAttendants != NULL) {
    $currUsedSeats = count($currAttendants);
}

$availSeats = $selChurch->get_maxseats() - $currUsedSeats;

$msgSeatCount = "";
$weekDay = '<b>' . Days::getWeekDayName($theService) . '</b>';

if (Days::isTheServiceToday($theService)) {
    $weekDay .= ' (hoy d&iacute;a)';
}

if ($availSeats > 0) {
    $msgSeatCount = "Todav&iacute;a nos quedan <strong><em><mark>" . $availSeats . '</mark></em></strong>';
}
else  {
    $msgSeatCount = "Ya <b>no resta</b>";
}
?>
          <?php echo $msgSeatCount; ?> cupo(s) para el culto del d&iacute;a <?php echo $weekDay; ?>.
          <?php /*if ($availSeats == 0) { echo "Pero p&oacute;ngase en la fila - tal vez haya desistencia y le avisar&eacute; en este caso"; }*/ ?>
        </div>
        <div class="w3-container">
          <input type="hidden" id="<?php echo FLD_ACTION; ?>" name="<?php echo FLD_ACTION; ?>" value="<?php echo ACTION_SUBSCRIBE; ?>">
          <p><select class="w3-select" id="<?php echo FLD_CHURCH; ?>" name="<?php echo FLD_CHURCH; ?>">
<?php foreach ($churchs as $church) { ?>
              <option value="<?php echo $church->get_id() ?>"><?php echo $church->get_name() ?></option>
<?php } ?>
          </select></p>
        </div>
        <div class="w3-row-padding">
            <div class="w3-half">
              <select class="w3-select w3-border" id="<?php echo FLD_LOGIN_KEY; ?>" name="<?php echo FLD_LOGIN_KEY; ?>">
                  <option value="" disabled>Informe la manera de identificaci&oacute;n</option>
                  <option value="<?php echo LOGIN_KEY_SOCIAL_SEC[0] ?>"><?php echo LOGIN_KEY_SOCIAL_SEC[1] ?></option>
                  <option value="<?php echo LOGIN_KEY_EMAIL[0] ?>" selected><?php echo LOGIN_KEY_EMAIL[1] ?></option>
                  <option value="<?php echo LOGIN_KEY_TEL[0] ?>"><?php echo LOGIN_KEY_TEL[1] ?></option>
              </select>
            </div>
            <div class="w3-half">
                <input class="w3-input w3-border" type="text" id=<?php echo FLD_KEY_VALUE; ?> name=<?php echo FLD_KEY_VALUE; ?> placeholder="Elija la manera de identificaci&oacute;n y digite aqu&iacute; de acuerdo con lo seleccionado (e-mail o rut o telefono)"><br>
            </div>
        </div>
        <div class="w3-container">
            <input class="w3-input w3-border" type="password" id=<?php echo FLD_PASSWD; ?> name=<?php echo FLD_PASSWD; ?> placeholder="Informe su contrase&ntilde;a"><br>
            <!-- <a href="<?php echo PAG_RECOVER_PASSWD; ?>">(&iexcl;Me olvid&eacute; la contrase&ntilde;a!)</a><br> -->
            <a href="<?php echo PAG_NEW_MEMBER; ?>">(&iexcl;No tengo registro en el sistema!)</a>
        </div>
        <div class="w3-container w3-center">
          <p><?php if ($availSeats > 0) { ?><input class="w3-button w3-blue" type="button" onclick="validateForm(<?php echo ACTION_SUBSCRIBE; ?>);" value="Inscribir"> <?php } ?>
                <input class="w3-button w3-red" type="button" onclick="validateForm(<?php echo ACTION_UNSUBSCRIBE; ?>);" value="Borrar inscripci&oacute;n"></p>
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
<?php
if (Utils\General::getHTTPVar(FLD_USRDATEZN) == NULL) {
?>
    <script type="text/javascript">
        location.href = location.href + '?<?php echo FLD_USRDATEZN; ?>=' + ( new Date() ).getTimezoneOffset();
    </script>
<?php
}
?>
</html>
