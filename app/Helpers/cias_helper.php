<?php

/**
 * Debug dump — prints data and halts.
 */
if (!function_exists('pre')) {
    function pre($data): void
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
}

/**
 * Returns a bcrypt hash of the plain password.
 */
if (!function_exists('getHashedPassword')) {
    function getHashedPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }
}

/**
 * Verifies a plain password against a bcrypt hash.
 */
if (!function_exists('verifyHashedPassword')) {
    function verifyHashedPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}

/**
 * CI3 compatibility shim: validation_errors($prefix, $suffix)
 * Collects all validation errors from CI4's validation service and wraps
 * each message in the supplied prefix/suffix HTML strings.
 */
if (!function_exists('validation_errors')) {
    function validation_errors(string $prefix = '<p>', string $suffix = '</p>'): string
    {
        $errors = \Config\Services::validation()->getErrors();
        if (empty($errors)) {
            return '';
        }
        $out = '';
        foreach ($errors as $error) {
            $out .= $prefix . esc($error) . $suffix;
        }
        return $out;
    }
}

/**
 * CI3 compatibility shim: set_value($field, $default)
 * Re-populates form fields using CI4's old() helper after validation failure.
 */
if (!function_exists('set_value')) {
    function set_value(string $field, string $default = ''): string
    {
        return esc(old($field, $default));
    }
}

/**
 * Returns a human-readable browser/agent string from the current request.
 */
if (!function_exists('getBrowserAgent')) {
    function getBrowserAgent(): string
    {
        $agent = \Config\Services::userAgent(\Config\Services::request());

        if ($agent === null) {
            return 'Unidentified User Agent';
        }
        if ($agent->isBrowser()) {
            return $agent->getBrowser() . ' ' . $agent->getVersion();
        }
        if ($agent->isRobot()) {
            return $agent->getRobot();
        }
        if ($agent->isMobile()) {
            return $agent->getMobile();
        }
        return 'Unidentified User Agent';
    }
}
