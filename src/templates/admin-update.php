<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Cyan\PortalImporter\ProductUpdater;

$updater = new ProductUpdater();
$page = $updater->getPage();
$count = $updater->getCount();

?>


<div class="wrap">
	<h1>به روز رسانی محصولات</h1>

	<h2>برای به روز رسانی موجودی محصولات کلیک کنید</h2>

	<form method="post"
		  hx-indicator="#indicator"
		  hx-post="<?php echo rest_url( 'product-importer/v1/update' ); ?>"
		  hx-on::after-request="document.getElementById('result').style.display = 'block';"
		  action="">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="count">تعداد محصولاتی که نیاز دارید درون ریزی شود:</label>
					</th>
					<td>
						<input type="number"
							   id="count"
							   name="count"
							   value="<?php echo esc_attr( $count ); ?>"
							   min="1"
							   class="small-text">
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
							   value="<?php echo esc_attr( $page ); ?>"
							   min="1"
							   class="small-text">
					</td>
				</tr>


			</tbody>
		</table>

		<p class="submit">
			<input type="submit"
				   name="update_products"
				   class="button button-primary"
				   value="به روز رسانی موجودی">
		</p>
	</form>


	<div id="indicator"
		 class="htmx-indicator">
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