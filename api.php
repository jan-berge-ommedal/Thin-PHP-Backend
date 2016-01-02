<?

include "util/all.php";

function performGet()
{

    withDatabase(function ($database) {
        $id = getParameter(PARAMETER_ID);
        $dataType = getParameter(PARAMETER_DATA_TYPE);
        $statement = null;

        if ($id) { // get by id
            $statement = $database->prepare('SELECT * FROM DATA WHERE id=?');
            $statement->bind_param("s", $id);
        } else if ($dataType) { // list
            $statement = $database->prepare('SELECT * FROM DATA WHERE dataType=?');
            $statement->bind_param("s", $dataType);
        } else {
            badRequest("missing parameter 'id' or 'dataType'");
        }

        executeStatement($statement);

        $result = $statement->get_result();
        if ($id) {
            resultToJsonObject($result);
        } else if ($dataType) {
            resultToJsonArray($result);
        }

        $statement->close();
    });
}


function performPost()
{
    validateUser();
    withStatement("INSERT INTO DATA(id,dataType,contentType,data, name) VALUES(?,?,?,?,?)", function ($statement) {
        $id = getParameter(PARAMETER_ID, PARAMETER_REQUIRED);
        $dataType = getParameter(PARAMETER_DATA_TYPE, PARAMETER_REQUIRED);
        $contentType = getParameter(PARAMETER_CONTENT_TYPE, PARAMETER_REQUIRED);
        $data = getParameter(PARAMETER_DATA, PARAMETER_REQUIRED);
        $name = getParameter(PARAMETER_NAME);
        $statement->bind_param("sssss", $id, $dataType, $contentType, $data, $name);
        executeStatement($statement);
    });

    performGet();
}

function performPut()
{
    validateUser();
    withStatement("UPDATE DATA SET data=? WHERE id=?", function ($statement) {
        $data = getParameter(PARAMETER_DATA, PARAMETER_REQUIRED);
        $id = getParameter(PARAMETER_ID, PARAMETER_REQUIRED);
        $statement->bind_param("ss", $data, $id);
        executeStatement($statement);
    });
}


function performDelete()
{
    validateUser();
    withStatement("DELETE FROM DATA WHERE id=?", function ($statement) {
        $id = getParameter(PARAMETER_ID, PARAMETER_REQUIRED);
        $statement->bind_param("s", $id);
        executeStatement($statement);
    });
}

handleRequest(array(
    "GET" => performGet,
    "PUT" => performPut,
    "POST" => performPost,
    "DELETE" => performDelete
));


?>