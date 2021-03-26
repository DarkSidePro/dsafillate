<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$sql = array();

$sql[] = 'CREATE TABLE `' . _DB_PREFIX_ . 'participant_total_per_code`
(
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `id_cart_rule`  INT(10),
    `description` text,
    `id_member` INT(10),
    `owed` decimal(20,6),
    `total_rabat_values` decimal(20,6),
    `total_orders_values` decimal(20,6)
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'rabattest`
(
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `id_cart_rule`  INT(10),
    `description` text,
    `id_cart` INT(10),
    `id_product` INT(10),
    `name`  varchar(128),
    `quantity` INT(10),
    `price`  decimal(20,6),
    `iso_code`  varchar(3),
    `rabat` decimal(20,6),
    `date_add` datetime,
    `total` decimal(20,6)
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'participant`
(
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `id_member` INT(10),
    `date_add` datetime
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'participant_rabat`
(
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `id_member` INT(10),
    `id_cart_rule` INT(10)
)';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'participant_total`
(
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `id_member` INT(10),
    `running_total`  decimal(20,6),
    `paid` decimal(20,6) NOT NULL DEFAULT 0.000000,
    `owed` decimal(20,6),
    `number_of_rabats_used` INT(10),
    `number_of_rabats_created` INT(10),
    `total_rabat_values`  decimal(20,6),
    `total_orders_values`  decimal(20,6)
)';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
