{*
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
*}
<style>
	.btn-cst {
		margin-top: 1rem;
	}
</style>

<div class='container-fluid'>
	<div class='row'>
		<div class='col-lg-5 panel'>
			<h2>{l s='Members list' mod='dsafillate'}</h2>
			<select id='memberList' class='form-control' multiple>
				{foreach $members as $member}
					<option value='{$member.id_customer}'>{$member.firstname} {$member.lastname}</option>
				{/foreach}
			</select>
			<button class='btn btn-success btn-cst' id='add'>{l s='Add' mod='dsafillate'}</button>
		</div>
		<div class='col-lg-2'></div>
		<div class='col-lg-5 panel'>
			<h2>{l s='Participant list' mod='dsafillate'}</h2>	
			<select id='participantList' class='form-control' multiple>
				{foreach $participants as $participant}
					{if $participant.id_member != 0}
						<option value='{$participant.id_member}'>{$participant.firstname} {$participant.lastname}</option>
					{/if}
				{/foreach}
			</select>
			<button class='btn btn-danger btn-cst' id='remove'>{l s='Remove' mod='dsafillate'}</button>
			<button class='btn btn-success btn-cst' id='save' data-token='{$token}'>{l s='Save participant list' mod='dsafillate'}</button>
		</div>
	</div>
	<div class='row'>
		<div class='col-lg-12 panel'>
			<h2>{l s='Participant totals' mod='dsafillate'}</h2>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th>#</th>
						<th>{l s='Participant name' mod='dsafillate'}</th>
						<th>{l s='Number of rabats' mod='dsafillate'}</th>
						<th>{l s='Total used rabats' mod='dsafillate'}</th>
						<th>{l s='Total rabat value' mod='dsafillate'}</th>
						<th>{l s='Total orders value' mod='dsafillate'}</th>
						<th>{l s='Paid' mod='dsafillate'}</th>
						<th>{l s='Owed' mod='dsafillate'}</th>
						<th>{l s='Action' mod='dsafillate'}</th>
					</tr>
				</thead>
				<tbody>	
					{foreach $participantsinfo as $info}
						<tr>
							<th>{$info.id}</th>
							<td>{$info.firstname} {$info.lastname}</td>
							<td>{$info.number_of_rabats_created}</td>
							<td>{$info.number_of_rabats_used}</td>
							<td>{Tools::displayPrice($info.total_rabat_values)}</td>
							<td>{Tools::displayPrice($info.total_orders_values)}</td>
							<td>{Tools::displayPrice($info.paid)}</td>
							<td>{Tools::displayPrice($info.owed)}</td>	
							<td>
								<a href="{$link->getAdminLink('AdminModules')|escape:'htmlall':'UTF-8'}&configure={$namemodules}&editParticipant={$info.id}" title="{l s='Edit' mod='dsafillate'}" class="details btn btn-default">
										<i class="icon-edit"></i> {l s='Edit' mod='dsafillate'}
								</a>
							</td>						
						</tr>
					{/foreach}				
				</tbody>
			</table>
		</div>
	</div>
	<div class='row'>
		<div class='col-lg-12 panel'>
			<h2>{l s='Participant info by code' mod='dsafillate'}</h2>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th>#</th>
						<th>{l s='Participant name' mod='dsafillate'}</th>
						<th>{l s='Code' mod='dsafillate'}</th>
						<th>{l s='Owed' mod='dsafillate'}</th>
						<th>{l s='Total rabat value' mod='dsafillate'}</th>
						<th>{l s='Total orders value' mod='dsafillate'}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $participantCodes as $code}
						<tr>
							<th>{$code.id}</th>
							<td>{$code.firstname} {$code.lastname}</td>
							<td>{$code.description}</td>
							<td>{Tools::displayPrice($code.owed)}</td>
							<td>{Tools::displayPrice($code.total_rabat_values)}</td>
							<td>{Tools::displayPrice($code.total_orders_values)}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<div class='row'>
		<div class='col-lg-12 panel'>
			<h2>{l s='Participant info by period of time' mod='dsafillate'}</h2>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th>#</th>
						<th>{l s='Participant name' mod='dsafillate'}</th>
						<th>{l s='Order date' mod='dsafillate'}</th>
						<th>{l s='Owed' mod='dsafillate'}</th>
						<th>{l s='Total rabat value' mod='dsafillate'}</th>
						<th>{l s='Total orders value' mod='dsafillate'}</th>
						<th>{l s='Code' mod='dsafillate'}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $participantsOrders as $order}
						<tr>
							<th>{$order.id_cart_rule}</th>
							<td>{$order.firstname} {$order.lastname}</td>
							<td>{$order.date_add}</td>
							<td>{Tools::displayPrice($order.owed)}</td>
							<td>{Tools::displayPrice($order.total_rabat_values)}</td>
							<td>{Tools::displayPrice($order.total_orders_values)}</td>
							<td>{$order.description}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	var url = '{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}';
</script>
{literal}
	<script>
		$().ready(function() {  
			$('#add').click(function() {  
				return !$('#memberList option:selected').remove().appendTo('#participantList');  
			});  
			$('#remove').click(function() {  
				return !$('#participantList option:selected').remove().appendTo('#memberList');  
			});
		});  
		$('#participantList').bind('DOMSubtreeModified', () => {
			var options = $('#participantList option');
			values = [0];
			var value = $.map(options, function(option) {
				values.push(option.value)
			});
		})

		$('#save').on('click', (e) => {
			e.preventDefault();
			let token = $('#save').data('token');
			$.ajax({
				type: 'POST',
				url: baseAdminDir+'index.php',
				data: {
					ajax: true,
					controller: 'AdministratorDsafillate',
					action: 'call',
					token: token,
					array: values,
					
				},
				success: function (data) {
					location.reload();
				},
				error: function (data) {
					console.log('An error occurred.');
					console.log(data);
				},
        	});
		})
	</script>
{/literal}