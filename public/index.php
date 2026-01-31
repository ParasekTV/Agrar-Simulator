<?php
/**
 * Landwirtschafts-Simulator Browsergame
 *
 * Entry Point
 */

// Lade Konfiguration
require_once __DIR__ . '/../config/config.php';

// Erstelle Router
$router = new Router(BASE_URL);

// ==========================================
// Web-Routen
// ==========================================

// Home / Landing
$router->get('/', 'Auth', 'loginForm');

// Authentifizierung
$router->get('/login', 'Auth', 'loginForm');
$router->post('/login', 'Auth', 'login');
$router->get('/register', 'Auth', 'registerForm');
$router->post('/register', 'Auth', 'register');
$router->get('/logout', 'Auth', 'logout');

// Dashboard
$router->get('/dashboard', 'Farm', 'dashboard');

// Farm
$router->get('/farm', 'Farm', 'overview');
$router->get('/inventory', 'Farm', 'inventory');
$router->get('/events', 'Farm', 'events');

// Felder
$router->get('/fields', 'Field', 'index');
$router->get('/fields/{id}', 'Field', 'show');
$router->post('/fields/plant', 'Field', 'plant');
$router->post('/fields/harvest', 'Field', 'harvest');
$router->post('/fields/buy', 'Field', 'buy');
$router->post('/fields/fertilize', 'Field', 'fertilize');

// Tiere
$router->get('/animals', 'Animal', 'index');
$router->post('/animals/buy', 'Animal', 'buy');
$router->post('/animals/feed', 'Animal', 'feed');
$router->post('/animals/collect', 'Animal', 'collect');
$router->post('/animals/sell', 'Animal', 'sell');

// Fahrzeuge
$router->get('/vehicles', 'Vehicle', 'index');
$router->post('/vehicles/buy', 'Vehicle', 'buy');
$router->post('/vehicles/sell', 'Vehicle', 'sell');
$router->post('/vehicles/repair', 'Vehicle', 'repair');

// Forschung
$router->get('/research', 'Research', 'index');
$router->post('/research/start', 'Research', 'start');
$router->post('/research/cancel', 'Research', 'cancel');

// Marktplatz
$router->get('/market', 'Market', 'index');
$router->post('/market/create', 'Market', 'create');
$router->post('/market/buy', 'Market', 'buy');
$router->post('/market/cancel', 'Market', 'cancel');
$router->post('/market/sell-direct', 'Market', 'sellDirect');
$router->get('/market/history', 'Market', 'history');

// Genossenschaften
$router->get('/cooperative', 'Cooperative', 'index');
$router->get('/cooperative/{id}', 'Cooperative', 'show');
$router->post('/cooperative/create', 'Cooperative', 'create');
$router->post('/cooperative/join', 'Cooperative', 'join');
$router->post('/cooperative/leave', 'Cooperative', 'leave');
$router->post('/cooperative/donate', 'Cooperative', 'donate');
$router->post('/cooperative/share-equipment', 'Cooperative', 'shareEquipment');
$router->post('/cooperative/borrow-equipment', 'Cooperative', 'borrowEquipment');
$router->post('/cooperative/return-equipment', 'Cooperative', 'returnEquipment');

// News/Forum
$router->get('/news', 'News', 'index');
$router->get('/news/create', 'News', 'create');
$router->post('/news/store', 'News', 'store');
$router->get('/news/search', 'News', 'search');
$router->get('/news/{id}', 'News', 'show');
$router->post('/news/comment', 'News', 'comment');
$router->post('/news/like', 'News', 'like');
$router->post('/news/delete', 'News', 'delete');

// Ranglisten
$router->get('/rankings', 'Ranking', 'index');
$router->get('/rankings/cooperatives', 'Ranking', 'cooperatives');
$router->get('/rankings/challenges', 'Ranking', 'challenges');

// ==========================================
// API-Routen
// ==========================================

// Auth API
$router->api('GET', '/auth/check', 'Auth', 'checkApi');

// Farm API
$router->api('GET', '/farm/stats', 'Farm', 'statsApi');
$router->api('GET', '/farm/data', 'Farm', 'dataApi');
$router->api('GET', '/farm/fields', 'Farm', 'fieldsApi');
$router->api('GET', '/farm/animals', 'Farm', 'animalsApi');
$router->api('GET', '/farm/vehicles', 'Farm', 'vehiclesApi');
$router->api('GET', '/farm/inventory', 'Farm', 'inventoryApi');
$router->api('GET', '/farm/events', 'Farm', 'eventsApi');

// Field API
$router->api('POST', '/field/plant', 'Field', 'plantApi');
$router->api('POST', '/field/harvest', 'Field', 'harvestApi');
$router->api('GET', '/field/{id}', 'Field', 'getApi');
$router->api('POST', '/field/buy', 'Field', 'buyApi');

// Animal API
$router->api('POST', '/animal/buy', 'Animal', 'buyApi');
$router->api('POST', '/animal/feed', 'Animal', 'feedApi');
$router->api('POST', '/animal/collect', 'Animal', 'collectApi');
$router->api('GET', '/animal/available', 'Animal', 'availableApi');

// Vehicle API
$router->api('POST', '/vehicle/buy', 'Vehicle', 'buyApi');
$router->api('POST', '/vehicle/sell', 'Vehicle', 'sellApi');
$router->api('POST', '/vehicle/repair', 'Vehicle', 'repairApi');
$router->api('GET', '/vehicle/available', 'Vehicle', 'availableApi');

// Research API
$router->api('GET', '/research/tree', 'Research', 'treeApi');
$router->api('POST', '/research/start', 'Research', 'startApi');
$router->api('GET', '/research/progress', 'Research', 'progressApi');
$router->api('POST', '/research/complete', 'Research', 'completeApi');

// Market API
$router->api('GET', '/market/listings', 'Market', 'listingsApi');
$router->api('POST', '/market/create', 'Market', 'createApi');
$router->api('POST', '/market/buy', 'Market', 'buyApi');
$router->api('DELETE', '/market/cancel/{id}', 'Market', 'cancelApi');

// Cooperative API
$router->api('GET', '/cooperative/list', 'Cooperative', 'listApi');
$router->api('GET', '/cooperative/{id}/members', 'Cooperative', 'membersApi');

// Ranking API
$router->api('GET', '/rankings/global', 'Ranking', 'globalApi');
$router->api('GET', '/rankings/cooperatives', 'Ranking', 'cooperativesApi');
$router->api('GET', '/rankings/weekly', 'Ranking', 'weeklyApi');

// News API
$router->api('GET', '/news/posts', 'News', 'postsApi');
$router->api('POST', '/news/create', 'News', 'createApi');
$router->api('POST', '/news/like', 'News', 'likeApi');

// Verarbeite Anfrage
$router->dispatch();
