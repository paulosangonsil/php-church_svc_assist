<?php
require_once '../model/includes.inc';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Recuperaci&oacute;n de contrase&ntilde;a</title>
    </head>
    <body>
      <p>Su contrase&ntilde;a ser&aacute; enviada en un mensaje a su correo electr&oacute;nico</p>
      <form action="/action_page.php" method="post">
          <label for="email">Informe su correo electr&oacute;nico:</label>
          <input type="email" id="email" name="email"><br>
          <p><input type="button" onclick="alert('Hello World!')" value="Solicitar recuperaci&oacute;n de contrase&ntilde;a"> |
                <a href="<?php echo PAG_NEW_PROP ?>">Volver al inicio</a></p>
      </form>
    </body>
<!--
Se ha enviado un mensaje a su correo electrónico
Ya solicitaste la recuperación de contraseña hoy día. Revise sus mensajes, incluso en la caja SPAM
Aún no estás inscrito en el sistema. Haga clic aquí para hacerlo
-->
</html>
