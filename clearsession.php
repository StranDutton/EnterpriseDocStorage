<?php
    include("functions.php");

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
?>