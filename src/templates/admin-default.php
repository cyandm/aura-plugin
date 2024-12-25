<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Cyan\PortalImporter\ProductImporter;

if ( isset( $_POST['import_products'] ) ) {
	$count = intval( $_POST['count'] );
	$percentage = intval( $_POST['percentage'] );

	ProductImporter::init( $count, $percentage );
}

$importer = new ProductImporter();
$count = $importer->getCount();
$percentage = $importer->getPercentage();

?>

<div class="wrap">
	<h1>درون ریزی محصولات</h1>

	<h2>تعداد محصولات و درصد افزایش قیمت را وارد کنید و سپس فرآیند درون ریزی را آغاز کنید.</h2>

	<form method="post"
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
			</tbody>
		</table>

		<p class="submit">
			<input type="submit"
				   name="import_products"
				   class="button button-primary"
				   value="شروع درون ریزی">
		</p>
	</form>

</div>