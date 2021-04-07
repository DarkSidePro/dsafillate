<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <DARK SIDE TEAM> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Poul-Henning Kamp
 * ----------------------------------------------------------------------------
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
