<?

include "util/all.php";

$performGet = function () {
    withStatement("SELECT name, contentType, data FROM DATA WHERE ID=?", function ($statement) {

        $id = getParameter(PARAMETER_ID, PARAMETER_REQUIRED);

        $statement->bind_param("s", $id);

        executeStatement($statement);

        $result = $statement->get_result();
        list($name, $type, $data) = $result->fetch_array();
        $name = $name ? $name : "file";
        if ($data) {
            header("Content-type: {$type}");
            header("Content-Disposition: attachment; filename=\"{$name}\"");
            echo $data;
        } else {
            setStatus(404, "Not Found");
        }

    });
};

handleRequest(array(
    "GET" => $performGet
));


?>