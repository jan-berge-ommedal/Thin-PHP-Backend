<?

include "util/all.php";

handleRequest(array(
    "POST" => function(){
        $from = getParameter("from", PARAMETER_REQUIRED);
        $subject = getParameter("subject", PARAMETER_REQUIRED);
        $body = getParameter("body", PARAMETER_REQUIRED);
        $to = emailPrefixToAddress(getParameter("to", PARAMETER_REQUIRED));

        $receiptSubjectPrefix = getParameter("receiptSubjectPrefix");
        $receiptBodyPrefix = getParameter("receiptBodyPrefix");

        sendEmail(
            $to,
            $subject,
            $body,
            $from
        );

        sendEmail(
            $from,
            $receiptSubjectPrefix . $subject,
            $receiptBodyPrefix . $body,
            $to
        );
    }
));

?>