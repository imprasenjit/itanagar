<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Admin Auth ───────────────────────────────────────────────────────────────
$routes->addRedirect('/', 'login');
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
$routes->get('customerListing',  'User::customerListing');
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

// ── Web / Admin game listing ──────────────────────────────────────────────────
$routes->get('web',              'Web::index');
$routes->get('web/weblist_data', 'Web::weblist_data');

// ── GameController — game CRUD, ranges, tiers, draw dates ────────────────────
$routes->get('web/addNew',       'GameController::addNew');
$routes->post('web/addNewWeb',   'GameController::addNewWeb');
$routes->get('web/edit/(:num)',  'GameController::edit/$1');
$routes->post('web/editWeb',     'GameController::editWeb');
$routes->post('web/deleteWeb',   'GameController::deleteWeb');
$routes->get('web/view/(:num)',  'GameController::view/$1');
$routes->get('web/view/(:num)/(:num)', 'GameController::view/$1');
$routes->get('web/rangeEdit/(:num)', 'GameController::rangeEdit/$1');
$routes->post('web/editRange',   'GameController::editRange');
$routes->get('web/descriptionEdit/(:num)', 'GameController::descriptionEdit/$1');
$routes->post('web/editdesc',    'GameController::editdesc');
$routes->get('web/tier/(:num)',  'GameController::tier/$1');
$routes->post('web/addtier',     'GameController::addtier');
$routes->post('web/addNewWebdate/(:num)', 'GameController::addNewWebdate/$1');
$routes->get('web/addtwoWebdate/(:num)', 'GameController::addtwoWebdate/$1');
$routes->post('web/deleteWebDate','GameController::deleteWebDate');

// ── SettingsController — reports, dashboard
$routes->get('web/reports',              'SettingsController::reports');
$routes->get('web/report_download',      'SettingsController::report_download');
$routes->get('web/dashboard_stats',      'SettingsController::dashboard_stats');
$routes->get('web/dashboard_txn_data',   'SettingsController::dashboard_txn_data');

// ── MigrationController — migration tracker
$routes->get('web/migrations',           'MigrationController::migrations');
$routes->post('web/runMigrations',       'MigrationController::runMigrations');
$routes->post('web/runSingleMigration',   'MigrationController::runSingleMigration');
$routes->post('web/runSeeder',            'MigrationController::runSeeder');

// ── RoleController — role CRUD and RBAC permission assignment
$routes->get('web/rbac',                 'RoleController::rbac');
$routes->post('web/rbacSave',            'RoleController::rbacSave');
$routes->get('web/roles',                'RoleController::roles');
$routes->get('web/addRole',              'RoleController::addRole');
$routes->post('web/addRole',             'RoleController::addRole');
$routes->get('web/editRole/(:num)',       'RoleController::editRole/$1');
$routes->post('web/updateRole',          'RoleController::updateRole');
$routes->post('web/deleteRole',          'RoleController::deleteRole');

// ── DataTables server-side data endpoints ─────────────────────────────────────
$routes->get('users_data',                          'User::users_data');
$routes->get('login_history_data',                  'User::login_history_data');
$routes->get('login_history_data/(:num)',            'User::login_history_data/$1');
$routes->get('customers_data',                      'User::customers_data');
$routes->get('web/faq_data',                        'ContentController::faq_data');
$routes->get('web/page_data',                       'ContentController::page_data');
$routes->get('web/contact_data',                    'ContentController::contact_data');
$routes->get('web/roles_data',                      'RoleController::roles_data');
$routes->get('web/wallet_data',                     'FinanceController::wallet_data');
$routes->get('web/winner_data',                     'FinanceController::winner_data');
$routes->get('web/withdrawl_data',                  'FinanceController::withdrawl_data');
$routes->get('web/tickets_data',                    'FinanceController::tickets_data');
$routes->get('web/refund_data',                     'FinanceController::refund_data');
$routes->get('web/user_wallet_data/(:num)',          'FinanceController::user_wallet_data/$1');
$routes->get('web/user_order_data/(:num)',           'FinanceController::user_order_data/$1');
$routes->get('web/detail_data/(:num)',               'GameController::detail_data/$1');

// ── ContentController — FAQ, CMS pages, contact ───────────────────────────────
$routes->get('web/faq',             'ContentController::faq');
$routes->post('web/faq',            'ContentController::faq');
$routes->get('web/addfaq',          'ContentController::addfaq');
$routes->post('web/addNewfaq',      'ContentController::addNewfaq');
$routes->get('web/faqedit/(:num)',  'ContentController::faqedit/$1');
$routes->post('web/faqupdate',      'ContentController::faqupdate');
$routes->post('web/deletefaq',      'ContentController::deletefaq');
$routes->get('web/page',            'ContentController::page');
$routes->get('web/pageedit/(:num)', 'ContentController::pageedit/$1');
$routes->post('web/editUpadtePage', 'ContentController::editUpadtePage');
$routes->get('web/contact_list',    'ContentController::contact_list');
$routes->get('web/contact_list/(:num)', 'ContentController::contact_list');

// ── FinanceController — orders, tickets, transactions, wallet, winners ────────
$routes->get('web/order_data',               'FinanceController::order_data');
$routes->get('web/order',                    'FinanceController::order');
$routes->post('web/order',                   'FinanceController::order');
$routes->get('web/order/(:num)',             'FinanceController::order');
$routes->post('web/order/(:num)',            'FinanceController::order');
$routes->post('web/confirm_order_by_admin',  'FinanceController::confirm_order_by_admin');
$routes->post('web/release_order_by_admin',  'FinanceController::release_order_by_admin');
$routes->get('web/tickets',                  'FinanceController::tickets');
$routes->get('web/tickets/(:num)',           'FinanceController::tickets');
$routes->post('web/ticket_cancel',           'FinanceController::ticket_cancel');
$routes->post('web/ticket_resend',           'FinanceController::ticket_resend');
$routes->post('web/ticket_verify',           'FinanceController::ticket_verify');
$routes->get('web/blocked_tickets_count',    'FinanceController::blocked_tickets_count');
$routes->post('web/release_expired_holds',   'FinanceController::release_expired_holds');
$routes->post('web/force_release_holds',     'FinanceController::force_release_holds');
$routes->get('web/transactions_data',        'FinanceController::transactions_data');
$routes->get('web/transactions',             'FinanceController::transactions');
$routes->get('web/transactions/(:num)',      'FinanceController::transactions');
$routes->get('web/wallet',                   'FinanceController::wallet');
$routes->get('web/wallet/(:num)',            'FinanceController::wallet');
$routes->post('web/wallet',                  'FinanceController::wallet');
$routes->post('web/wallet/(:num)',           'FinanceController::wallet');
$routes->get('web/winner',                   'FinanceController::winner');
$routes->get('web/winner/(:num)',            'FinanceController::winner');
$routes->post('web/winner',                  'FinanceController::winner');
$routes->post('web/winner/(:num)',           'FinanceController::winner');
$routes->get('web/refund',                   'FinanceController::refund');
$routes->get('web/refund/(:num)',            'FinanceController::refund');
$routes->post('web/refund',                  'FinanceController::refund');
$routes->post('web/refund/(:num)',           'FinanceController::refund');
$routes->post('web/refund_req/(:num)',       'FinanceController::refund_req/$1');
$routes->get('web/withdrawl',               'FinanceController::withdrawl');
$routes->get('web/withdrawl/(:num)',        'FinanceController::withdrawl');
$routes->post('web/withdrawl',              'FinanceController::withdrawl');
$routes->post('web/withdrawl/(:num)',       'FinanceController::withdrawl');
$routes->post('web/with_req/(:num)',        'FinanceController::with_req/$1');

// ── Order ──────────────────────────────────────────────────────────────────────
$routes->post('order/release_order','Order::release_order');

// ── API (React frontend — served from admin.itanagarchoice.com) ──────────────
$routes->options('api/(:any)', 'Api\GamesController::options');
$routes->get('api/home',         'Api\GamesController::home');
$routes->get('api/games',              'Api\GamesController::games');
$routes->get('api/games/upcoming',     'Api\GamesController::upcoming_games');
$routes->get('api/games/(:num)',        'Api\GamesController::game_detail/$1');
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

// ── Cron: release expired cart reservations (secured by CRON_SECRET env var) ─
$routes->get('api/cron/release-reservations', 'Api\GamesController::release_reservations');

// ── API Account (authenticated) ─────────────────────────────────────────────
$routes->get('api/account/profile',           'Api\AccountController::account_profile');
$routes->post('api/account/profile',          'Api\AccountController::account_profile_update');
$routes->post('api/account/password',         'Api\AccountController::account_password');
$routes->get('api/account/wallet',            'Api\AccountController::account_wallet');
$routes->post('api/account/wallet/topup',     'Api\AccountController::account_wallet_topup');
$routes->get('api/account/orders',            'Api\AccountController::account_orders');
$routes->get('api/account/orders/(:num)',      'Api\AccountController::account_order_detail/$1');
$routes->get('api/account/refunds',           'Api\AccountController::account_refunds');
$routes->post('api/account/refunds',          'Api\AccountController::account_refund_create');
$routes->get('api/account/withdrawals',       'Api\AccountController::account_withdrawals');
$routes->post('api/account/withdrawals',      'Api\AccountController::account_withdrawal_create');
$routes->get('api/account/transfers',         'Api\AccountController::account_transfers');
$routes->post('api/account/transfers',        'Api\AccountController::account_transfer_create');
$routes->get('api/account/winners',           'Api\AccountController::account_winners');
