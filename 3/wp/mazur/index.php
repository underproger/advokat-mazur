<?php
/**
 * Fallback-шаблон.
 */
defined( 'ABSPATH' ) || exit;
if ( is_singular() ) {
	require __DIR__ . '/page.php';
	return;
}
require __DIR__ . '/404.php';
