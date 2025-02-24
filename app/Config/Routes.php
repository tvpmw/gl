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
		$routes->get('neraca', 'DashboardController::neraca');
	});

	$routes->group("report", function ($routes) {
		$routes->get('labarugi/(:any)/(:any)/(:any)', 'ReportController::labaRugi/$1/$2/$3');
		$routes->get('neraca/(:any)/(:any)/(:any)', 'ReportController::neraca/$1/$2/$3');
		$routes->get('bukubesar/(:any)/(:any)/(:any)/(:any)', 'ReportController::bukuBesar/$1/$2/$3/$4');
	});

	$routes->get('accounts', 'AccountController::index');
});