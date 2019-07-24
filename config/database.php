<?php
require 'constants.php';

$conn = new mysqli(DB_HOST,DB_USER);

if($conn->connect_error){
        die('Database error:'.$conn->connect_error);
}else {
//  echo ("Connected!!!...");
}
?>