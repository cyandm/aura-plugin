<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Cyan\PortalImporter\ProductImporter;

$importer = new ProductImporter();
$count = $importer->getCount();
$percentage = $importer->getPercentage();
$page = $importer->getPage();

?>

<div class="wrap">
	<h1>درون ریزی محصولات</h1>

	<h2>تعداد محصولات و درصد افزایش قیمت را وارد کنید و سپس فرآیند درون ریزی را آغاز کنید.</h2>

	<form method="post"
		  action=""
		  hx-post="<?php echo rest_url( 'product-importer/v1/import' ); ?>"
		  hx-target="#result"
		  hx-on::before-request="console.log('request is running');"
		  hx-on::after-request="console.log('request is done');">
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
						<label for="percentage">درصد افزایش قیمت:</label>
					</th>
					<td>
						<input type="number"
							   id="percentage"
							   name="percentage"
							   value="<?php echo esc_attr( $percentage ); ?>"
							   min="0"
							   max="100"
							   step="5"
							   class="small-text">
						% درصد
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
				   name="import_products"
				   class="button button-primary"
				   value="شروع درون ریزی">
		</p>
	</form>

	<pre dir="ltr"
		 id="result"></pre>

</div>