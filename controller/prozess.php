<?php
session_start();
require 'config/database.php';   
$errors = array( );
$vornameUsers = " ";
$nameUsers = " ";
$emailUsers = " ";
$keyUsers = " ";

$datumheute = date('Y-m-d H:i:s')." <br/>";
//echo "Datum heute". $datumheute;
$regdatum = date('Y-m-d H:i:s')." <br/>";
//echo "Regdatum ".$regdatum;
$dateinfo = new DateTime();
$ablaufsdate = $dateinfo->add(new DateInterval("P1Y"))->format('Y-m-d H:i:s');
//echo "Ablaufdatum ".$ablaufsdate;
$spass = "$?!Pimmel%sa";
//Mac adresse auslesen
$obj = new COM('winmgmts://localhost/root/CIMV2' ); 
$NetworkAdapterConfiguration =  $obj->ExecQuery("Select * from Win32_NetworkAdapterConfiguration WHERE IPEnabled = 'True'");

    foreach ($NetworkAdapterConfiguration as $wmi_NetworkAdapterConfiguration )
    {
            $MACAddress = $wmi_NetworkAdapterConfiguration->MACAddress;
    }
//echo " MACAddress --> ".$MACAddress." <br/>";
//Click auf Register-Button
if(isset($_POST['signup-btn'])){
        $vornameUsers = $_POST['vornameUsers'];
        $nameUsers = $_POST['nameUsers'];
        $emailUsers = $_POST['emailUsers'];
        
//Validation für Register-Form
    if(empty($vornameUsers)){
        array_push($errors," Benutzername fehlt !");
    }
    if(empty($nameUsers)){
        array_push($errors," Password fehlt !");
    }
    if(empty($emailUsers)){
        array_push($errors," Email fehlt !");
    }
    if(!filter_var($emailUsers, FILTER_VALIDATE_EMAIL)){
        array_push($errors," Email nicht korrekt !");
    }
    //Überprüfung, ob der Email-Adresse bereits registriert ist 
    $emailQuery = "SELECT * FROM loginsystem.users WHERE emailUsers=? LIMIT 1";
        $stmt = $conn->prepare($emailQuery);
        $stmt->bind_param('s',$emailUsers);
        $stmt->execute();
        $result = $stmt->get_result();
        $userCount = $result->num_rows;
        $stmt->close();

    if($userCount > 0){
      //  $erorrs['emailUsers'] = "Email-Adresse bereits registriert!";
      echo  "Email-Adresse bereits registriert!";
    }
    if(count($errors) == 0){
    $keyUsers = strtoupper(dechex(crc32(crypt($vornameUsers,$spass)))."-"
                .dechex(crc32(crypt($nameUsers,$spass)))."-"
                .dechex(crc32(crypt($emailUsers,$spass))));
     //echo $keyUsers;
        $sql = "INSERT INTO loginsystem.users (vornameUsers,nameUsers,emailUsers,keyUsers,macUsers,registriert_am,ablaufsdatum) VALUES (?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === FALSE) {
               echo "Fehler beim Statment Insert!";
        }
        $stmt->bind_param('sssssss',$vornameUsers,$nameUsers,$emailUsers,$keyUsers,$MACAddress,$regdatum,$ablaufsdate);
        if ($stmt->execute()){
             $user_id = $conn->insert_id;
             $_SESSION['idUsers'] = $user_id; 
             $_SESSION['vornameUsers'] = $vornameUsers;
             $_SESSION['nameUsers'] = $nameUsers;
             $_SESSION['emailUsers'] = $emailUsers;   
             $_SESSION['keyUsers']= $keyUsers;
             $_SESSION['macUsers']= $MACAddress;
             $_SESSION['registriert_am']= $regdatum;
             $_SESSION['ablaufsdatum']= $ablaufsdate;
        }
    }
}   
     //Validation für Login-Form
    if(isset($_POST['login-btn'])){
     $vornameUsers=$_POST['vornameUsers'];
     $nameUsers=$_POST['nameUsers'];
     $vornameUsers=stripslashes($vornameUsers);
     $nameUsers=stripslashes($nameUsers);
     $vornameUsers=mysqli_real_escape_string($conn,$vornameUsers);
     $nameUsers=mysqli_real_escape_string($conn,$nameUsers);
    if(empty($vornameUsers)){
        array_push($errors," Benutzername fehlt !");
    }
    if(empty($nameUsers)){
        array_push($errors," Password fehlt !");
    }
    $result_login = mysqli_query($conn,"SELECT * FROM loginsystem.users WHERE vornameUsers='$vornameUsers' AND nameUsers='$nameUsers' LIMIT 1");
    // $result_lizenz = mysqli_query($conn,"SELECT keyUsers,registriert_am FROM loginsystem.users WHERE registriert_am >= DATE_SUB(NOW(), INTERVAL 365 DAY)" ); 
       // prüft ob Registration älter als 1 Jahr ist 
    $result_lizenz = "SELECT keyUsers,registriert_am FROM loginsystem.users WHERE registriert_am >= DATE_SUB(NOW(), INTERVAL 365 DAY)";

    // $reg = mysqli_query($conn, "SELECT * FROM loginsystem.users WHERE 
    // keyUsers='$keyUsers'");

    // $mac = mysqli_query($conn, "SELECT * FROM loginsystem.users WHERE 
    // macUsers='$MACAddress'");
    
    // $regdatum = "SELECT vornameUsers,registriert_am FROM loginsystem.users WHERE registriert_am >= '2019-01-01 00:00:00'";

    $row = mysqli_fetch_array($result_login);
    //$row1 = mysqli_fetch_array($reg);
    //$row2 = mysqli_fetch_array($enddate);
    $result = mysqli_query($conn,$result_lizenz);
    if($row['vornameUsers'] == $vornameUsers && $row['nameUsers'] == $nameUsers){
        echo "".$row['vornameUsers']." ist in der Datenbank registriert ! ";    
        $_SESSION['vornameUsers'] = $_POST['vornameUsers'];
        $_SESSION['eingeloggt'] = true;
    //echo "<b>einloggen erfolgreich</b>";
    
    }else{
        $_SESSION['eingeloggt'] = false;
        echo "Die Logindaten sind nicht korrekt! Überprüfen Sie Ihre 'Benutzer/Passwort'- Daten!";
    }
    if (isset($_SESSION['eingeloggt']) AND $_SESSION['eingeloggt'] == true){
    // Benutzer begruessen
    echo "<br>"."<h1>Hallo ". $_SESSION['vornameUsers'] . "</h1>" ."REGISTRIERT AM ".$row["registriert_am"]."<br>" ;

    }else {
        echo "<br>"."Benutzer ist nicht registriert"."<br>";
    }
    // if (mysqli_num_rows($result)) {
    //     // Lesen jede Zeile
    //     while($row1 = $result->fetch_assoc()) {
    //         lizenzabgelaufen($row1["registriert_am"],row1[]);
    //         echo " REG-Key: " . $row1["keyUsers"]." registriert am: ".$row1["registriert_am"].  "<br>";
    //     }
    //     mysqli_free_result($result);
    // } else {
    //     echo "0 results";
    // }
    
    function lizenzabgelaufen(DateTime $regdatum, DateInterval $interval)
    {
      $now = new DateTime();
      $ablaufsdate = clone $regdatum;
      $ablaufsdate->add($interval);

      return $now > $ablaufsdate;
    }

}
    // if($row1['keyUsers'] == $keyUsers){
    //     echo "Bitte geben Sie den richtigen Schlüssel neu ein. ";
    // }else{
    //     echo " ".$row1['keyUsers']. " Der in der Registry vorhandene Schlüssel ist falsch !"; 
    // }
    // if($row2['registriert_am'] <= '2017-01-01 00:00:00'){
    //     echo  "Lizenz von".$row['vornameUsers']. " ist ungültig!";
    // }
    // if($row1['keyUsers'] != $keyUsers){
    //     echo " ".$row1['keyUsers']. " Der in der Registry vorhandene Schlüssel ist falsch !"; 
    // }else{
    //     echo "Bitte geben Sie den richtigen Schlüssel neu ein. ";
    // }
    // && $row['keyUsers'] == $keyUsers && $row['macUsers'] == $MACAddress
    ?>
<!--     
    <?php 
    @session_start();
    $userRegDate = ['registriet_am'];
    $MembershipEnds = date("Y-m-d",strtotime(date("Y-m-d",strtotime($userRegDate)). "365 Day"));
    ?> -->