<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Cyan\PortalImporter\Stats;

?>

<div class="wrap">
	<h1>آمار</h1>

	<table class="widefat striped">
		<thead>
			<tr>
				<th>نام</th>
				<th>مقدار</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>تعداد محصولات از سرور</td>
				<td><?php echo Stats::getTotalProductsCount(); ?></td>
			</tr>

			<tr>
				<td>تعداد محصولات در دیتابیس</td>
				<td><?php echo Stats::getTotalProductsCountFromDatabase(); ?></td>
			</tr>
		</tbody>
	</table>
</div>