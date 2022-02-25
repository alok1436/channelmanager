<?php
    // $servername = "localhost";
    // $username   = "root";
    // $password   = "";
    // $dbname     = "coffee";
    $servername = "localhost";
    $username   = "channeli_root";
    $password   = "YL9-b(8vP1*X";
    $dbname     = "channeli_coffee";
    $siteUrl    = "http://channelister.com/newchannelmanager/public";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>