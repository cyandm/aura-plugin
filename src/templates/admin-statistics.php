<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Cyan\PortalImporter\Stats;

?>

<div class="wrap">
    <h1>آمار محصولات</h1>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>منبع</th>
                <th>تعداد کل محصولات در API</th>
                <th>تعداد محصولات ایمپورت شده</th>
                <th>درصد پیشرفت</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $apis = [
                'mobomobo' => [
                    'name' => 'موبوموبو',
                    'url' => 'https://mobomobo.ir'
                ],
                'mobomobochap' => [
                    'name' => 'موبوچاپ',
                    'url' => 'https://mobomobochap.ir'
                ]
            ];

            foreach ($apis as $key => $api) {
                $totalInApi = Stats::getTotalProductsCount($api['url']);
                $importedCount = Stats::getTotalProductsCountFromDatabase($api['url']);
                $percentage = $totalInApi > 0 ? round(($importedCount / $totalInApi) * 100, 2) : 0;
            ?>
                <tr>
                    <td><?php echo $api['name']; ?></td>
                    <td><?php echo number_format($totalInApi); ?></td>
                    <td><?php echo number_format($importedCount); ?></td>
                    <td>
                        <div class="progress-bar" style="background: #f0f0f1; height: 20px; width: 100%; border-radius: 4px;">
                            <div style="background: #2271b1; height: 100%; width: <?php echo $percentage; ?>%; border-radius: 4px;">
                                <span style="padding: 0 5px; color: #000000; white-space: nowrap;"><?php echo $percentage; ?>%</span>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>