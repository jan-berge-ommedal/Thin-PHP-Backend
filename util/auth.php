<?

include_once "db.php";
include_once "http.php";

function isValidSessionId($sessionId)
{
    return withStatement("SELECT * FROM SESSION WHERE SESSION_ID=? AND CREATED > NOW() - INTERVAL 1 DAY", function ($statement) use (&$sessionId) {
        $statement->bind_param("s", $sessionId);
        $valid = countRows($statement) > 0;
        debug("isValidUser: " . ($valid ? "true" : "false"));
        return $valid;
    });
}

function validateUser()
{
    if (!isValidSessionId(getCookie(COOKIE_SESSION_ID))) {
        setStatus("401", "Unauthorized");
        die();
    } else {
        debug("Validated user");
    }
}

function transferSessionIdToCookieAndRedirect()
{
    $sessionId = getParameter("sessionId");
    if (isValidSessionId($sessionId)) {
        debug("setting session cookie");
        setcookie(
            COOKIE_SESSION_ID,
            $sessionId,
            time() + 60 * 60 * 24,
            "/",
            getDomainName()
        );
        $baseUrl = getBaseUrl();
        debug("redirecting to: " . $baseUrl);
        header("Location: " . $baseUrl . "/");
    } else {
        performLogout();
    }
}

function generateSessionId()
{
    return md5(rand());
}

function newLogin()
{
    withStatement("INSERT INTO SESSION (SESSION_ID,CREATED) VALUES (?,NOW())", function ($statement) {
        $sessionId = generateSessionId();
        $statement->bind_param("s", $sessionId);
        executeStatement($statement);

        $baseUrl = getBaseUrl();
        $loginEmail = emailPrefixToAddress(LOGIN_EMAIL_PREFIX);
        sendEmail(emailPrefixToAddress(LOGIN_EMAIL_PREFIX), "Innlogging", "\n\nLogg inn via denne linken:\n{$baseUrl}/php/login.php?sessionId={$sessionId}");
        echo "{\"email\":\"${loginEmail}\"}";
    });
}

function performLogout()
{
    $expire = time() - 60 * 60 * 24 * 365;
    $domainName = getDomainName();

    setcookie(COOKIE_SESSION_ID, "", $expire, "/", $domainName);
    header("Location: " . getBaseUrl() . "/");
}


?>