{*
* 2007-2016 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<div id="container-system_notification">
	<div class="row">
		{*left*}
		<div>
      <div class="panel clearfix">
				<div class="panel-heading">
					<i class="icon-envelope"></i>
					{($system_notification->short_desc)}
					[{$system_notification->id|string_format:"%06d"}]
				</div>
				<div class="form-horizontal">

					<div class="row">
						<label class="control-label col-lg-3"><strong>{l s='Notification:'}</strong></label>
						<div class="col-lg-9">
							<p class="form-control-static">{$system_notification->description}</p>
						</div>
					</div>

					<div class="row">
						<label class="control-label col-lg-3"><strong>{l s='Importance:'}</strong></label>
						<div class="col-lg-9">
							<p class="form-control-static">{$system_notification->importance}</p>
						</div>
					</div>
					<div class="row">
						<label class="control-label col-lg-3"><strong>{l s='Date added:'}</strong></label>
						<div class="col-lg-9">
							<p class="form-control-static">{$system_notification->date_add}</p>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>


</div>
{/block}
