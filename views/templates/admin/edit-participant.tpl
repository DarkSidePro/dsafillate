{*
* 2007-2019 Dark-Side.pro
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
* needs please refer to http://www.dark-side.pro for more information.
*
*  @author    Dark-Side.pro <contact@dark-side.pro>
*  @copyright 2007-2019 Dark-Side.pro
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel">
	<h3><i class="icon icon-truck"></i> {l s='DS: Afillate - edit participmant paid' mod='dsafillate'}</h3>
	<div class='panel-body'>
		<div class='container'>
			<div class='row'>
				<div class='col-lg-12'>
					<form id='editParticipant' class='form-horizontal' method='POST'>
						<input type='hidden' name='participant_paid_update' value='{$data[0].id}'>
                        <div class='form-group'>
                            <label for='paid'>{l s='Paid' mod='dsafillate'}</label>
                            <input type='number' class='form-control' name='paid' required value='{$data[0].paid|string_format:"%.2f"}'> 
                        </div>
                        <div class='form-group'>
                            <label for='owed'>{l s='Owed' mod='dsafillate'}</label>
                            <input type='number' class='form-control' name='owed' value='{$data[0].owed|string_format:"%.2f"}' disabled>
                        </div>
                        <a href='{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure={$namemodules|strip_tags:"UTF-8"|escape:"htmlall":"UTF-8"}' class='pull-left btn btn-default'>{l s='Back' mod='dsdeliveryhours'}</a>                    
						<button type='submit' class='pull-right btn btn-default'>{l s='Save' mod='dsafillate'}</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

