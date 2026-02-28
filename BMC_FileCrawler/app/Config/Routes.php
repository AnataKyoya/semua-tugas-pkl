<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('scraper', 'Scraper::index');
$routes->post('scraper/run', 'Scraper::scrape24x7');
$routes->get('scraper/export/(:any)', 'Scraper::exportFile/$1');

$routes->get('scraper/file', 'Scraper::indexFile');
$routes->post('scraper/file/set', 'Scraper::file');
$routes->get('scraper/file/panduan', 'Scraper::panduan');

// Test Group Parser Routes
$routes->group('test-group', function ($routes) {
    $routes->get('/', 'TestGroupController::index');
    $routes->get('templates', 'TestGroupController::templates');
    $routes->get('file-list', 'TestGroupController::fileList');
    $routes->get('preview/(:any)', 'TestGroupController::preview/$1');
    $routes->get('cleanup/(:num)', 'TestGroupController::cleanup/$1');

    $routes->post('upload', 'TestGroupController::upload');
    $routes->post('process', 'TestGroupController::process');
    $routes->post('export/(json|csv)', 'TestGroupController::export/$1');
    $routes->post('parse-direct', 'TestGroupController::parseDirect');

    $routes->delete('delete-file/(:any)', 'TestGroupController::deleteFile/$1');
});

$routes->get('ui', 'Scraper::ui');
$routes->get('check-dom', 'TestGroupController::checkChildClassesFromParent');
$routes->get('test-dom', 'TestGroupController::testLib');
$routes->post('check-dom/process', 'TestGroupController::process');
$routes->get('dom/check-child-class', 'TestGroupController::checkChildClass');
$routes->post('dom/check-child-class', 'TestGroupController::checkChildClass');
$routes->get('dom/analyze', 'DomAnalyzerController::index');
$routes->post('dom/analyze', 'DomAnalyzerController::analyze');
$routes->get('api/dom/check-class', 'Api\TestGroupController::checkClass');
$routes->post('api/dom/check-class', 'Api\TestGroupController::checkClass');


// app/Config/Routes.php
$routes->group('file-crawler', function ($routes) {
    $routes->get('/', 'FileCrawlerController::index');
    $routes->post('file-upload', 'FileCrawlerController::fileUpload');
    $routes->post('process', 'FileCrawlerController::process');
    $routes->get('list-files', 'FileCrawlerController::listFiles');
    $routes->delete('delete-file/(:segment)', 'FileCrawlerController::deleteFile/$1');
    $routes->get('downloadas/(:segment)/(:segment)', 'DownloadController::downloadAs/$1/$2');
});
$routes->get('testdownload/(:segment)/(:segment)', 'DownloadController::downloadAs/$1/$2');

$routes->get('openrouter', 'OpenRouterTestController::index');
$routes->get('openrouter/stream', 'OpenRouterTestController::stream');

$routes->group('bmc', function ($routes) {
    $routes->get('/', 'BMCController::index');
    $routes->post('process', 'BMCController::process');
    $routes->post('stream', 'BMCController::stream');
    $routes->get('result/(:any)', 'BMCController::getResult/$1');
});
