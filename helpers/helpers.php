<?php

global $env;

$GLOBALS['env'] = parse_ini_file('.env');

function env(string $name = '') {
    if (empty($name))                  return $GLOBALS['env'];
    if (isset($GLOBALS['env'][$name])) return $GLOBALS['env'][$name];
    
    return '';
}

function pr($data = '', $exit = true) {
    print_r('<pre>');
    print_r($data);
    print_r('</pre>');

    if ($exit) exit();
}

function getClientIp() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Prin the error message.
 * 
 * @param string $error The error view that you want to show.
 * @param array $data The data parsed in the error.
 * @return void
 */
function showError(string $errorName, array $data): void
{
    if (env('local') === 'live') {
        $message = '';
        if (!empty($data['message'])) $message = $data['message']; 
        
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'data'    => $data
        ]);
        exit();
    };

    $filePath = "./app/views/errors/$errorName.php";

    extract($data);

    include $filePath;
    $view = file_get_contents($filePath, false);

    echo $view;
    exit();
}

/**
 * Prin the error message if Local or return False if live.
 * 
 * @param string $error The error view that you want to show.
 * @param array $data The data parsed in the error.
 * @return void
 */
function showDbError(string $errorName, array $data): bool
{
    if (env('local') === 'live') return false;

    $filePath = "./app/views/errors/$errorName.php";

    extract($data);

    include $filePath;
    $view = file_get_contents($filePath, false);

    echo $view;
    exit();
}

/**
 * Build timestamp date.
 * If $offset is empty, it will create current date to timestamp.
 * 
 * @param string|null $offset Set the date to future or past.
 * @param string|null $format The format needed.
 */
function buildTDate(string $offset = null, string $format = 'Y-m-d H:i:s'): int
{
    if (!$offset) return strtotime(date($format));

    return strtotime(date($format, strtotime($offset)));
}