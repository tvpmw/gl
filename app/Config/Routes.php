<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login-sso', 'Home::loginSSO');
$routes->get('login/with-sso', 'Home::loginWithSSO');
$routes->get('logout', 'Home::logout');

$routes->group('cms', ['filter' => 'auth'], function ($routes) {
	$routes->group("dashboard", function ($routes) {
		$routes->get('/', 'DashboardController::index');
		$routes->post('get-data', 'DashboardController::getData');
	});

	$routes->group("report", function ($routes) {
		$routes->get('labarugi/(:any)/(:any)/(:any)', 'ReportController::labaRugi/$1/$2/$3');
	});

	$routes->get('accounts', 'AccountController::index');
});