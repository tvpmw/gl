<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login-sso', 'Home::loginSSO');
$routes->get('login/with-sso', 'Home::loginWithSSO');
$routes->get('logout', 'Home::logout');
$routes->get('unauthorized', 'ErrorController::unauthorized');

$routes->group("api", function ($routes) {
	$routes->get('npwp/check', 'NpwpController::apiCheckSingle');
	$routes->get('npwp/check-bulk', 'NpwpController::apiCheckBulk');
});

$routes->group('cms', ['filter' => ['auth', 'menuAccess']], function ($routes) {    

	$routes->get('barang/search', 'BarangController::search');
    $routes->get('barang/tax-codes', 'BarangController::getTaxCodes');

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

	$routes->group("npwp", function ($routes) {
		$routes->get('/', 'NpwpController::index');
		$routes->post('check-single', 'NpwpController::checkSingle');
		$routes->post('check-bulk', 'NpwpController::checkBulk');
		$routes->post('check-nitku', 'NpwpController::checkNitku');		
	});	

	$routes->group('mapping-coretax', function($routes) {
		$routes->get('', 'MappingCoretaxController::index');
		$routes->post('data', 'MappingCoretaxController::getData');
		$routes->get('kode-tax', 'MappingCoretaxController::getKodeTax');
		$routes->post('save', 'MappingCoretaxController::save');
		$routes->post('delete', 'MappingCoretaxController::delete');
	});

	$routes->group("faktur", function ($routes) {
		$routes->get('/', 'FakturController::index');
		$routes->get('import', 'FakturController::import');
		$routes->post('get-detail', 'FakturController::getDetail');
		$routes->post('check-existing', 'FakturController::checkExisting');
		$routes->post('generate', 'FakturController::generate');
		$routes->post('get-data', 'FakturController::getData');
		$routes->post('tidak-dibuat', 'FakturController::tidakDibuat');
		$routes->get('generate_excel', 'FakturController::generate_excel');
		$routes->post('preview-import', 'FakturController::previewImport');
		$routes->post('save-import', 'FakturController::saveImport');		
	});

	$routes->group('tax-generate', function ($routes) {
		$routes->get('/', 'TaxGenerateCheckController::index');
		$routes->post('get-data', 'TaxGenerateCheckController::getData');
		$routes->post('get-detail', 'TaxGenerateCheckController::getDetail');
		$routes->post('batal-generate', 'TaxGenerateCheckController::batalGenerate');
		$routes->post('get-coretax-detail', 'TaxGenerateCheckController::getCoretaxDetail');
	});

	$routes->group('tax-retur', function ($routes) {
		$routes->get('/', 'TaxGenerateCheckController::retur');
		$routes->post('get-data', 'TaxGenerateCheckController::getDataRetur');
		$routes->post('sudah-lapor', 'TaxGenerateCheckController::sudahLapor');
	});

    $routes->group("user", function($routes){
		$routes->get('/','AdminController::index');
		$routes->get('privileges','AdminController::privileges');
		$routes->post('lists','AdminController::lists');
		$routes->post('save','AdminController::save');
		$routes->post('delete','AdminController::delete');
		$routes->post('savePrivileges','AdminController::savePrivileges');
		$routes->get('login','AdminController::userLogin');
		$routes->get('userAktif','AdminController::userAktif');
		$routes->get('getOnlineUsers','AdminController::getOnlineUsers');
		$routes->post('forceLogout','AdminController::forceLogout');
		
		// Update module access routes
        $routes->get('module-access/(:num)', 'AdminController::moduleAccess/$1');
        $routes->post('get-module-access', 'AdminController::getModuleAccess');
        $routes->post('save-module-access', 'AdminController::saveModuleAccess');
	});

	$routes->group('customer', function ($routes) {
		$routes->get('/', 'CustomerController::index');
		$routes->post('get-data', 'CustomerController::getData');
	});	
});