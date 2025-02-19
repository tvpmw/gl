<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/dashboard', 'DashboardController::index');
$routes->post('/dashboard/get-data', 'DashboardController::getData');
// Add this route
$routes->get('accounts', 'AccountController::index');