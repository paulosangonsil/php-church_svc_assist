<?php
require_once '../model/includes.inc';

const FLD_NAME = "name";
const FLD_SURNAME = "apellido";
const FLD_EMAIL = "email";
const FLD_TEL = "tel";
const FLD_SOC_SEC = "rut";
const FLD_CHURCH = "church_id";

$selChurch = UNDEFINED;

$fldSurname = Utils\General::getHTTPVar(FLD_SURNAME);
$fldName = Utils\General::getHTTPVar(FLD_NAME);
$fldEmail = Utils\General::getHTTPVar(FLD_EMAIL);
$fldTel = Utils\General::getHTTPVar(FLD_TEL);
$fldSocSec = Utils\General::getHTTPVar(FLD_SOC_SEC);
$fldChurch = Utils\General::getHTTPVar(FLD_CHURCH);

$warnMsg = NULL;
$msgType = MSG_TYPE_ERROR;

if ( ( ! empty($fldSurname) && ( ! is_numeric($fldSurname) ) ) &&
    ( ! empty($fldName) && ( ! is_numeric($fldName) ) ) &&
    ( ! empty($fldChurch) && ( is_numeric($fldChurch) ) ) &&
    ( ( ! empty($fldEmail) ) || ( ! empty($fldTel) ) || ( ! empty($fldSocSec) ) ) ) {
    $selChurch = new Church($fldChurch);

    $theProp = new Proposal();

    $theProp->set_church($selChurch);

    if ( ! empty($fldSocSec) ) {
        $theProp->set_socialsec($fldSocSec);
    }

    if ( ! empty($fldTel) ) {
        $theProp->set_tel($fldTel);
    }

    if ( ! empty($fldEmail) ) {
        $theProp->set_email($fldEmail);
    }

    $isValid = ($theProp->get_name() == NULL);

    if ($isValid) {
        $theProp->set_name(htmlentities($fldName));
        $theProp->set_surname(htmlentities($fldSurname));

        if ($theProp->store()) {
            $msgAdd = 'Alguien de la iglesia le avisar&aacute; cuando el registro en el sistema est&eacute; listo';

            if ( ! empty($fldEmail) ) {
                $msgAdd = 'Recibir&aacute;s un correo confirmando su registro en el sistema';
            }

            $warnMsg = '&iexcl;Registro solicitado! ' . $msgAdd;
            $msgType = MSG_TYPE_SUCCESS;
        }
        else {
            $warnMsg = 'Ha pasado un error en el registro de su petici&oacute;n. &iquest;Usted ya est&aacute; registrado en el sistema?';
        }
    }
    else {
        $warnMsg = 'Ya est&aacute;s inscrito en el sistema';
        $msgType = MSG_TYPE_WARN;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Registro de miembro</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="w3-2012.css">
        <script type="text/javascript" src="common.js"></script>
        <script type="text/javascript">
            function validateForm(subscr) {
                var objFrm = document.forms["mainForm"];
                var fldEmail = objFrm["<?php echo FLD_EMAIL; ?>"].value.trim();
                var fldTel = objFrm["<?php echo FLD_TEL; ?>"].value;
                var fldSocSec = objFrm["<?php echo FLD_SOC_SEC; ?>"].value;
                var fldName = objFrm["<?php echo FLD_NAME; ?>"].value.trim();
                var fldSurname = objFrm["<?php echo FLD_SURNAME; ?>"].value.trim();
                var validInfos = false;
                var wrnMsg = '';

                validInfos = validateName(fldName, 20);

                if (validInfos) {
                    validInfos = validateName(fldSurname, 30);
                }
                else {
                    wrnMsg = "Informe su(s) nombre(s)!";
                }

                typedFlds = 0;

                if (validInfos) {
                    if (fldEmail.length != 0) {
                        validInfos = validateEmail(fldEmail);

                        typedFlds++;
                    }

                    if (validInfos) {
                        if (fldTel.length != 0) {
                            fldTel = formatTelNmb(fldTel);

                            validInfos = validateTel(fldTel);

                            typedFlds++;
                        }
                    }
                    else {
                        wrnMsg = "Correo invalido!";
                    }

                    if (validInfos) {
                        if (fldSocSec.length != 0) {
                            validInfos = validaRut(fldSocSec);

                            typedFlds++;
                        }
                    }
                    else {
                        if (wrnMsg.length == 0) {
                            wrnMsg = "Numero de telefono invalido!";
                        }
                    }
                }
                else {
                    if (wrnMsg.length == 0) {
                        wrnMsg = "Informe su apellido!";
                    }

                    typedFlds = 1;
                }

                if (validInfos) {
                    objFrm["<?php echo FLD_TEL; ?>"].value = fldTel;
                    objFrm["<?php echo FLD_EMAIL; ?>"].value = fldEmail;
                    objFrm["<?php echo FLD_NAME; ?>"].value = fldName;
                    objFrm["<?php echo FLD_SURNAME; ?>"].value = fldSurname;

                    objFrm.submit();
                }
                else {
                    if (wrnMsg.length == 0) {
                        wrnMsg = "RUT invalido!";
                    }
                    else if (typedFlds == 0) {
                        wrnMsg = "Es necesario informar el e-mail o un numero de telefono o el RUT";
                    }
                }

                if (wrnMsg.length > 0) {
                    alert(wrnMsg);
                }
            }
        </script>
    </head>
    <body>
        <div class="w3-container">
            <h2>Bienvenido a la p&aacute;gina de registro para participaci&oacute;n en los cultos de nuestra amada <b>Iglesia Latina Pr&iacute;ncipe de Paz</b></h2>
            As&iacute; que una persona validar sus informaciones, ser&aacute; enviado al correo informado, una contrase&ntilde;a para su inscripci&oacute;n en los cultos
        </div>
      <form name="mainForm" action="<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>" method="post">
        <div class="w3-container">
          <p><select class="w3-select" id="<?php echo FLD_CHURCH; ?>" name="<?php echo FLD_CHURCH; ?>">
<?php
$churchs = Church::listAll();

if ($selChurch == UNDEFINED) {
    $selChurch = $churchs[0];
}

foreach ($churchs as $church) {
?>
              <option value="<?php echo $church->get_id() ?>"><?php echo $church->get_name() ?></option>
<?php
}
?>
          </select></p>
        </div>
        <div class="w3-container">
          <input class="w3-input w3-border" type="text" id="<?php echo FLD_NAME; ?>" name="<?php echo FLD_NAME; ?>" placeholder="Informe su(s) nombre(s)"><br>
        </div>
        <div class="w3-container">
          <input class="w3-input w3-border" type="text" id="<?php echo FLD_SURNAME; ?>" name="<?php echo FLD_SURNAME; ?>" placeholder="Informe su apellido"><br>
        </div>
        <div class="w3-container">
          <input class="w3-input w3-border" type="email" id="<?php echo FLD_EMAIL; ?>" name="<?php echo FLD_EMAIL; ?>" placeholder="Informe su correo electr&oacute;nico"><br>
        </div>
        <div class="w3-container">
          <input class="w3-input w3-border" type="text" id="<?php echo FLD_TEL; ?>" name="<?php echo FLD_TEL; ?>" placeholder="Informe su tel&eacute;fono"><br>
        </div>
        <div class="w3-container">
          <input class="w3-input w3-border" type="text" id="<?php echo FLD_SOC_SEC; ?>" name="<?php echo FLD_SOC_SEC; ?>" placeholder="Informe su RUT con el gui&oacute;n: ejemplo 23654987-8">
        </div>
        <div class="w3-container w3-center">
          <p><input class="w3-button w3-blue" type="button" onclick="validateForm()" value="Solicitar registro"> |
                <a href="<?php echo PAG_NEW_PROP ?>">Volver al inicio</a></p>
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
