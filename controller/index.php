<?php  
session_start();
if(!isset($_SESSION['eingeloggt'])){
    $_SESSION['redirectURL'] = $_SERVER['REQUEST_URI'];
  // echo $_SERVER['REQUEST_URI'];
     header('location:prozess.php');
}
?>

<h1>Home PAGE</h1>