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
 * Parses a comma-separated range string like "250381-250382,250384-250386"
 * into an array of ['start' => int, 'end' => int] pairs.
 *
 * Single numbers (no dash) are treated as a range of one: start == end.
 */
if (!function_exists('parseTicketRanges')) {
    function parseTicketRanges(string $rangeString): array
    {
        $ranges = [];
        foreach (explode(',', trim($rangeString)) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $ends = explode('-', $part);
            $ranges[] = [
                'start' => (int) $ends[0],
                'end'   => (int) ($ends[1] ?? $ends[0]),
            ];
        }
        return $ranges;
    }
}

/**
 * Checks whether a ticket number falls within any of the parsed ranges.
 * $rangeString — e.g. "250381-250382,250384-250386,250388-250392"
 */
if (!function_exists('isTicketInRange')) {
    function isTicketInRange(int $ticket, string $rangeString): bool
    {
        foreach (parseTicketRanges($rangeString) as $r) {
            if ($ticket >= $r['start'] && $ticket <= $r['end']) {
                return true;
            }
        }
        return false;
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
