<?php
function db_connect($db)
{
	$dblink=new mysqli($hostname,$username,$password,$db);
	if (mysqli_connect_errno())
	{
		die("Error connecting to database: ".mysqli_connect_error());
	}
	return $dblink;
}

function clear_session($username, $password)
{
    $data="username=$username&password=$password";
    $ch=curl_init('https://cs4743.professorvaladez.com/api/clear_session');
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,array(
        'content-type: application/x-www-form-urlencoded',
        'content-length: ' . strlen($data))
    );
    
    //execute cURL:
    $result=curl_exec($ch);
    curl_close($ch);
    
    $sql="Insert into `sessions` (`action`,`status`,`message`,`request`,`user`,`date`,`time`) values ('N/A','N/A','N/A','Requested to CLEAR session','$username','$date','$time')"; 
       
    $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$mysqli->error); 
}

function create_session($username, $password)
{
    $data="username=$username&password=$password";
    $ch=curl_init('https://cs4743.professorvaladez.com/api/create_session');
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,array(
        'content-type: application/x-www-form-urlencoded',
        'content-length: ' . strlen($data))
    );
    
    //execute cURL:
    $result=curl_exec($ch);
    curl_close($ch);
    $cinfo=json_decode($result,true);
    $dblink=db_connect("document_management_system");
    
    if($cinfo[0]=='Status: OK')
    {
        $sessionID = $cinfo[2];     
        $date=date("Y-m-d");
        $time=date("H:i:s");
        
        //successfully obtained a session ID from the server. 
        
        //LOG THIS IN THE DATABASE:
        $sql="Insert into `sessions` (`action`,`status`,`message`,`request`,`user`,`date`,`time`) values ('$cinfo[2]','$cinfo[0]','$cinfo[1]','Requested to create session','$username','$date','$time')"; 
       
        $dblink->query($sql) or
            die("Something went wrong with $sql<br>".$mysqli->error); 
        
        return $sessionID;
    }
    else
    {
        $date=date("Y-m-d");
        $time=date("H:i:s");
        $sql="Insert into `sessions` (`action`,`status`,`message`,`request`,`user`,`date`,`time`) values ('$cinfo[2]','$cinfo[0]','$cinfo[1]','Requested to create session','$username','$date','$time')";
        $dblink->query($sql) or
            die("Something went wrong with $sql<br>".$mysqli->error);
        
        return -1;
    }
}


function close_session($session_id)
{
    $username='dga804';
    $dblink=db_connect("document_management_system");
    $data="sid=$session_id";
    $ch=curl_init('https://cs4743.professorvaladez.com/api/close_session');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'content-type: application/x-www-form-urlencoded',
        'content-length: ' . strlen($data))
    );
    
    //execute cURL:
    $result = curl_exec($ch);
    curl_close($ch);
    
    $date=date("Y-m-d");
    $time=date("H:i:s");
    
    $cinfo = json_decode($result, true);

    $sql="Insert into `sessions` (`action`,`status`,`message`,`request`,`user`,`date`,`time`) values ('$cinfo[2]','$cinfo[0]','$cinfo[1]','Requested to end session','$username','$date','$time')";
    
    $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$mysqli->error);
}

function download_file($sid, $username, $fileID)
{
    $data="sid=$sid&uid=$username&fid=$fileID";
    $ch=curl_init('https://cs4743.professorvaladez.com/api/request_file');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'content-type: application/x-www-form-urlencoded',
        'content-length: ' . strlen($data))
    );
    
    $file = curl_exec($ch);
    curl_close($ch);
    $fp = fopen("/var/www/html/temp_downloads/$fileID","wb");
    fwrite($fp,$file);
    fclose($fp);
    if(filesize("/var/www/html/temp_downloads/$fileID") <= 67)
    {
        return "ERROR";
    }
    else
    {
        return $file;
    }
}
function query_files($username, $sid)
{
    $dblink=db_connect("document_management_system");
    $data="sid=$sid&uid=$username";
    $ch=curl_init('https://cs4743.professorvaladez.com/api/query_files');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'content-type: application/x-www-form-urlencoded',
        'content-length: ' . strlen($data))
    );
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    $date=date("Y-m-d");
    $time=date("H:i:s");
    
    $cinfo = json_decode($result, true);
    
    if($cinfo[0]=="Status: OK")
    {
        //query complete
        if($cinfo[2]=="Action: None")
        {
            //no files to be downloaded
            
            $file_count=0;
            
            $file_list="None";
            
            $sql="Insert into `query_logs` (`status`,`message`,`action`,`date`,`time`,`session_id`,`num_files`) values ('$cinfo[0]','$cinfo[1]','No new files to be imported','$date','$time','$sid','$file_count')";
            
            $dblink->query($sql) or
                die("Something went wrong with $sql<br>".$mysqli->error);
            
            return($file_list);
        }
        else
        {
            //there are files to download
            
            $temp=explode(":",$cinfo[1]); //temp[0] = "MSG: " ... temp[1] = beginning, of, list, of, files
            $file_list=explode(",",$temp[1]);
            $file_count=count($file_list);
            
            $sql="Insert into `query_logs` (`status`,`message`,`action`,`date`,`time`,`session_id`,`num_files`) values ('$cinfo[0]','$cinfo[2]','Files available for download','$date','$time','$sid','$file_count')";
            
            $dblink->query($sql) or
                die("Something went wrong with $sql<br>".$mysqli->error);
            
            foreach($file_list as $rawFilePath)
            {
                $pathArray=explode("/",$rawFilePath);
                $fileName =$pathArray[4];
                $sql="Insert into `queried_files` (`date`,`time`,`session_id`,`filename`) values ('$date','$time','$sid','$fileName')";

                $dblink->query($sql) or
                die("Something went wrong with $sql<br>".$mysqli->error);
            }
            
            return($file_list);
        }
    }
    elseif($cinfo[0]=="Status: ERROR")
    {
        //there was in error in the query request
        $file_list="ERROR";
        
        $file_count=-1;
        
        $sql="Insert into `query_logs` (`status`,`message`,`action`,`date`,`time`,`session_id`,`num_files`) values ('$cinfo[0]','$cinfo[1]','$cinfo[2]','$date','$time','$sid','$file_count')";
        
        $dblink->query($sql) or
            die("Something went wrong with $sql<br>".$mysqli->error);
        
        return($file_list);
    }
    
}

function request_files($username, $session_id, $file_list)
{
    $dblink=db_connect("document_management_system");
    
    foreach($file_list as $rawFilePath)
    {
        $pathArray=explode("/",$rawFilePath);
        $fileName =$pathArray[4];
        $fileNameSegments=explode("-",$fileName);
        $client_id = $fileNameSegments[0];
        $category = $fileNameSegments[1];
        $dateAndType= $fileNameSegments[2];
        
        $dateAndTypeSeparated = explode(".",$dateAndType);
        $date_created=$dateAndTypeSeparated[0];
        $filetype = $dateAndTypeSeparated[1];
        
        //copy the file
        $fileContent=download_file($session_id, $username, $fileName);
        $filesize=0;
        
        if($fileContent=="ERROR")
        {
            //file was too small 
            return -1;
        }
        
        if(!file_exists("/var/www/html/downloads/$fileName"))
        {
            $filesize=filesize("/var/www/html/temp_downloads/$fileName");
            
            $fp = fopen("/var/www/html/downloads/$fileName","wb");
            fwrite($fp,$fileContent);
            fclose($fp);
            if($fp==NULL)
            {
                return(-1);
            }

            $sql="Insert into `downloaded_files` (`filename`,`filetype`,`creation_date`,`client_id`,`file_category`,`file_size`,`isDuplicate`,`remote_path`, `local_path`) values ('$fileName','$filetype','$date_created','$client_id','$category','$filesize','FALSE','$rawFilePath','/var/www/html/downloads/$fileName')";

            $dblink->query($sql) or
                die("Something went wrong with $sql<br>".$mysqli->error);
        }
        else
        {
            //FILE ALREADY EXISTS... DO SOMETHING 
            $original= "/var/www/html/downloads/".$fileName;
            $duplicate= "/var/www/html/temp_downloads/".$fileName;
            
            //using md5sum to compare file contents before writing to file system
            $og_hash=hash_file('md5',$original);
            $new_hash=hash_file('md5',$duplicate);
            
            if($og_hash == $new_hash)
            {
                //files are exact duplicates, skip downloading this file
                continue;
            }
            else
            {
                //files have different contents.
                //still download the file but modify the name
                $filesize=filesize("/var/www/html/downloads/$newFileName");
                $newFileNameArray=explode(".",$fileName);
                $newFileName=$newFileNameArray[0]."_MODIFIED.".$newFileNameArray[1];
                $fp = fopen("/var/www/html/downloads/$newFileName","wb");
                fwrite($fp,$fileContent);
                fclose($fp);
                if($fp==NULL)
                {
                    return(-1);
                }
                $sql="Insert into `downloaded_files` (`filename`,`filetype`,`creation_date`,`client_id`,`file_category`,`file_size`,`isDuplicate`,`remote_path`, `local_path`) values ('$newFileName','$filetype','$date_created','$client_id','$category','$filesize','TRUE','$rawFilePath','/var/www/html/downloads/$fileName')";

                $dblink->query($sql) or
                    die("Something went wrong with $sql<br>".$mysqli->error);
            }
            
        }
        
    }
    /* this was for file verification but i haven't figured it out yet
    foreach($file_list as $rawFilePath)
    {
        $pathArray=explode("/",$rawFilePath);
        $fileName =$pathArray[4];
        if(!file_exists('/var/www/html/downloads/$fileName'))
        {
            
        }
    }
    */
    close_session($session_id);
    return 1;
}

function redirect ( $uri )
{ ?>
	
	<script type="text/javascript">
	<!--
	document.location.href="<?php echo $uri; ?>";
	-->
	</script>
<?php die;
}
?>