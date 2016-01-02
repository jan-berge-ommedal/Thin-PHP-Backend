<?

include_once "http.php";

function assertNotEmpty($string, $errorMessage = "bad request")
{
    if (empty($string)) {
        debug("not empty string: {$errorMessage}");
        badRequest($errorMessage);
        die();
    } else {
        return $string;
    }
}


function debug($msg)
{
    if ($_REQUEST["debug"]) { // cannot use getParameter() because it uses logging
        echo "// {$msg}\n"; // TODO does comments work with json?
    }
}

?>