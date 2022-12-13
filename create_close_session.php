<?php
include("functions.php");
$dblink=db_connect("document_management_system");


$session_id = create_session($username, $password);
if($session_id==-1)
{
    echo "error creating session";
    $sql="Insert into `CRON_log` (`status`,`sessionID`) values ('Failure. Error creating session. Clearing now...','N/A')";
    clear_session($username, $password);
    
    $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$mysqli->error);
    
    $session_id = create_session($username, $password);
    
    $sql="Insert into `CRON_log` (`status`,`sessionID`) values ('Session cleared and re-started.','$session_id')";
    $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$mysqli->error);
}

$file_list = query_files($username, $session_id);

if($file_list=="None")
{
    close_session($session_id);
    $sql="Insert into `CRON_log` (`status`,`sessionID`) values ('Successful - No files were available to download.','$session_id')";
    $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$mysqli->error);
    die(1);
}
elseif($file_list=="ERROR")
{
    close_session($session_id);
    $sql="Insert into `CRON_log` (`status`,`sessionID`) values ('ERROR - Problem encountered when accessing /api/query_files.','$session_id')";
    $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$mysqli->error);
    die(1);
}
else
{
    $request_status=request_files($username, $session_id, $file_list);
    
    if($request_status == 1)
    {
        $sql="Insert into `CRON_log` (`status`,`sessionID`) values ('Files successfully downloaded.','$session_id')";
        $dblink->query($sql) or
            die("Something went wrong with $sql<br>".$mysqli->error);
    }
    else
    {
        $sql="Insert into `CRON_log` (`status`,`sessionID`) values ('Files successfully downloaded.','$session_id')";
        $dblink->query($sql) or
            die("Something went wrong with $sql<br>".$mysqli->error);
    }
}

return 0;
?>