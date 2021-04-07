<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <DARK SIDE TEAM> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Poul-Henning Kamp
 * ----------------------------------------------------------------------------
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Dsafillate extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'dsafillate';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Dark-Side.pro';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('DS: Afillate');
        $this->description = $this->l('This moduel add afilate program to your store');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include dirname(__FILE__).'/sql/install.php';
        $this->createTab();
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('ModuleRoutes') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        $this->tabRem();
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {   
        if (((bool) Tools::isSubmit('submitAfillateModule')) == true) {
            $this->postProcess();
        }

        if (Tools::isSubmit('participant_paid_update') == true) {
            $participant_id = Tools::getValue('participant_paid_update');
            $paid = Tools::getValue('paid');

            $this->updateParticipantPaid($participant_id, $paid);

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure=dsafillate');
        }

        if (Tools::isSubmit('editParticipant') == true) {
            $participant_id = Tools::getValue('editParticipant');
            $data = $this->getParticipantOwed($participant_id);

            $this->context->smarty->assign('data', $data);            
            $this->context->smarty->assign('namemodules', $this->name);
            $this->context->smarty->assign('link', $this->context->link);

            $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/edit-participant.tpl');

            return $output;
        }

        $token = Tools::getAdminTokenLite('AdministratorDsafillate');        

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('members', $this->getMembers());
        $this->context->smarty->assign('participants', $this->getParticipants());
        $this->context->smarty->assign('participantsinfo', $this->getParticipantsInfo());
        $this->context->smarty->assign('token', $token);
        $this->context->smarty->assign('participantCodes', $this->getParticipantsInfoByCode());
        $this->context->smarty->assign('participantsOrders' , $this->getParticipantInfoByDays("0000-00-00", "NOW()"));
        $this->context->smarty->assign('namemodules', $this->name);
        $this->context->smarty->assign('link', $this->context->link);

        $this->participantCalculations();

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {        
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    protected function getMembers()
    {
       $query = "SELECT id_customer, firstname, lastname 
       FROM "._DB_PREFIX_ ."customer
       WHERE passwd != 'allegro_user' AND NOT EXISTS (SELECT * FROM "._DB_PREFIX_."participant WHERE "._DB_PREFIX_."participant.id_member = "._DB_PREFIX_."customer.id_customer)";
       $data = Db::getInstance()->ExecuteS($query);

       return $data;
    }

    protected function getParticipants()
    {
        $sql = new DbQuery();
        $sql->select('p.id_member, c.firstname, c.lastname')
            ->from('participant', 'p')
            ->leftJoin('customer', 'c', 'p.id_member = c.id_customer');

        return DB::getInstance()->executeS($sql);
    }

    protected function getParticipantOwed($id)
    {
        $id = (int) $id;
        $sql = new DbQuery();
        $sql->select('p.paid, p.owed, p.id')
            ->from('participant_total', 'p')
            ->where('p.id = '.$id);
        
        return DB::getInstance()->executeS($sql);
    }

    protected function updateParticipantPaid($id, $paid)
    {
        $id = (int) $id;
        $sql = 'UPDATE '._DB_PREFIX_.'participant_total SET paid = '.$paid.' WHERE id = '.$id;

        DB::getInstance()->execute($sql);
    }

    protected function getParticipantsInfo()
    {
        $sql = new DbQuery();
        $sql->select('p.*, c.firstname, c.lastname')
            ->from('participant_total', 'p')
            ->leftJoin('customer', 'c', 'p.id_member = c.id_customer');

        return DB::getInstance()->executeS($sql);
    }

    public function hookDisplayCustomerAccount()
    {   
        if ($this->context->customer->isLogged()) {

            if (isset($this->isParticipant(Context::getContext()->customer->id)[0])) {
                return $this->display(__FILE__, 'displayCustomerAccount.tpl');
            }
        }

    }

    protected function isParticipant($customer_id)
    {
        $sql = new DbQuery();
        $sql->select('id')
            ->from('participant')
            ->where('id_member ='.$customer_id);
        
        return DB::getInstance()->executeS($sql);
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-dsafillate-rabats' => array(
                'controller' => 'rabats',
                'rule' => 'rabats',
                'keywords' => array(),
                'params' => array(
                    'module' => 'dsafillate',
                    'fc' => 'module',
                )
            )
        );
    }

    public function hookActionValidateOrder($params)
    {
        $this->participantCalculations();

    }

    protected function participantCalculations()
    {
        $sql = 'INSERT INTO '._DB_PREFIX_.'rabattest(id_cart_rule, description, id_cart, id_product, name, quantity, price, iso_code, rabat, date_add) SELECT id_cart_rule, description, id_cart, id_product, name, quantity, price, iso_code, rabat, date_add FROM ( SELECT CCR.id_cart_rule, CR.description ,CCR.id_cart, CP.id_product ,PL.name ,CP.quantity, P.price ,CUR.iso_code, ((CR.reduction_amount * CP.quantity) + (P.price * CR.reduction_percent * 0.01 * CP.quantity)) AS rabat, C.date_add FROM '._DB_PREFIX_.'cart_cart_rule AS CCR JOIN '._DB_PREFIX_.'cart_product AS CP ON CCR.id_cart = CP.id_cart JOIN '._DB_PREFIX_.'cart_rule AS CR ON CR.id_cart_rule = CCR.id_cart_rule JOIN '._DB_PREFIX_.'cart AS C ON C.id_cart = CCR.id_cart JOIN '._DB_PREFIX_.'currency AS CUR ON CUR.id_currency = C.id_currency JOIN '._DB_PREFIX_.'product AS P ON P.id_product = CP.id_product JOIN '._DB_PREFIX_.'manufacturer AS M ON M.id_manufacturer = P.id_manufacturer JOIN '._DB_PREFIX_.'product_lang AS PL ON PL.id_product = P.id_product WHERE M.name = "NAVIGATOR take the course to health" GROUP BY CCR.id_cart_rule,CCR.id_cart, CP.id_product) AS t1 WHERE NOT EXISTS ( SELECT id_cart_rule, description, id_cart, id_product, name, quantity, price, iso_code, rabat, date_add FROM '._DB_PREFIX_.'rabattest AS r WHERE t1.id_cart_rule = r.id_cart_rule and t1.id_cart = r.id_cart and t1.id_product = r.id_product )';

        Db::getInstance()->execute($sql);
        
        $sql = 'INSERT INTO '._DB_PREFIX_.'participant_total(id_member, running_total) SELECT t1.id_member, t1.running_total FROM ( SELECT pr.id_member, pr.id_cart_rule, Sum(r.rabat) AS running_total FROM '._DB_PREFIX_.'participant_rabat AS pr JOIN '._DB_PREFIX_.'rabattest AS r ON pr.id_cart_rule = r.id_cart_rule GROUP BY pr.id_member) AS t1 WHERE NOT EXISTS ( SELECT id_member FROM '._DB_PREFIX_.'participant_total AS pt WHERE t1.id_member = pt.id_member)';
        
        Db::getInstance()->execute($sql);

        $sql = 'UPDATE '._DB_PREFIX_.'participant_total AS pt LEFT JOIN( SELECT t2.id_member, t2.running_total, Sum(t2.number_of_rabats_created + t3.id_cart_number) AS number_of_rabats_created, t3.id_cart_number AS number_of_rabats_used, t4.total_discounts, t4.total_paid FROM ( SELECT id_member, Sum(running_total) AS running_total, Sum(quantity) AS number_of_rabats_created FROM ( SELECT pr.id_member, pr.id_cart_rule, Sum(r.rabat) AS running_total, cr.quantity FROM '._DB_PREFIX_.'participant_rabat AS pr JOIN '._DB_PREFIX_.'rabattest AS r ON pr.id_cart_rule = r.id_cart_rule JOIN '._DB_PREFIX_.'cart_rule AS cr ON cr.id_cart_rule = pr.id_cart_rule GROUP BY pr.id_member, pr.id_cart_rule) AS t1 GROUP BY id_member) AS t2 JOIN (SELECT id_member, Sum(id_cart_number) AS id_cart_number FROM (SELECT pr.id_member, pr.id_cart_rule, Count(ccr.id_cart) AS id_cart_number FROM '._DB_PREFIX_.'participant_rabat AS pr JOIN '._DB_PREFIX_.'cart_cart_rule AS ccr ON ccr.id_cart_rule = pr.id_cart_rule GROUP BY pr.id_member, pr.id_cart_rule) AS tt1 GROUP BY id_member) AS t3 ON t3.id_member = t2.id_member JOIN (SELECT pr.id_member, pr.id_cart_rule, Sum(o.total_discounts) AS total_discounts, Sum(o.total_paid) AS total_paid FROM '._DB_PREFIX_.'participant_rabat AS pr JOIN '._DB_PREFIX_.'order_cart_rule AS ocr ON pr.id_cart_rule = ocr.id_cart_rule JOIN '._DB_PREFIX_.'orders AS o ON ocr.id_order = o.id_order GROUP BY pr.id_member) AS t4 ON t4.id_member = t2.id_member GROUP BY id_member) AS t ON t.id_member = pt.id_member SET pt.running_total = t.running_total, pt.owed = ( t.running_total - pt.paid), pt.number_of_rabats_used = t.number_of_rabats_used, pt.number_of_rabats_created = t.number_of_rabats_created, pt.total_rabat_values = t.total_discounts, pt.total_orders_values = t.total_paid';

        Db::getInstance()->execute($sql);

        $sql = 'INSERT INTO '._DB_PREFIX_.'participant_total_per_code(id_cart_rule, description, id_member, owed, total_rabat_values, total_orders_values) SELECT id_cart_rule, description, id_member, owed, total_rabat_values, total_orders_values FROM ( SELECT DISTINCT CCR.id_cart_rule, CR.description, PR.id_member, t1.owed, t2.total_rabat_values, t2.total_orders_values FROM '._DB_PREFIX_.'cart_cart_rule AS CCR JOIN '._DB_PREFIX_.'cart_rule AS CR ON CR.id_cart_rule = CCR.id_cart_rule JOIN '._DB_PREFIX_.'participant_rabat AS PR ON PR.id_cart_rule = CCR.id_cart_rule JOIN ( SELECT SUM(R.rabat) AS owed, R.id_cart_rule FROM '._DB_PREFIX_.'rabattest AS R GROUP BY R.id_cart_rule) AS t1 ON t1.id_cart_rule = CCR.id_cart_rule JOIN ( SELECT OCR.id_cart_rule,SUM(O.total_discounts) AS total_rabat_values, SUM(O.total_paid) AS total_orders_values FROM '._DB_PREFIX_.'order_cart_rule AS OCR JOIN '._DB_PREFIX_.'orders AS O ON OCR.id_order = O.id_order GROUP BY OCR.id_cart_rule ) AS t2 ON t2.id_cart_rule = CCR.id_cart_rule ) AS t3 WHERE NOT EXISTS ( SELECT id_cart_rule FROM '._DB_PREFIX_.'participant_total_per_code AS PTPC WHERE t3.id_cart_rule = PTPC.id_cart_rule )';

        Db::getInstance()->execute($sql);

        $sql = 'UPDATE '._DB_PREFIX_.'participant_total_per_code AS PTPC LEFT JOIN( SELECT id_cart_rule, description, id_member, owed, total_rabat_values, total_orders_values FROM ( SELECT DISTINCT CCR.id_cart_rule, CR.description, PR.id_member, t1.owed, t2.total_rabat_values, t2.total_orders_values FROM '._DB_PREFIX_.'cart_cart_rule AS CCR JOIN '._DB_PREFIX_.'cart_rule AS CR ON CR.id_cart_rule = CCR.id_cart_rule JOIN '._DB_PREFIX_.'participant_rabat AS PR ON PR.id_cart_rule = CCR.id_cart_rule JOIN ( SELECTSUM(R.rabat) AS owed, R.id_cart_rule FROM '._DB_PREFIX_.'rabattest AS R GROUP BY R.id_cart_rule) AS t1 ON t1.id_cart_rule = CCR.id_cart_rule JOIN ( SELECT OCR.id_cart_rule,SUM(O.total_discounts) AS total_rabat_values, SUM(O.total_paid) AS total_orders_values FROM '._DB_PREFIX_.'order_cart_rule AS OCR JOIN '._DB_PREFIX_.'orders AS O ON OCR.id_order = O.id_order GROUP BY OCR.id_cart_rule ) AS t2 ON t2.id_cart_rule = CCR.id_cart_rule ) AS t3 ) AS t ON t.id_cart_rule = PTPC.id_cart_rule SET PTPC.owed = t.owed, PTPC.total_rabat_values = t.total_rabat_values, PTPC.total_orders_values = t.total_orders_values';

        Db::getInstance()->execute($sql);
    }    

    private function createTab()
    {
        $response = true;
        $parentTabID = Tab::getIdFromClassName('AdminDarkSideMenu');
        if ($parentTabID) {
            $parentTab = new Tab($parentTabID);
        } else {
            $parentTab = new Tab();
            $parentTab->active = 1;
            $parentTab->name = array();
            $parentTab->class_name = 'AdminDarkSideMenu';
            foreach (Language::getLanguages() as $lang) {
                $parentTab->name[$lang['id_lang']] = 'Dark-Side.pro';
            }
            $parentTab->id_parent = 0;
            $parentTab->module = '';
            $response &= $parentTab->add();
        }
        $parentTab_2ID = Tab::getIdFromClassName('AdminDarkSideMenuSecond');
        if ($parentTab_2ID) {
            $parentTab_2 = new Tab($parentTab_2ID);
        } else {
            $parentTab_2 = new Tab();
            $parentTab_2->active = 1;
            $parentTab_2->name = array();
            $parentTab_2->class_name = 'AdminDarkSideMenuSecond';
            foreach (Language::getLanguages() as $lang) {
                $parentTab_2->name[$lang['id_lang']] = 'Dark-Side Config';
            }
            $parentTab_2->id_parent = $parentTab->id;
            $parentTab_2->module = '';
            $response &= $parentTab_2->add();
        }
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdministratorDsafillate';
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'DS: Afillate';
        }
        $tab->id_parent = $parentTab_2->id;
        $tab->module = $this->name;
        $response &= $tab->add();

        return $response;
    }

    private function tabRem()
    {
        $id_tab = Tab::getIdFromClassName('AdministratorDsafillate');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }
        $parentTab_2ID = Tab::getIdFromClassName('AdminDarkSideMenuSecond');
        if ($parentTab_2ID) {
            $tabCount_2 = Tab::getNbTabs($parentTab_2ID);
            if ($tabCount_2 == 0) {
                $parentTab_2 = new Tab($parentTab_2ID);
                $parentTab_2->delete();
            }
        }
        $parentTabID = Tab::getIdFromClassName('AdminDarkSideMenu');
        if ($parentTabID) {
            $tabCount = Tab::getNbTabs($parentTabID);
            if ($tabCount == 0) {
                $parentTab = new Tab($parentTabID);
                $parentTab->delete();
            }
        }

        return true;
    }

        /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAfillateModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a max rabat value'),
                        'name' => 'MAX_RABAT_VALUE',
                        'label' => $this->l('Rabat value'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'MAX_RABAT_VALUE' => Configuration::get('MAX_RABAT_VALUE', 10),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        return $this->displayConfirmation($this->trans('Settings updated.', array(), 'Admin.DSAfillate.Success'));
    }

    protected function getParticipantInfoByDays($dateFrom) {
        $dateFrom = date('Y-m-d H:i:m', strtotime($dateFrom));        

        $sql = 'SELECT OCR.id_cart_rule,SUM(O.total_discounts) AS total_rabat_values, SUM(O.total_paid) AS total_orders_values, PR.id_member, CR.description, SUM((CR.reduction_amount * CP.quantity) + (P.price * CR.reduction_percent * 0.01 * CP.quantity)) AS owed, c.firstname, c.lastname, O.date_add
        FROM '._DB_PREFIX_.'order_cart_rule AS OCR
        JOIN '._DB_PREFIX_.'orders AS O ON OCR.id_order = O.id_order
        JOIN '._DB_PREFIX_.'participant_rabat AS PR ON PR.id_cart_rule = OCR.id_cart_rule
        JOIN '._DB_PREFIX_.'cart_rule AS CR ON CR.id_cart_rule = OCR.id_cart_rule
        JOIN '._DB_PREFIX_.'cart_product AS CP ON CP.id_cart = O.id_cart
        JOIN '._DB_PREFIX_.'product AS P ON P.id_product = CP.id_product
        LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = PR.id_member
        WHERE O.date_add BETWEEN  "'.$dateFrom.'" AND NOW()
        GROUP BY OCR.id_cart_rule';
                
        return Db::getInstance()->executeS($sql);
    }

    protected function getParticipantsInfoByCode()
    {
        $sql = 'SELECT p.*, c.firstname, c.lastname 
        FROM '._DB_PREFIX_.'participant_total_per_code as p 
        LEFT JOIN '._DB_PREFIX_.'customer as c ON c.id_customer = p.id_member';

        return Db::getInstance()->executeS($sql);
    }
}
