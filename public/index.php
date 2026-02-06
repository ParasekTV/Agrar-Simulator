<?php
/**
 * Agrar Simulator Browsergame
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

// Statische Seiten (öffentlich)
$router->get('/impressum', 'Page', 'impressum');
$router->get('/datenschutz', 'Page', 'datenschutz');
$router->get('/spielregeln', 'Page', 'spielregeln');

// Authentifizierung
$router->get('/login', 'Auth', 'loginForm');
$router->post('/login', 'Auth', 'login');
$router->get('/register', 'Auth', 'registerForm');
$router->post('/register', 'Auth', 'register');
$router->get('/logout', 'Auth', 'logout');

// E-Mail-Verifizierung
$router->get('/auth/verify/pending', 'Auth', 'verifyPending');
$router->get('/auth/verify/resend', 'Auth', 'resendVerificationForm');
$router->post('/auth/verify/resend', 'Auth', 'resendVerification');
$router->get('/auth/verify/{token}', 'Auth', 'verify');

// Discord OAuth
$router->get('/auth/discord', 'Auth', 'discordAuth');
$router->get('/auth/discord/callback', 'Auth', 'discordCallback');
$router->get('/auth/discord/complete', 'Auth', 'discordComplete');
$router->post('/auth/discord/register', 'Auth', 'discordRegister');
$router->post('/auth/discord/unlink', 'Auth', 'unlinkDiscord');

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
$router->post('/fields/apply-fertilizer', 'Field', 'applyFertilizer');
$router->post('/fields/lime', 'Field', 'lime');

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

// Produktionen
$router->get('/productions', 'Production', 'index');
$router->get('/productions/shop', 'Production', 'shop');
$router->get('/productions/{id}', 'Production', 'show');
$router->post('/productions/buy', 'Production', 'buy');
$router->post('/productions/toggle', 'Production', 'toggle');
$router->post('/productions/start', 'Production', 'start');
$router->post('/productions/collect', 'Production', 'collect');

// Lager
$router->get('/storage', 'Storage', 'index');
$router->get('/storage/product/{id}', 'Storage', 'product');
$router->get('/storage/search', 'Storage', 'search');
$router->post('/storage/transfer', 'Storage', 'transfer');

// Verkaufsstellen
$router->get('/salespoints', 'SalesPoint', 'index');
$router->get('/salespoints/history', 'SalesPoint', 'history');
$router->get('/salespoints/compare/{id}', 'SalesPoint', 'compare');
$router->get('/salespoints/{id}', 'SalesPoint', 'show');
$router->post('/salespoints/sell', 'SalesPoint', 'sell');

// Shop/Einkauf
$router->get('/shop', 'Shop', 'index');
$router->get('/shop/history', 'Shop', 'history');
$router->get('/shop/compare/{id}', 'Shop', 'compare');
$router->get('/shop/{id}', 'Shop', 'show');
$router->post('/shop/buy', 'Shop', 'buy');

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
$router->post('/cooperative/create', 'Cooperative', 'create');
$router->post('/cooperative/join', 'Cooperative', 'join');
$router->post('/cooperative/leave', 'Cooperative', 'leave');
$router->post('/cooperative/donate', 'Cooperative', 'donate');
$router->post('/cooperative/share-equipment', 'Cooperative', 'shareEquipment');
$router->post('/cooperative/borrow-equipment', 'Cooperative', 'borrowEquipment');
$router->post('/cooperative/return-equipment', 'Cooperative', 'returnEquipment');
$router->post('/cooperative/apply', 'Cooperative', 'apply');
$router->get('/cooperative/applications', 'Cooperative', 'applications');
$router->post('/cooperative/process-application', 'Cooperative', 'processApplication');
$router->get('/cooperative/members', 'Cooperative', 'members');
$router->post('/cooperative/assign-role', 'Cooperative', 'assignRole');
$router->post('/cooperative/kick', 'Cooperative', 'kick');
$router->get('/cooperative/warehouse', 'Cooperative', 'warehouse');
$router->post('/cooperative/deposit', 'Cooperative', 'deposit');
$router->post('/cooperative/withdraw', 'Cooperative', 'withdraw');
$router->get('/cooperative/finances', 'Cooperative', 'finances');
$router->post('/cooperative/withdraw-money', 'Cooperative', 'withdrawMoney');
$router->get('/cooperative/research', 'Cooperative', 'research');
$router->post('/cooperative/start-research', 'Cooperative', 'startResearch');
$router->get('/cooperative/challenges', 'Cooperative', 'challenges');
$router->get('/cooperative/{id}', 'Cooperative', 'show');

// News/Forum
$router->get('/news', 'News', 'index');
$router->get('/news/create', 'News', 'create');
$router->post('/news/store', 'News', 'store');
$router->get('/news/search', 'News', 'search');
$router->get('/news/{id}', 'News', 'show');
$router->post('/news/comment', 'News', 'comment');
$router->post('/news/like', 'News', 'like');
$router->post('/news/delete', 'News', 'delete');

// Bug Reports
$router->get('/bugreport', 'BugReport', 'index');
$router->post('/bugreport/submit', 'BugReport', 'submit');

// Ranglisten
$router->get('/rankings', 'Ranking', 'index');
$router->get('/rankings/cooperatives', 'Ranking', 'cooperatives');
$router->get('/rankings/challenges', 'Ranking', 'challenges');

// Admin
$router->get('/admin', 'Admin', 'index');
$router->get('/admin/users', 'Admin', 'users');
$router->get('/admin/users/{id}', 'Admin', 'editUser');
$router->post('/admin/users/{id}/update', 'Admin', 'updateUser');
$router->post('/admin/users/{id}/delete', 'Admin', 'deleteUser');
$router->post('/admin/users/{id}/verify', 'Admin', 'verifyUser');
$router->get('/admin/farms', 'Admin', 'farms');
$router->get('/admin/farms/{id}', 'Admin', 'editFarm');
$router->post('/admin/farms/{id}/update', 'Admin', 'updateFarm');
$router->get('/admin/cooperatives', 'Admin', 'cooperatives');
$router->get('/admin/cooperatives/{id}', 'Admin', 'editCooperative');
$router->post('/admin/cooperatives/{id}/update', 'Admin', 'updateCooperative');
$router->post('/admin/cooperatives/{id}/delete', 'Admin', 'deleteCooperative');
$router->post('/admin/cooperatives/remove-member', 'Admin', 'removeMember');

// Admin Bug Reports
$router->get('/admin/bugs', 'Admin', 'bugs');
$router->post('/admin/bugs/{id}/status', 'Admin', 'updateBugStatus');

// Admin News/Changelog
$router->get('/admin/news', 'Admin', 'news');
$router->get('/admin/news/create', 'Admin', 'createNews');
$router->post('/admin/news/store', 'Admin', 'storeNews');
$router->get('/admin/news/{id}', 'Admin', 'editNews');
$router->post('/admin/news/{id}/update', 'Admin', 'updateNews');
$router->post('/admin/news/{id}/delete', 'Admin', 'deleteNews');

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

// Production API
$router->api('GET', '/production/list', 'Production', 'listApi');
$router->api('GET', '/production/{id}', 'Production', 'getApi');
$router->api('POST', '/production/start', 'Production', 'startApi');
$router->api('POST', '/production/collect', 'Production', 'collectApi');
$router->api('POST', '/production/toggle', 'Production', 'toggleApi');

// Storage API
$router->api('GET', '/storage/list', 'Storage', 'listApi');
$router->api('GET', '/storage/search', 'Storage', 'searchApi');
$router->api('GET', '/storage/quantity/{id}', 'Storage', 'quantityApi');

// SalesPoint API
$router->api('GET', '/salespoint/list', 'SalesPoint', 'listApi');
$router->api('GET', '/salespoint/{id}/prices', 'SalesPoint', 'pricesApi');
$router->api('POST', '/salespoint/sell', 'SalesPoint', 'sellApi');
$router->api('GET', '/salespoint/best-prices/{id}', 'SalesPoint', 'bestPricesApi');
$router->api('GET', '/salespoint/history', 'SalesPoint', 'historyApi');

// Shop API
$router->api('GET', '/shop/list', 'Shop', 'listApi');
$router->api('GET', '/shop/{id}/prices', 'Shop', 'pricesApi');
$router->api('POST', '/shop/buy', 'Shop', 'buyApi');
$router->api('GET', '/shop/best-prices/{id}', 'Shop', 'bestPricesApi');
$router->api('GET', '/shop/history', 'Shop', 'historyApi');

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
