<?php
require_once '../../model/includes.inc';
?>
        <div class="w3-bar w3-black">
            <a class="w3-bar-item w3-button" href="<?php echo PAG_REPORT ?>">Reportes</a>
            <a class="w3-bar-item w3-button" href="<?php echo PAG_VALIDATE_PROPS ?>">Pedidos de registro</a>
            <a class="w3-bar-item w3-button" href="<?php echo PAG_LIST_MEMBERS ?>">Listado de Miembros</a>
            <a class="w3-bar-item w3-button" href=<?php echo PAG_RESERVATION ?>>Reserva de cupo</a>
            <a class="w3-bar-item w3-button w3-red" href="index.php?<?php echo FLD_OPER . '=' . _OPER_LOGOFF; ?>">Salir</a>
        </div>
