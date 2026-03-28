# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library


Admin Panel Requirement – Itanagar Choice (Initial Stage)

Since the company currently has three members (Founder + 2 Co-founders), there will be three admin accounts. All three admins should have full operational access, but one account will be designated as Super Admin with final control over the system.

1. Admin Login

- Create a secure admin login page: "/admin"
- Login fields: Email/Username and Password
- Allow creation of three admin accounts.

2. Admin Accounts
Create three admin users:

- Admin 1 – Super Admin (Founder)
- Admin 2 – Admin (Co-Founder)
- Admin 3 – Admin (Co-Founder)

3. Access Permissions

- All three admins can access the admin panel and perform daily operations.
- All three admins can:
  - Create and edit events
  - View transactions
  - Manage tickets
  - View users
  - Access reports

4. Super Admin Control
The Super Admin should have additional authority to:

- Add or remove admin accounts
- Reset admin passwords
- Modify admin permissions
- Configure payment gateway settings
- Access complete system settings
- Override or approve sensitive actions if required

5. Admin Dashboard
Dashboard should display:

- Total events
- Total tickets sold
- Total revenue
- Today’s sales
- Recent transactions
- Upcoming events

6. Event Management
Admin should be able to:

- Add new events
- Edit or delete events
- Upload event posters
- Set event date, time, and venue
- Set ticket categories and pricing
- Define total ticket capacity

7. Transaction Management
Admin panel must display all ticket purchase transactions.

Transaction table should include:

- Date
- Event name
- User name
- Ticket ID
- Amount
- Payment status

Features:

- Event-wise transaction filtering
- Date-wise filtering
- Payment status filtering

8. Ticket Management
Admin should be able to:

- View issued tickets
- Cancel tickets
- Resend tickets to users
- Verify ticket QR code for entry

9. User Management
Admin should be able to:

- View registered users
- View user contact details
- View booking history of users

10. Reports
System should generate downloadable reports:

- Daily sales report
- Event revenue report
- Monthly revenue report

11. Payment Gateway Integration
Integrate payment gateway such as Razorpay.

Admin panel should show:

- Successful payments
- Failed payments
- Settlement records

12. Activity Log
System must record admin activities including:

- Admin name
- Action performed
- Date and time

Examples:

- Event created
- Ticket price updated
- Refund processed

Note for Future Development
In later stages, detailed role-based permissions can be implemented when more staff members are added.