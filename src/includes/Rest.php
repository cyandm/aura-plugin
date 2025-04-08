<?php

namespace Cyan\PortalImporter;

if (! defined('ABSPATH')) {
	exit;
}

class Rest
{
	public static function init()
	{
		add_action('rest_api_init', [__CLASS__, 'registerRoutes']);
	}

	public static function registerRoutes()
	{
		register_rest_route('product-importer/v1', '/import', [
			'methods' => 'POST',
			'callback' => [__CLASS__, 'importProducts'],
			'permission_callback' => '__return_true',
		]);

		register_rest_route('product-importer/v1', '/update', [
			'methods' => 'POST',
			'callback' => [__CLASS__, 'updateProducts'],
			'permission_callback' => '__return_true',
		]);

		register_rest_route('product-importer/v1', '/log/(?P<file>[a-zA-Z0-9_-]+)', [
			'methods' => 'GET',
			'callback' => [__CLASS__, 'getLog'],
			'permission_callback' => '__return_true',
		]);
	}

	public static function importProducts($request)
	{

		$count = $request->get_param('count');
		$percentage = $request->get_param('percentage');
		$page = $request->get_param('page');

		$totalProductsCount = Stats::getTotalProductsCount();

		$count = intval($count);
		$totalProductsCount = intval($totalProductsCount);

		$totalPages = ceil($totalProductsCount / $count);

		ProductImporter::init($count, $percentage, $page);

		return rest_ensure_response([
			'message' => 'Products imported successfully',
			'count' => $count,
			'percentage' => $percentage,
			'page' => $page,
			'status' => 'success',
			'totalPages' => $totalPages,
		]);
	}

	public static function updateProducts($request)
	{
		$count = $request->get_param('count');
		$percentage = $request->get_param('percentage');
		$page = $request->get_param('page');
		$requestId = $request->get_param('requestId');

		if ($requestId !== UPDATE_TOKEN) {
			return rest_ensure_response([
				'message' => 'Invalid request',
				'status' => 'error',
			]);
		}

		$totalProductsCount = Stats::getTotalProductsCount();
		$totalPages = ceil($totalProductsCount / $count);

		ProductUpdater::init($count, $percentage, $page);

		return rest_ensure_response([
			'message' => 'Products updated successfully',
			'count' => $count,
			'percentage' => $percentage,
			'page' => $page,
			'status' => 'success',
			'totalPages' => $totalPages,
		]);
	}

	public static function getLog($request)
	{
		$file = $request->get_param('file');
		$filePath = PLUGIN_DIR . '/logs/' . $file . '.log';

		if (! file_exists($filePath)) {
			return rest_ensure_response([
				'log' => 'File not found',
			]);
		}

		// Read entire file content
		$logContent = file_get_contents($filePath);
		$logLines = explode("\n", $logContent);

		$logHtml = '';
		foreach ($logLines as $line) {
			if (!empty(trim($line))) {
				$logHtml .= '<p>' . $line;
			}
		}
		return rest_ensure_response($logHtml);
	}

	// private static function tail($file, $lines = 10)
	// {
	// 	$handle = fopen($file, 'r');
	// 	if (! $handle)
	// 		return [];

	// 	$buffer = 4096; // Read buffer size
	// 	$output = '';
	// 	fseek($handle, 0, SEEK_END);
	// 	$position = ftell($handle);
	// 	$remaining_lines = $lines;

	// 	while ($remaining_lines > 0 && $position > 0) {
	// 		$read_size = ($position - $buffer > 0) ? $buffer : $position;
	// 		$position -= $read_size;
	// 		fseek($handle, $position);
	// 		$data = fread($handle, $read_size);

	// 		$output = $data . $output;
	// 		$remaining_lines -= substr_count($data, "\n");
	// 	}

	// 	fclose($handle);

	// 	return array_slice(explode("\n", trim($output)), -$lines);
	// }
}
