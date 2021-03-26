<?php
/**
* DS: Afillate
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    Dark-Side.pro
* @copyright Copyright 2017 © fmemodules All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FO Module
* @package   dsafillate
*/

class AdministratorDsafillateController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!Tools::isSubmit('array')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure=dsafillate');
        }
    }

    public function ajaxProcessCall()
    {
        $participants = Tools::getValue('array');
        $this->addParticipant($participants);
    }

    protected function addParticipant($array)
    {   $this->deleteParticipants();
        $db = \Db::getInstance();
        foreach ($array as $participant) {
            $sql = "INSERT INTO `" . _DB_PREFIX_ . "participant` (`id_member`, `date_add`) VALUES ($participant, NOW())";
            $db->execute($sql);
        }
    }

    protected function deleteParticipants()
    {
        $db = \Db::getInstance();
        $sql = "DELETE FROM "._DB_PREFIX_."participant";

        return DB::getInstance()->execute($sql);
    }
}
