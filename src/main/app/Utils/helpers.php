<?php
/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

use App\Core\Auth\UserPrincipal;
use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Matex\Evaluator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Payload;

function current_user(): ?UserPrincipal
{
    $payload = jwt_payload();
    // TODO: Bypass dummy token
    if ($payload === null) return dummy_user();

    $ret = new UserPrincipal();
    $ret->id = $payload->get('sub');
    $ret->username = $payload->get('username');
    $ret->role_ids = $payload->get('roles') ?? [];
    $ret->company_id = get_company_id($payload->get('companies') ?? []);

    return $ret;
}

function jwt_payload(): ?Payload
{
    try {
        return JWTAuth::parseToken()->getPayload();
    } catch (Exception $e) {
        Log::error('Can\'t parse jwt token: '.$e->getMessage());
        return null;
    }
}

function get_company_id(array $company_ids): int
{
    if (sizeof($company_ids) === 1) return $company_ids[0];

    $headerCompanyId = get_company_id_from_header();
    if ($headerCompanyId !== null) {
        if (empty($company_ids)) return $headerCompanyId;
        foreach ($company_ids as $companyId) {
            if ($companyId === $headerCompanyId) return $companyId;
        }
    }
    abort(403, "Can't get company from this user");
}

function dummy_user(): UserPrincipal
{
    $ret = new UserPrincipal();
    $ret->id = 1;
    $ret->username = 'super.admin.system';
    $ret->role_ids = [1];
    $ret->company_id = 1;
    return $ret;
}

function get_company_id_from_header(): ?int
{
    $value = $_SERVER['HTTP_X_COMPANY_ID'] ?? null;
    if ($value === null) return null;
    return (int) $value;
}

function abort($code, $message = '', ?string $key = null, ?array $vars = null, \Throwable $previous = null,
               array $headers = [])
{
    if ($code instanceof Response) {
        throw new HttpResponseException($code);
    } elseif ($code instanceof Responsable) {
        throw new HttpResponseException($code->toResponse(request()));
    }
    throw new ValidationException($code, $message, $previous, $headers, 0, $key, $vars);
}

function is_sequential_array($array): bool
{
    if (!is_array($array)) return false;
    return array_keys($array) === range(0, count($array) - 1);
}

function array_find(array $array, Closure $block)
{
    foreach ($array as $item) {
        if ($block($item)) return $item;
    }
    return null;
}

function map($items, \Closure $cb): array {
    $ret = [];
    foreach ($items as $d) {
        $ret[] = $cb($d);
    }
    return $ret;
}

function iterate_each_month(Carbon $start, Carbon $end, \Closure $block)
{
    $isLastDayOfMonth = $end->isLastOfMonth();
    $current = $start->clone();
    $date = $current->day;

    while ($current->isBefore($end)) {
        if ($isLastDayOfMonth) {
            $periodEnd = $current->clone()->addDay()->lastOfMonth();
        } else {
            $periodEnd = $current->clone()->startOfMonth()->addMonth();
            $lastDay = $periodEnd->clone()->lastOfMonth()->day;
            $periodEnd->setDay(($date < $lastDay) ? $date : $lastDay)->subDay();
        }
        $block($current, $periodEnd);
        $current = $periodEnd->clone()->addDay();
    }
}

function str_to_date($str): ?Carbon
{
    if ($str === null) return null;
    if ($str instanceof Carbon) return $str;
    return Carbon::createFromFormat('Y-m-d', $str);
}

function date_to_str($date): ?string
{
    if ($date === null) return null;
    if ($date instanceof Carbon) return $date->format('Y-m-d');
    return (string) $date;
}

function is_same_date($date, $other): bool
{
    if ($date === null || $other === null) return false;
    if (!($date instanceof Carbon)) $date = str_to_date($date);
    return $date->isSameAs('Y-m-d', $other);
}

function plus_day($date): ?Carbon
{
    return plus_days($date, 1);
}

function plus_days($date, int $days): ?Carbon
{
    if ($date instanceof Carbon) {
        $newDate = $date->clone();
    } else {
        $newDate = str_to_date($date);
    }
    return $newDate->addDays($days);
}

function minus_day($date): ?Carbon
{
    return minus_days($date, 1);
}

function minus_days($date, int $days): ?Carbon
{
    if ($date instanceof Carbon) {
        $newDate = $date->clone();
    } else {
        $newDate = str_to_date($date);
    }
    return $newDate->subDays($days);
}

function min_date(...$dates): ?Carbon
{
    $min = null;
    foreach ($dates as $d) {
        $date = is_string($d) ? str_to_date($d) : ($d instanceof Carbon ? $d : null);
        if ($date === null) continue;
        if ($min === null || $date->isBefore($min)) {
            $min = $date;
        }
    }
    return $min;
}

function max_date(...$dates): ?Carbon
{
    $max = null;
    foreach ($dates as $d) {
        $date = is_string($d) ? str_to_date($d) : ($d instanceof Carbon ? $d : null);
        if ($date === null) continue;
        if ($max === null || $date->isAfter($max)) {
            $max = $date;
        }
    }
    return $max;
}

function is_bot($date): bool
{
    if ($date === null) return false;
    if (!($date instanceof Carbon)) $date = str_to_date($date);
    return $date->format('Y-m-d') === BOT;
}

function is_eot($date): bool
{
    if ($date === null) return false;
    if (!($date instanceof Carbon)) $date = str_to_date($date);
    return $date->format('Y-m-d') === EOT;
}

function now()
{
    return date('Y-m-d H:i:s');
}

/**
 * Generate unique id.
 *  - short -> char length 11
 *  - normal -> char length 14
 *  - full -> char length 22
 */
function generate_id(array $options = []): String
{
    if ($options['short'] ?? false) {
        $id = micro_time_bin().random_bytes(1);
        $len = 11;
    } else if ($options['full'] ?? false) {
        $id = micro_time_bin().random_bytes(6).random_bytes(3);
        $len = 22;
    } else {
        $id = micro_time_bin().random_bytes(3);
        $len = 14;
    }
    $ret = gmp_strval(gmp_init(bin2hex($id), 16), 62);
    return str_pad($ret, $len, '0', STR_PAD_LEFT);
}

function micro_time_bin(): string
{
    // get real microsecond precision, as both microtime(1) and array_sum(explode(' ', microtime()))
    // are limited by php.ini precision
    $timeParts = explode(' ', microtime());
    $timeMicroSec = $timeParts[1].substr($timeParts[0], 2, 6);
    // convert to 56-bit integer (7 bytes), enough to store micro time is enough up to 4253-05-31 22:20:37
    $time = base_convert($timeMicroSec, 10, 16);
    // left pad the eventual gap
    return hex2bin(str_pad($time, 14, '0', STR_PAD_LEFT));
}

function exec_procedure(string $procedureName, array $params, array $paramValues): object
{
    $db_config = Config::get('database.connections.'.Config::get('database.default'));
    $mysqli = mysqli_connect("p:".$db_config["host"], $db_config['username'], $db_config['password'], $db_config['database']);

    $sParams = '';
    $aParams = [];
    $types = '';
    $sParamsSelect = '';

    foreach ($params as $p) {
        $name = strtolower(str_starts_with($p->name, '_') ? substr($p->name, 1) : $p->name);
        if (!empty($sParams)) $sParams .= ', ';
        $sParams .= $p->out ? "@$name" : '?';
        if (!$p->out) {
            $types .= $p->type ?? 's';
            $aParams[] = $paramValues[$name] ?? null;
        } else {
            if (!empty($sParamsSelect)) $sParamsSelect .= ', ';
            $sParamsSelect .= "@$name as $name";
        }
    }

    $call = mysqli_prepare($mysqli, "call $procedureName($sParams)");

    mysqli_stmt_bind_param($call, $types, ...$aParams);
    mysqli_stmt_execute($call);

    $select = mysqli_query($mysqli, "select $sParamsSelect");
    $result = mysqli_fetch_assoc($select);

    return (object) $result;
}

function extract_formula_expr(string $expression): array
{
    $ret = [];
    $matches = [];
    preg_match_all('/([A-Za-z_]+\w*.?[A-Za-z_]+\w*)\b/', strtolower($expression), $matches);
    if (empty($matches)) return $ret;
    foreach (array_unique($matches[0]) as $var) {
        $s = explode('.', $var);
        $ret[] = (object) ['var' => $var, 'element' => $s[0], 'input_value' => $s[1] ?? 'amount'];
    }
    return $ret;
}

/**
 * @throws \Matex\Exception
 */
function parse_formula(string $formula, ?array $variables = null)
{
    $evaluator = new Evaluator();
    if (!empty($variables)) $evaluator->variables = $variables;
    return $evaluator->execute($formula);
}

function simple_trace(\Throwable $e, $seen = null): array
{
    $starter = $seen ? 'Caused by: ' : '';
    $result = [];
    if (!$seen) $seen = [];
    $trace = $e->getTrace();
    $prev = $e->getPrevious();
    $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
    $file = $e->getFile();
    $line = $e->getLine();

    while (true) {
        $current = "$file:$line";
        if (is_array($seen) && in_array($current, $seen)) {
            $result[] = sprintf(' ... %d more', count($trace) + 1);
            break;
        }
        $result[] = sprintf(' at %s%s%s(%s%s%s)',
            (count($trace) && array_key_exists('class', $trace[0])) ? str_replace('\\', '.', $trace[0]['class']) : '',
            (count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0])) ? '.' : '',
            (count($trace) && array_key_exists('function', $trace[0])) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
            ($line === null) ? $file : basename($file),
            ($line === null) ? '' : ':',
            ($line === null) ? '' : $line
        );
        if (is_array($seen)) $seen[] = "$file:$line";
        if (!count($trace)) break;

        $file = (array_key_exists('file', $trace[0])) ? $trace[0]['file'] : 'Unknown Source';
        $line = (array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line']) ? $trace[0]['line'] : null;
        array_shift($trace);
    }

    if ($prev) {
        $result = array_merge($result, simple_trace($prev, $seen));
    }

    return $result;
}
