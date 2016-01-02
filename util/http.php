<?

include_once "config.php";
include_once "common.php";

const PARAMETER_ID = "id";
const PARAMETER_DATA_TYPE = "dataType";
const PARAMETER_DATA = "data";
const PARAMETER_CONTENT_TYPE = "contentType";
const PARAMETER_NAME = "name";

const COOKIE_SESSION_ID = "SESSIONID";

// TODO
//error_reporting(E_NONE);
// TODO

register_shutdown_function('CatchFatalError');

function CatchFatalError()
{
    $error = error_get_last();
    $ignore = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED;
    $errorType = $error['type'];
    if ($error != null && ($errorType & $ignore) == 0) {
        $errorMessage = $error['message'];
        debug("Error: type={$errorType} message={$errorMessage}");
        internalServerError($errorMessage);
    }
}

function processParameters()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "PUT" || $method == "DELETE") {
        parse_str(file_get_contents('php://input'), $_REQUEST);
    }
}

processParameters();

function setHeader($header)
{
    if (!headers_sent()) {
        header($header);
    } else {
        echo "// cannot set header: {$header}\n";
    }
}

function setStatus($code, $name)
{
    setHeader("HTTP/1.1 {$code} {$name}");
}

function setContentType($contentType)
{
    setHeader("Content-Type: {$contentType}");
}

function setContentTypeJson()
{
    setContentType("application/json; charset=UTF-8");
}

function badRequest($msg)
{
    setStatus(400, "Bad Request");
    setContentTypeJson();
    echo '{"error":"' . $msg . '"}';
    die();
}

function internalServerError($msg)
{
    setStatus(500, "Internal Server Error");
    setContentTypeJson();
    echo '{"error":"' . $msg . '"}';
    die();
}

const PARAMETER_REQUIRED = 1;
function getParameter($parameterName, $options = null)
{
    $parameter = $_REQUEST[$parameterName];
    debug("Parameter {$parameterName}={$parameter}");
    if ($options == PARAMETER_REQUIRED) {
        assertNotEmpty($parameter, "missing parameter {$parameterName}");
    }
    return $parameter;
}

function getCookie($cookieName)
{
    return $_COOKIE[$cookieName];
}

const SKIP_WWW = 1;
function getDomainName($options = null)
{
    $serverName = $_SERVER['SERVER_NAME'];
    if ($options == SKIP_WWW) {
        $serverName = substr($serverName, 4);
    }
    return $serverName;
}

function getRequestMethod()
{
    return $_SERVER['REQUEST_METHOD'];
}

function getBaseUrl()
{
    return "http://" . getDomainName();
}

function handleRequest($handlerArray)
{
    $method = getRequestMethod();
    debug("Request method: ${method}");
    $handler = $handlerArray[$method];
    if ($handler != null) {
        $handler();
    } else {
        badRequest("Method not supported: " . $method);
    }
}


function post($url, $fields)
{
    $fields_string = "";
    foreach ($fields as $key => $value) {
        $fields_string .= $key . '=' . urlencode($value) . '&';
    }
    rtrim($fields_string, '&');

    //echo "curl to {$url}\n";
    //echo "data\n{$fields_string}\n";

    debug("http post: {$fields_string}");

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    debug("response code: ${$httpcode}");
    debug("response:\n${response}");
}


?>