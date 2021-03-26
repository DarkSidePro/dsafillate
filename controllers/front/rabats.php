<?php
/**
* Advance Blog
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    Dark-Side.pro
* @copyright Copyright 2017 © Dark-Side.pro All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FO Module
* @package   dsafillate
*/

class DsafillateRabatsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (!Context::getContext()->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication&redirect=module&module='.$this->module->name.'&action=rabats');
        }

        $customer_id = (int) Context::getContext()->customer->id;

        if (!isset($this->isParticipant($customer_id)[0])) {
            Tools::redirect('index.php?controller=authentication');
        }
        
        
        $customerRabats = $this->getCustomerRabats($customer_id);
        $this->context->smarty->assign('rabats', $customerRabats);
        $this->context->smarty->assign('maxRabatValue', Configuration::get('MAX_RABAT_VALUE'));
        $this->context->smarty->assign('participantInfo', $this->getParticipantInfo($customer_id));
        $this->context->smarty->assign('id_module', $this->module->id);
        $this->context->smarty->assign('link', $this->context->link);
        $this->context->smarty->assign(array('lang_iso' => $this->context->language->iso_code));
        $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        $this->context->smarty->assign(array('base_dir' => _PS_BASE_URL_.__PS_BASE_URI__,
            'base_dir_ssl' => _PS_BASE_URL_SSL_.__PS_BASE_URI__,
            'force_ssl' => $force_ssl));
        $this->setTemplate('module:'.$this->module->name.'/views/templates/front/rabats.tpl');
    }

    protected function getCustomerRabats($customer_id)
    {
        $sql = new DbQuery();
        $sql->select('cr.date_from, cr.date_to, cr.code, cr.reduction_percent, cr.date_add, cr.date_upd, cr.active, cr.id_cart_rule')
            ->from('participant_rabat', 'pr')
            ->leftJoin('cart_rule', 'cr', 'cr.id_cart_rule = pr.id_cart_rule')
            ->where('pr.id_member ='.$customer_id);
        
        return DB::getInstance()->executeS($sql);
    }

    protected function isParticipant($customer_id)
    {
        $sql = new DbQuery();
        $sql->select('id')
            ->from('participant')
            ->where('id_member ='.$customer_id);
        
        return DB::getInstance()->executeS($sql);
    }

    protected function isParticipantRabat($customer_id, $id_cart_rule)
    {
        $sql = new DbQuery();
        $sql->select('id')
            ->from('participant_rabat')
            ->where('id_member = '.$customer_id.' AND id_cart_rule ='.$id_cart_rule );

        return DB::getInstance()->executeS($sql);
    }

    protected function changeRabatStatus($status, $customer_id, $id_cart_rule)
    {
        if ($this->isParticipantRabat($customer_id, $id_cart_rule)[0]['id'] != null) {
            $status = (int) $status;
            $sql = 'UPDATE '._DB_PREFIX_.'cart_rule SET active = '.$status.' WHERE id_cart_rule = '.$id_cart_rule;
            return DB::getInstance()->executeS($sql);
        }
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUi('ui.datepicker');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array('title' => 'My account',
            'url' => $this->context->link->getPageLink('my-account'),
        );
        $breadcrumb['links'][] = array('title' => 'Afillate panel',
            'url' => $this->context->link->getModuleLink($this->module->name, 'rabats', array(), true),
        );
        return $breadcrumb;
    }

    protected function getParticipantInfo($customer_id)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'participant_total WHERE id_member ='.$customer_id;
        return DB::getInstance()->executeS($sql);
    }
}
