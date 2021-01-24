<?php
require_once 'includes.inc';

/**
 *
 * @author Administrator
 *
 */
class Proposal extends \Abstract_User {
    /**
     */
    public function __construct($connId = UNDEFINED) {
        $this->TB_NAMES = array(TB_PREFIX . "proposal");

        parent::__construct($connId);
    }

    protected function _isEmailValid(): bool {
        $mtdRet = FALSE;

        $email = $this->get_email();

        if ($email != NULL) {
            $mtdRet = ! Member::emailExists($email, $this->get_church());
        }

        return $mtdRet;
    }

    protected function _isTelValid(): bool {
        $mtdRet = FALSE;

        $tel = $this->get_tel();

        if ($tel != NULL) {
            $mtdRet = ! Member::telExists($tel, $this->get_church());
        }

        return $mtdRet;
    }

    protected function _isSocialSecValid(): bool {
        $mtdRet = FALSE;

        $socsec = $this->get_socialsec();

        if ($socsec != NULL) {
            $mtdRet = ! Member::socialSecExists($socsec, $this->get_church());
        }

        return $mtdRet;
    }

    protected function _storeAlertNew(): bool {
        $mtdRet = FALSE;

        $destPersons = array('psgsilva1@gmail.com');

        foreach ($destPersons as $emailAddr) {
            $msgBody = 'El hermano ' . $this->get_name() . ' ' . $this->get_surname() .
                        ' ha solicitado ingreso en el sistema.\n***Este es un mensage automatico';

            $mtdRet = mail($emailAddr, '[Church Service Assist] Nueva solicitacion de registro', $msgBody);
        }

        return $mtdRet;
    }
}
