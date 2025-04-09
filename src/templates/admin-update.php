<?php

if (! defined('ABSPATH')) {
	exit;
}

use Cyan\PortalImporter\ProductUpdater;
use Cyan\PortalImporter\Stats;

$updater = new ProductUpdater();
$count = $updater->getCount();
$percentage = $updater->getPercentage();
$page = $updater->getPage();
$baseUrl = get_option(PLUGIN_NAME . '_base_url', 'https://mobomobo.ir');
//$totalProductsCount = get_option(PLUGIN_NAME . '_total_products_count_from_api');
$totalProductsCount = Stats::getTotalProductsCount($baseUrl);
$totalProductsCount = (int)$totalProductsCount;
?>


<div class="wrap">
	<h1>به روز رسانی محصولات</h1>

	<h2>برای به روز رسانی موجودی محصولات کلیک کنید</h2>

	<!-- <p style="color: red;">لطفا بعد از تعیین تعداد محصولات دکمه ذخیره تنظیمات رو بزنید تا تعداد صفحه بروز شود</p> -->

	<p style="color: red;">لطفا آدرس سایتی که نیاز دارید محصولات با آن سینک و آپدیت شود رو انتخاب کنید:</p>

	<form method="post"
		id="updateProductsForm"
		action=""
		data-total-products="<?php echo esc_attr($totalProductsCount); ?>">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label>انتخاب آدرس API:</label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio"
									name="base_url"
									value="https://mobomobo.ir"
									<?php checked($baseUrl, 'https://mobomobo.ir'); ?> checked>
								موبوموبو
							</label><br>
							<label style="opacity: 0.6; cursor: not-allowed;">
								<input type="radio"
									name="base_url"
									value="https://mobomobochap.ir"
									<?php //checked($baseUrl, 'https://mobomobochap.ir'); ?> disabled>
								موبوچاپ
							</label>
						</fieldset>
					</td>
				</tr>
				<th scope="row">
					<label for="count">تعداد محصولاتی که نیاز دارید بروزرسانی شود:</label>
				</th>
				<td>
					<input type="number"
						id="count"
						name="count"
						value="<?php echo esc_attr($count); ?>"
						min="1"
						class="small-text">
				</td>

				</tr>

				<tr>
					<th scope="row">
						<label for="percentage">مقدار افزایش قیمت:</label>
					</th>
					<td>
						<input type="number"
							id="percentage"
							name="percentage"
							value="<?php echo esc_attr($percentage); ?>"
							min="0"
							step="5"
							class="small-text">
						تومان
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="page">صفحه:</label>
					</th>
					<td>
						<input type="number"
							id="page"
							name="page"
							value="<?php echo esc_attr($page); ?>"
							min="1"
							class="small-text">
					</td>
				</tr>

				<input type="hidden"
					name="requestId"
					value="<?php echo UPDATE_TOKEN ?>">

			</tbody>
		</table>

		<!-- <p class="update">
			<input type="button"
				name="save"
				id="updater_save_settings"
				class="button button-secondary"
				value="ذخیره تنظیمات">
		</p> -->

		<p class="submit">
			<input type="submit"
				name="update_products"
				class="button button-primary"
				value="به روز رسانی موجودی">
		</p>

		<div class="log-container" style="margin-top: 20px;">
			<h3>گزارش عملیات:</h3>
			<textarea
				id="log-content"
				readonly
				style="width: 100%; height: 660px; direction: ltr; font-family: monospace; background: #f6f7f7;"></textarea>
		</div>
	</form>

	<div id="indicator" class="htmx-indicator">
		<span class="htmx-indicator-content">در حال به روز رسانی...</span>
	</div>

	<pre dir="ltr"
		id="result"
		style="
		white-space: pre-wrap;
		word-wrap: break-word;
		font-size: 12px;
		font-family: monospace !important;
		background-color: #fff;
		padding: 10px;
		border-radius: 5px;
		max-height: 500px;
		overflow-y: auto;
		border: 2px solid #000;
		display: none;
	 ">
</pre>

</div>


<style>
	.custom-notification {
		position: fixed;
		bottom: 60px;
		margin: 5% auto;
		left: 40%;
		right: 40%;
		background: #00a32a;
		color: white;
		padding: 20px;
		border-radius: 8px;
		font-family: IRANSans, Tahoma;
		font-size: 16px;
		text-align: center;
		direction: rtl;
		opacity: 0;
		transform: translateY(20px);
		transition: all 0.3s ease;
		z-index: 9999;
		box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
		display: flex;
		flex-direction: column;
		gap: 15px;
	}

	.notification-content {
		margin-bottom: 5px;
	}

	.notification-button {
		background: white;
		color: #00a32a;
		border: none;
		padding: 8px 20px;
		border-radius: 4px;
		cursor: pointer;
		font-family: IRANSans, Tahoma;
		font-size: 14px;
		transition: all 0.2s ease;
	}

	.notification-button:hover {
		background: #f0f0f0;
	}

	.custom-notification.show {
		opacity: 1;
		transform: translateY(0);
	}

	.notification-close {
		background: none;
		border: none;
		color: white;
		font-size: 24px;
		cursor: pointer;
		padding: 0 0 0 10px;
		margin-right: 10px;
		opacity: 0.8;
	}

	.notification-close:hover {
		opacity: 1;
	}

	.custom-notification {
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
</style>