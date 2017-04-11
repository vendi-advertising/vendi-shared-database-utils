<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Vendi_Health_Tradition_Click4rates
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';
