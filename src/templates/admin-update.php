<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Cyan\PortalImporter\ProductUpdater;

if ( isset( $_POST['import_products'] ) ) {
	ProductUpdater::init();
}

?>

<div class="wrap">
	<h1>به روز رسانی محصولات</h1>

	<h2>برای به روز رسانی موجودی محصولات کلیک کنید</h2>

	<form method="post"
		  action="">

		<p class="submit">
			<input type="submit"
				   name="import_products"
				   class="button button-primary"
				   value="به روز رسانی موجودی">
		</p>
	</form>

</div>