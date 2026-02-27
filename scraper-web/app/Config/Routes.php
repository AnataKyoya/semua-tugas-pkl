<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('scraper', 'Scraper::index');
$routes->post('scraper/run', 'Scraper::scrape24x7');
$routes->get('scraper/export/(:any)', 'Scraper::exportFile/$1');
