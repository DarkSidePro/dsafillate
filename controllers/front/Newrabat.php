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

class DsafillateNewrabatModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $guestAllowed = false;
    public $auth = true;

    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();
    }

    public function postProcess()
    {
    }

    public function displayAjaxNewrabatAction()
    {
        if (Tools::isSubmit('newCode')) {
            $code = Tools::getValue('promoCode');
            $reduction = Tools::getValue('reduction');
            $dateFrom = Tools::getValue('dateStart');
            $dateTo = Tools::getValue('dateStop');
            $customer_id = (int) Context::getContext()->customer->id;

            $this->createRabat($customer_id, $dateFrom, $dateTo, $code, $reduction);
        }
    }

    protected function createRabat($customer_id, $dateFrom, $dateTo, $code, $reduction)
    {
        $customer_id = (int) $customer_id;
        $reduction = (float) $reduction;

        $maxReductionValue = Configuration::get('MAX_RABAT_VALUE');

        if ((int) $reduction > (int) $maxReductionValue) {
            $response = array('msg' => 'Reduction is to high');
            echo Tools::jsonEncode($response);
            die();
        }

        $dateFrom = date('Y-m-d H:i:m', strtotime($dateFrom));
        $dateTo = date('Y-m-d H:i:m', strtotime($dateTo));

        if ($this->isUniqueCode($code) == false) {
            $response = array('msg' => 'Code isnt unique');
            echo Tools::jsonEncode($response);
            die();
        }

        $sql = 'INSERT INTO '._DB_PREFIX_.'cart_rule (date_from, date_to, description, quantity, quantity_per_user, code, minimum_amount, minimum_amount_currency, product_restriction, cart_rule_restriction, reduction_percent, reduction_currency, reduction_product, highlight, active, date_add, date_upd)
        VALUES ("'.$dateFrom.'", "'.$dateTo.'", "'.$code.'", 1000, 1, "'.$code.'", 1.00, 1, 1, 1, "'.$reduction.'", 1, -2, 0, 1, NOW(), NOW())';

        Db::getInstance()->execute($sql);
        
        $id_cart_rule = Db::getInstance()->Insert_ID();        
        
        $sql = 'INSERT INTO '._DB_PREFIX_.'cart_rule_lang (id_cart_rule, id_lang, name)
        VALUES ('.$id_cart_rule.', 1, "'.$code.'")';

        Db::getInstance()->execute($sql);
        

       $sql = 'INSERT INTO '._DB_PREFIX_.'cart_rule_product_rule_group (id_cart_rule, quantity)
        VALUES ('.$id_cart_rule.', 1)';

        Db::getInstance()->execute($sql);

        $id_product_rule_group = Db::getInstance()->Insert_ID();

        $sql = 'INSERT INTO `'._DB_PREFIX_.'participant_rabat`(`id_member`, `id_cart_rule`) 
        VALUES ('.$customer_id.','.$id_cart_rule.')';

        Db::getInstance()->execute($sql);

        $sql = 'INSERT INTO '._DB_PREFIX_.'cart_rule_product_rule (id_product_rule_group, type) 
        VALUES ('.$id_product_rule_group.', "manufacturers")';

        Db::getInstance()->execute($sql);

        $id_product_rule = Db::getInstance()->Insert_ID();

        $sql = 'INSERT INTO '._DB_PREFIX_.'cart_rule_product_rule_value (id_product_rule, id_item) 
        VALUES ('.$id_product_rule.', 5)';

        Db::getInstance()->execute($sql);
    }

    protected function isUniqueCode($code)
    {
        $sql = 'SELECT id_cart_rule FROM '._DB_PREFIX_.'cart_rule WHERE code = "'.$code.'"';
        $result = Db::getInstance()->executeS($sql);

        if (!isset($result[0]['id_cart_rule'])) {
            return true;
        } else {
            return false;
        }
    }
}