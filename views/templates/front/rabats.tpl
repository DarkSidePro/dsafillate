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
{extends file='index.tpl'}
{block name="content_wrapper"}
    <style>
        .cst-number::-webkit-outer-spin-button,
        .cst-number::-webkit-inner-spin-button {
            -webkit-appearance: auto !important;
            margin: 0;
        }
        .cst-number {
            -webkit-appearance: auto;
            appearance: auto;
        }
    </style>
    <div class='container-fluid'>
        <div class='row'>
            <div class='col-lg-3 panel'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>{l s="Create new promo code" mod='dsafillate'}</h2>
                </div>
                <div class='pane-body'>
                    <form method='POST' id='newCode'>
                        <input type='hidden' name='newCode'>
                        <div class='form-group'>
                            <label>{l s='Acitve from:' mod='dsafillate'}</label>
                            <input type='text' class='datepicker-1 form-control' name='dateStart' required autocomplete='off'>
                        </div>
                        <div class='form-group'>
                            <label>{l s='Acitve to:' mod='dsafillate'}</label>
                            <input type='text' class='datepicker-2 form-control' name='dateStop' required autocomplete='off'>
                        </div>
                        <div class='form-group'>
                            <label>{l s='Promo code' mod='dsafillate'}</label>
                            <input type='text' minlength='5' maxlength='254' class='form-control' name='promoCode' id='promoCode' required autocomplete='off'>
                            <button id='generate' class='btn'>{l s='Generate' mod='dsafillate'}</button>
                        </div>
                        <div class='form-group'>
                            <label>{l s='Reduction in percent' mod='dsafillate'}</label>
                            <input type="number" min='0.01' max='10.0' class='form-control cst-number' name='reduction' step='0.01' required max='{$maxRabatValue}'/>
                            <small>{l s='Max rabat value is:' mod='dsafillate'}{$maxRabatValue} %</small>
                        </div>
                        <button type='submit' class='btn btn-success'>{l s='Save' mod='dsafillate'}</button>
                    </form>
                </div>
            </div>
            <div class='col-lg-9 panel'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>{l s="Your promo code's" mod='dsafillate'}</h2>
                </div>
                <div class='panel-body'>
                    <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>#</th>
                                <td>{l s='Date from' mod='dsafillate'}</td>
                                <td>{l s='Date to' mod='dsafillate'}</td>
                                <td>{l s='Code' mod='dsafillate'}</td>
                                <td>{l s='Reduction' mod='dsafillate'}</td>
                                <td>{l s='Used' mod='dsafillate'}</td>
                                <td>{l s='Created' mod='dsafillate'}</td>
                                <td>{l s='Updated' mod='dsafillate'}</td>
                                <td>{l s='Status' mod='dsafillate'}</td>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $rabats as $rabat}
                                <tr>
                                    <th>{$rabat.id_cart_rule}</th>
                                    <td>{$rabat.date_from|date_format:"%D"}</td>
                                    <td>{$rabat.date_to|date_format:"%D"}</td>
                                    <td>{$rabat.code}</td>
                                    <td>{$rabat.reduction_percent}</td>
                                    <td></td>
                                    <td>{$rabat.date_add|date_format:"%D"}</td>
                                    <td>{$rabat.date_upd|date_format:"%D"}</td>
                                    <td>
                                        {if $rabat.active == 0} 
                                            {l s='Disabled' mod='dsafillate'} 
                                        {else} 
                                            {l s='Active' mod='dsafillate'} 
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>    
                    </table>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='col-lg-12'>
                <table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{l s='Running total' mod='dsafillate'}</th>
                            <th>{l s='Paid' mod='dsafillate'}</th>
                            <th>{l s='Owed' mod='dsafillate'}</th>
                            <th>{l s='Number of rabats used' mod='dsafillate'}</th>
                            <th>{l s='Number of rabats created' mod='dsafillate'}</th>
                            <th>{l s='Total rabat values' mod='dsafillate'}</th>
                            <th>{l s='Total orders values' mod='dsafillate'}</th>
                        </tr>
                    </thead>
                    <tbody> 
                        {foreach $participantInfo as $info}
                            <tr>
                                <th>{$info.id_member}</th>
                                <td>{Tools::displayPrice($info.running_total)}</td>
                                <td>{Tools::displayPrice($info.paid)}</td>
                                <td>{Tools::displayPrice($info.owed)}</td>
                                <td>{$info.number_of_rabats_used}</td>
                                <td>{$info.number_of_rabats_created}</td>
                                <td>{Tools::displayPrice($info.total_rabat_values)}</td>
                                <td>{Tools::displayPrice($info.total_orders_values)}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script>
    var url= '{url entity='module' name='dsafillate' controller='Newrabat' params = [action => 'NewrabatAction']}';
</script>
{literal} 
    <script defer>
        function makeid(length) {
            var result           = '';
            var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var charactersLength = characters.length;
            for ( var i = 0; i < length; i++ ) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }

        function checkJQuery()
        {
            if (!window.jQuery) {
                setTimeout(250);
                console.log('check');
                checkJQuery();
            } else {
                $('#promoCode').on('keypress', function (event) {
                var regex = new RegExp("^[a-zA-Z0-9]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                    if (!regex.test(key)) {
                        event.preventDefault();
                        return false;
                    }
                });

                $("input.datepicker-1").datepicker({ minDate: 0 });
                $("input.datepicker-2").datepicker({ minDate: +1 });

                $('#promoCode').on('keypress keydown blur change', () => {
                      $('#promoCode').val( $('#promoCode').val().toUpperCase() );
                })
            
                $('#generate').on('click', (e) => {
                    e.preventDefault();
                    $('#promoCode').val(makeid(5));
                })

                $('#newCode').submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(data) {
                            myObj = JSON.parse(JSON.stringify(data));
                            if (!isEmpty(myObj)) {
                                $('#newCode').append(myObj.msg);
                            } else {
                                location.reload();
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            alert(xhr.status);
                            alert(xhr.responseText);
                            alert(thrownError);
                        }
                    });
                });
            }
        }

        window.addEventListener('load', function () {
            checkJQuery();
        })     
    </script>
{/literal}
{/block}

