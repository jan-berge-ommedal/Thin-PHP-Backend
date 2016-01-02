<?

include_once "config.php";
include_once "http.php";

function executeStatement($statement)
{
    if (!$statement->execute()) {
        $errorMessage = 'Error executing MySQL query: ' . $statement->error;
        $statement->close();
        badRequest($errorMessage);
    }
}

const TEXT_TYPES = array(
    "application/json" => true,
    "application/xml" => true,
    "text/plain" => true
);

function encodeData(&$row)
{
    $contentType = $row['CONTENT_TYPE'];
    $textTypes = TEXT_TYPES;
    if (!($textTypes[$contentType])) {
        $row['DATA'] = base64_encode($row['DATA']);
    }
}

function resultToJsonArray($result)
{
    $encode = array();
    while ($row = $result->fetch_assoc()) {
        encodeData($row);
        $encode[] = $row;
    }
    setContentTypeJson();
    echo json_encode($encode);
}

function resultToJsonObject($result)
{
    $row = $result->fetch_assoc();
    if ($row) {
        encodeData($row);
        setContentTypeJson();
        echo json_encode($row);
    } else {
        setStatus(404, "Not Found");
    }
}

function withDatabase($function)
{
    $database = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Could not connect: ' . mysqli_error($database));
    try {
        $database->select_db(DB_SCHEMA) or die("Could not select database");
        return $function($database);
    } finally {
        $database->close();
    }
}

function withStatement($statement, $function)
{
    debug("DB statement: {$statement}");
    return withDatabase(function ($database) use (&$statement, &$function) {
        $dbStatement = $database->prepare($statement);
        try {
            return $function($dbStatement);
        } finally {
            $dbStatement->close();
        }
    });
}

function countRows($statement)
{
    executeStatement($statement);
    $result = $statement->get_result();
    return mysqli_num_rows($result);
}


?>