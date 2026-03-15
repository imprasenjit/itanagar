<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Default / React SPA ──────────────────────────────────────────────────────
$routes->addRedirect('/', 'app');
$routes->get('app',        'ReactApp::serve');
$routes->get('app/(:any)', 'ReactApp::serve');

// ── Auth ─────────────────────────────────────────────────────────────────────
$routes->get('login',            'Login::index');
$routes->post('loginMe',         'Login::loginMe');
$routes->get('forgotPassword',   'Login::forgotPassword');
$routes->post('resetPasswordUser', 'Login::resetPasswordUser');
$routes->get('resetPasswordConfirmUser/(:segment)/(:segment)', 'Login::resetPasswordConfirmUser/$1/$2');
$routes->post('createPasswordUser', 'Login::createPasswordUser');
$routes->get('logout',           'Login::logout');

// ── Dashboard / Users ─────────────────────────────────────────────────────────
$routes->get('dashboard',        'User::index');
$routes->get('userListing',      'User::userListing');
$routes->get('userListing/(:num)', 'User::userListing');
$routes->get('addNew',           'User::addNew');
$routes->post('addNewUser',      'User::addNewUser');
$routes->get('editOld/(:num)',   'User::editOld/$1');
$routes->post('editUser',        'User::editUser');
$routes->post('deleteUser',      'User::deleteUser');
$routes->post('checkEmailExists','User::checkEmailExists');
$routes->get('profile',          'User::profile');
$routes->get('profile/(:segment)', 'User::profile/$1');
$routes->post('profileUpdate',   'User::profileUpdate');
$routes->post('profileUpdate/(:segment)', 'User::profileUpdate/$1');
$routes->post('changePassword',  'User::changePassword');
$routes->post('changePassword/(:segment)', 'User::changePassword/$1');
$routes->get('login-history',    'User::loginHistoy');
$routes->get('login-history/(:num)', 'User::loginHistoy/$1');
$routes->get('login-history/(:num)/(:num)', 'User::loginHistoy/$1/$2');
$routes->get('resetUserPassword','User::resetUserPassword');
$routes->get('pageNotFound',     'User::pageNotFound');

// ── Account (logged-in users) ─────────────────────────────────────────────────
$routes->get('account',          'Account::index');
$routes->post('account/pUpdate', 'Account::pUpdate');
$routes->get('account/changepassword', 'Account::changepassword');
$routes->post('account/passwordUpdate','Account::passwordUpdate');
$routes->get('account/wallet',   'Account::wallet');
$routes->post('account/wupdate', 'Account::wupdate');
$routes->get('account/refund',   'Account::refund');

// ── CMS pages ─────────────────────────────────────────────────────────────────
$routes->get('page/(:segment)',  'Page::index/$1');
$routes->get('faq',              'Page::faq');
$routes->get('refunds_cacellations', 'Page::refunds_cacellations');
$routes->get('contact',          'Page::contact');
$routes->post('contact/save',    'Page::contact_save');

// ── Game (frontend ticket buying) ──────────────────────────────────────────────
$routes->get('game',             'Game::index');
$routes->get('game/type/(:num)', 'Game::type/$1');
$routes->get('game/jackpot',     'Game::jackpot');
$routes->get('game/tickets/(:num)/(:num)/(:num)', 'Game::tickets/$1/$2/$3');
$routes->post('game/getavailabletickets', 'Game::getavailabletickets');
$routes->post('game/addtocart',  'Game::addtocart');
$routes->get('game/step2',       'Game::step2');
$routes->get('game/confirm_order','Game::confirm_order');
$routes->post('game/deletecartdata','Game::deletecartdata');
$routes->get('game/payment',     'Game::payment');
$routes->post('game/payment_confirm','Game::payment_confirm');
$routes->post('game/payment_cancelled','Game::payment_cancelled');
$routes->get('game/result',      'Game::result');

// ── Web / Admin game management ───────────────────────────────────────────────
$routes->get('web',              'Web::index');
$routes->get('web/addNew',       'Web::addNew');
$routes->post('web/addNewWeb',   'Web::addNewWeb');
$routes->get('web/edit/(:num)',  'Web::edit/$1');
$routes->post('web/editWeb',     'Web::editWeb');
$routes->post('web/deleteWeb',   'Web::deleteWeb');
$routes->get('web/view/(:num)',  'Web::view/$1');
$routes->get('web/view/(:num)/(:num)', 'Web::view/$1');
$routes->get('web/rangeEdit/(:num)', 'Web::rangeEdit/$1');
$routes->post('web/editRange',   'Web::editRange');
$routes->get('web/common',       'Web::common');
$routes->post('web/editCommon',  'Web::editCommon');
$routes->get('web/descriptionEdit/(:num)', 'Web::descriptionEdit/$1');
$routes->post('web/editdesc',    'Web::editdesc');
$routes->get('web/tier/(:num)',  'Web::tier/$1');
$routes->post('web/addtier',     'Web::addtier');
$routes->post('web/addNewWebdate/(:num)', 'Web::addNewWebdate/$1');
$routes->get('web/addtwoWebdate/(:num)', 'Web::addtwoWebdate/$1');
$routes->post('web/deleteWebDate','Web::deleteWebDate');
$routes->get('web/faq',             'Web::faq');
$routes->post('web/faq',            'Web::faq');
$routes->get('web/addfaq',          'Web::addfaq');
$routes->post('web/addNewfaq',      'Web::addNewfaq');
$routes->get('web/faqedit/(:num)',  'Web::faqedit/$1');
$routes->post('web/faqupdate',      'Web::faqupdate');
$routes->post('web/deletefaq',      'Web::deletefaq');
$routes->get('web/page',         'Web::page');
$routes->get('web/pageedit/(:num)', 'Web::pageedit/$1');
$routes->post('web/editUpadtePage','Web::editUpadtePage');
$routes->get('web/contact_list', 'Web::contact_list');
$routes->get('web/contact_list/(:num)', 'Web::contact_list');
$routes->get('web/order',            'Web::order');
$routes->post('web/order',           'Web::order');
$routes->get('web/order/(:num)',     'Web::order');
$routes->post('web/confirm_order_by_admin','Web::confirm_order_by_admin');
$routes->post('web/release_order_by_admin','Web::release_order_by_admin');

// ── Order ──────────────────────────────────────────────────────────────────────
$routes->post('order/release_order','Order::release_order');

// ── API (React frontend) ───────────────────────────────────────────────────────
$routes->options('api/(:any)', 'Api::options');
$routes->get('api/home',         'Api::home');
$routes->get('api/games',        'Api::games');
$routes->get('api/games/(:num)', 'Api::game_detail/$1');
$routes->post('api/games/(:num)/tickets/search', 'Api::ticket_search/$1');
$routes->get('api/games/(:num)/tickets/(:num)/(:num)', 'Api::game_tickets/$1/$2/$3');
$routes->get('api/faq',          'Api::faq');
$routes->get('api/page/(:segment)', 'Api::page/$1');
$routes->get('api/results',      'Api::results');
$routes->post('api/contact',     'Api::contact');
$routes->get('api/auth/me',      'Api::me');
$routes->post('api/auth/login',  'Api::login');
$routes->get('api/auth/logout',  'Api::logout');
$routes->post('api/auth/register', 'Api::register');
$routes->post('api/auth/forgot-password', 'Api::forgot_password');
$routes->get('api/cart',         'Api::cart');
$routes->post('api/cart/add',    'Api::cart_add');
$routes->delete('api/cart/(:num)', 'Api::cart_remove/$1');
$routes->get('api/order/confirm', 'Api::order_confirm');
$routes->post('api/payment/create',  'Api::payment_create');
$routes->post('api/payment/confirm', 'Api::payment_confirm');
$routes->post('api/payment/cancel',  'Api::payment_cancel');

// ── React SPA catch-all for /app/* (must be last) ───────────────────────────
$routes->get('app/(:any)', 'ReactApp::serve');
