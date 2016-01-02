<?

include "util/all.php";

handleRequest(array(
    "GET" => transferSessionIdToCookieAndRedirect,
    "PUT" => newLogin,
    "POST" => newLogin,
));

?>