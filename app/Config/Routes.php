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
		$routes->get('get-akun', 'JurnalController::getAkun');
		$routes->post('get-kode', 'JurnalController::getKodeJurnal');
		$routes->post('save', 'JurnalController::save');
		$routes->get('edit', 'JurnalController::edit');
		$routes->post('delete', 'JurnalController::delete');
	});

	$routes->group("report", function ($routes) {
		$routes->get('labarugi/(:any)/(:any)/(:any)', 'ReportController::labaRugi/$1/$2/$3');
		$routes->get('labarugi-filter', 'ReportController::filterLabaRugi');
		$routes->get('labarugi-cetak/(:any)/(:any)/(:any)', 'ReportController::labaRugiCetak/$1/$2/$3');

		$routes->get('neraca/(:any)/(:any)/(:any)', 'ReportController::neraca/$1/$2/$3');
		$routes->get('neraca-filter', 'ReportController::filterNeraca');
		$routes->get('neraca-cetak/(:any)/(:any)/(:any)', 'ReportController::neracaCetak/$1/$2/$3');

		$routes->get('bukubesar/(:any)/(:any)/(:any)/(:any)', 'ReportController::bukuBesar/$1/$2/$3/$4');
		$routes->get('bukubesar-filter', 'ReportController::filterBukuBesar');
		$routes->post('bukubesar-filter', 'ReportController::resultBukuBesar');
		$routes->get('search-rekening', 'ReportController::searchRekening');
		$routes->get('bukubesar-filterket', 'ReportController::filterBukuBesarKet');
		$routes->post('bukubesar-filterket', 'ReportController::resultBukuBesarKet');
		$routes->get('jurnal-filter', 'ReportController::filterJurnal');
		$routes->post('jurnal-filter', 'ReportController::resultJurnal');
		$routes->get('perubahan-modal', 'ReportController::filterPerubahanModal');
		$routes->post('perubahan-modal', 'ReportController::resultPerubahanModal');
	});
});