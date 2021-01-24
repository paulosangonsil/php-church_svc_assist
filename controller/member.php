<?php
require_once 'includes.inc';

/**
 *
 * @author Administrator
 *
 */
class Member extends \Abstract_User {
    const   COL_PASSWD         = 8;
    const   COL_ATTRIB         = 9;

    const   ATTR_ROOT = 0x01;
    const   ATTR_ADM  = 0x20;

    protected /*string*/ $_passwd;
    protected /*int*/ $_attrib = 0;

    /**
     */
    public function __construct($connId = UNDEFINED) {
        $this->COL_NAMES[] = "passwd";
        $this->COL_NAMES[] = "attrib";

        $this->TB_NAMES = array(TB_PREFIX . "member");

        parent::__construct($connId);
    }

    protected function _genPasswd(): string {
        $randomWords = array('jesus', 'salvador', 'salva', 'dios',
                            'soberano', 'tremendo', 'paz', 'amor',
                            'vida', 'salva', 'salvacion', 'cielos',
                            'espiritu', 'gozo', 'bendicion', 'alegria',
                            'renuevo', 'victoria', 'santo', 'proteccion',
                            'ayuda', 'liberacion', 'gloria', 'celeste');

        $maxWords = 3;

        $mtdRet = '';

        $randIndex = array_rand($randomWords, $maxWords);

        for ($cnt = 0; $cnt < $maxWords - 1; $cnt++) {
            $mtdRet .= $randomWords[$randIndex[$cnt]];
        }

        $mtdRet .= $randIndex[$cnt];

        return $mtdRet;
    }

    protected function _initSpecific01($queryResArr) {
        $this->set_passwd($queryResArr[$this->COL_NAMES[Member::COL_PASSWD]]);

        $this->set_attrib($queryResArr[$this->COL_NAMES[Member::COL_ATTRIB]]);
    }

    protected function _storeSpecific01(&$valuesMap): bool {
        $strTmp = $this->get_passwd();

        if ($strTmp == NULL) {
            $strTmp = $this->_genPasswd();

            $this->set_passwd($strTmp);
        }

        $valuesMap[$this->COL_NAMES[Member::COL_PASSWD]] = $strTmp;

        $strTmp = $this->get_attrib();

        if ($strTmp != NULL) {
            $valuesMap[$this->COL_NAMES[Member::COL_ATTRIB]] = $strTmp;
        }

        return TRUE;
    }

    static public function socialSecExists($socsec, $objChurch): bool {
        $theMember = new Member();

        $theMember->set_church($objChurch);

        $theMember->set_socialsec($socsec);

        return ($theMember->get_name() != NULL);
    }

    static public function emailExists($email, $objChurch): bool {
        $theMember = new Member();

        $theMember->set_church($objChurch);

        $theMember->set_email($email);

        return ($theMember->get_name() != NULL);
    }

    static public function telExists($tel, $objChurch): bool {
        $theMember = new Member();

        $theMember->set_church($objChurch);

        $theMember->set_tel($tel);

        return ($theMember->get_name() != NULL);
    }

    /**
     * _passwd
     * @return string
     */
    public function get_passwd(){
        return $this->_passwd;
    }

    /**
     * _passwd
     * @param string $_passwd
     * @return Member
     */
    public function set_passwd($_passwd){
        $this->_passwd = $_passwd;
        return $this;
    }

    /**
     * attrib
     * @return int
     */
    public function get_attrib(): int {
        return $this->_attrib;
    }

    /**
     * _attrib
     * @param int $attrib
     * @return Member
     */
    public function set_attrib($attrib){
        $this->_attrib = $attrib;
        return $this;
    }

    public function isAdm() {
        return ($this->get_attrib() & Member::ATTR_ADM) == Member::ATTR_ADM;
    }
}
