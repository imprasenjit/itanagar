<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Default / React SPA ──────────────────────────────────────────────────────
$routes->addRedirect('/', 'ui');
$routes->get('ui',        'ReactApp::serve');
$routes->get('ui/(:any)', 'ReactApp::serve');
$routes->get('reset', 'ReactApp::resetPassword');
$routes->post('reset-password', 'Api::reset_password');
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
$routes->post('web/order/(:num)',    'Web::order');
$routes->post('web/confirm_order_by_admin','Web::confirm_order_by_admin');
$routes->post('web/release_order_by_admin','Web::release_order_by_admin');

// ── Order ──────────────────────────────────────────────────────────────────────
$routes->post('order/release_order','Order::release_order');

// ── API (React frontend) ───────────────────────────────────────────────────────
$routes->options('api/(:any)', 'Api\GamesController::options');
$routes->get('api/home',         'Api\GamesController::home');
$routes->get('api/games',        'Api\GamesController::games');
$routes->get('api/games/(:num)', 'Api\GamesController::game_detail/$1');
$routes->post('api/games/(:num)/tickets/search', 'Api\GamesController::ticket_search/$1');
$routes->get('api/games/(:num)/tickets/(:num)/(:num)', 'Api\GamesController::game_tickets/$1/$2/$3');
$routes->get('api/faq',          'Api\GamesController::faq');
$routes->get('api/page/(:segment)', 'Api\GamesController::page/$1');
$routes->get('api/results',      'Api\GamesController::results');
$routes->post('api/contact',     'Api\GamesController::contact');
$routes->get('api/auth/me',      'Api\AuthController::me');
$routes->post('api/auth/login',  'Api\AuthController::login');
$routes->post('api/auth/logout',           'Api\AuthController::logout');
$routes->post('api/auth/register',         'Api\AuthController::register');
$routes->post('api/auth/forgot-password',  'Api\AuthController::forgot_password');
$routes->post('api/auth/reset-password',   'Api\AuthController::reset_password');
$routes->get('api/cart',         'Api\CartController::cart');
$routes->post('api/cart/add',    'Api\CartController::cart_add');
$routes->delete('api/cart/(:num)', 'Api\CartController::cart_remove/$1');
$routes->get('api/order/confirm', 'Api\CartController::order_confirm');
$routes->post('api/payment/create',  'Api\PaymentController::payment_create');
$routes->post('api/payment/confirm', 'Api\PaymentController::payment_confirm');
$routes->post('api/payment/cancel',  'Api\PaymentController::payment_cancel');

// ── API Account (authenticated) ─────────────────────────────────────────────
$routes->get('api/account/profile',           'Api\AccountController::account_profile');
$routes->post('api/account/profile',          'Api\AccountController::account_profile_update');
$routes->post('api/account/password',         'Api\AccountController::account_password');
$routes->get('api/account/wallet',            'Api\AccountController::account_wallet');
$routes->post('api/account/wallet/topup',     'Api\AccountController::account_wallet_topup');
$routes->get('api/account/orders',            'Api\AccountController::account_orders');
$routes->get('api/account/refunds',           'Api\AccountController::account_refunds');
$routes->post('api/account/refunds',          'Api\AccountController::account_refund_create');
$routes->get('api/account/withdrawals',       'Api\AccountController::account_withdrawals');
$routes->post('api/account/withdrawals',      'Api\AccountController::account_withdrawal_create');
$routes->get('api/account/transfers',         'Api\AccountController::account_transfers');
$routes->post('api/account/transfers',        'Api\AccountController::account_transfer_create');
$routes->get('api/account/winners',           'Api\AccountController::account_winners');

// ── React SPA catch-all for /ui/* (must be last) ───────────────────────────
$routes->get('ui/(:any)', 'ReactApp::serve');
