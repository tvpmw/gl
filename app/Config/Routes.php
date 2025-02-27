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
		$routes->get('coa', 'DashboardController::coa');
	});

	$routes->group("jurnal", function ($routes) {
		$routes->get('/', 'JurnalController::index');
		$routes->post('get-data', 'JurnalController::getData');
		$routes->post('detail', 'JurnalController::getDetail');
	});

	$routes->group("report", function ($routes) {
		$routes->get('labarugi/(:any)/(:any)/(:any)', 'ReportController::labaRugi/$1/$2/$3');
		$routes->get('labarugi-filter', 'ReportController::filterLabaRugi');

		$routes->get('neraca/(:any)/(:any)/(:any)', 'ReportController::neraca/$1/$2/$3');
		$routes->get('neraca-filter', 'ReportController::filterNeraca');

		$routes->get('bukubesar/(:any)/(:any)/(:any)/(:any)', 'ReportController::bukuBesar/$1/$2/$3/$4');
		$routes->get('bukubesar-filter', 'ReportController::filterBukuBesar');
		$routes->post('bukubesar-filter', 'ReportController::resultBukuBesar');
		$routes->get('search-rekening', 'ReportController::searchRekening');
		$routes->get('bukubesar-filterket', 'ReportController::filterBukuBesarKet');
		$routes->post('bukubesar-filterket', 'ReportController::resultBukuBesarKet');
	});
});