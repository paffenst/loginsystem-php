<?php
session_start();
require 'config/database.php';   
$errors = array( );

$vornameUsers = " ";
$nameUsers = " ";
$emailUsers = " ";
$keyUsers = " ";

$datumheute = date('Y-m-d H:i:s')."<br/>";
//echo "Datum heute: ".$datumheute;
$regdatum = date('Y-m-d H:i:s')."<br/>";
//echo "RegistrierungsDatum: ".$regdatum;
$dateinfo = new DateTime();
$ablaufsdatum = $dateinfo->add(new DateInterval('P1Y'))->format('Y-m-d H:i:s')."<br/>";
//echo "AblaufsDatum: ".$ablaufsdatum;
//Sicherheitsword
$spass = "$?!Pimmel%sa";
//Mac adresse auslesen
$obj = new COM ( 'winmgmts://localhost/root/CIMV2' ); 
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
        array_push($errors," E-mail fehlt !");
    }
    if(!filter_var($emailUsers, FILTER_VALIDATE_EMAIL)){
        array_push($errors," E-mail nicht korrekt !");
    } 
    //Überprüfung, ob der Email-Adresse bereits registriert ist 
    $emailQuery = "SELECT * FROM loginsystem.users WHERE emailUsers=? LIMIT 1";

        $stmt = $conn->prepare($emailQuery);
        $stmt->bind_param('s',$emailUsers);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
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
        $stmt->bind_param('sssssss',$vornameUsers,$nameUsers,$emailUsers,$keyUsers,$MACAddress,$regdatum,$ablaufsdatum);
        if ($stmt->execute()){
             $user_id = $conn->insert_id;
             $_SESSION['idUsers'] = $user_id; 
             $_SESSION['vornameUsers'] = $vornameUsers;
             $_SESSION['nameUsers'] = $nameUsers;
             $_SESSION['emailUsers'] = $emailUsers;   
             $_SESSION['keyUsers']= $keyUsers;
             $_SESSION['macUsers']= $MACAddress;
             $_SESSION['registriert_am']= $regdatum;
             $_SESSION['ablaufsdatum']= $ablaufsdatum;

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

    // $result_lizenz = mysqli_query($conn,"SELECT keyUsers,registriert_am FROM loginsystem.users WHERE registriert_am >= DATE_SUB($regdatum, INTERVAL 365 DAY)" ); 
    // $reg = mysqli_query($conn, "SELECT * FROM loginsystem.users WHERE 
    // keyUsers='$keyUsers'");
    // $mac = mysqli_query($conn, "SELECT * FROM loginsystem.users WHERE 
    // macUsers='$MACAddress'");
    // $regdatum = "SELECT vornameUsers,registriert_am FROM loginsystem.users WHERE registriert_am >= '2019-01-01 00:00:00'";

    $row = mysqli_fetch_array($result_login);
    //$row1 = mysqli_fetch_array($reg);
    //$row2 = mysqli_fetch_array($enddate);
  
    // $result_lizenz = "SELECT keyUsers,registriert_am FROM loginsystem.users WHERE registriert_am >= DATE_SUB( NOW(), INTERVAL 365 DAY)";
    if($datumheute>$row['ablaufsdatum']){
        echo "Lizenz ist abgelaufen!!!"."<br>";
    }else{
        echo "Lizenz ist aktiv!"."<br>";
    }
    //$result = mysqli_query($conn,$result_lizenz);
    if($row['vornameUsers'] == $vornameUsers && $row['nameUsers'] == $nameUsers){
        echo "".$row['vornameUsers']." ist in der Datenbank registriert ! ";    
        $_SESSION['vornameUsers'] = $_POST['vornameUsers'];
        $_SESSION['eingeloggt'] = true;
    //echo "<b>einloggen erfolgreich</b>";
    } else{
        $_SESSION['eingeloggt'] = false;
        echo "Die Logindaten sind nicht korrekt! Überprüfen Sie Ihre 'Benutzer/Passwort'- Daten!";
    }
    if (isset($_SESSION['eingeloggt']) AND $_SESSION['eingeloggt'] == true ){
    // Benutzer begruessen
    echo "<br>"."<h3>Hallo ". $_SESSION['vornameUsers'] . "</h3>" ."REGISTRIERT AM: ".$row['registriert_am']."<br>" ."Lizenzablaufsdatum ist: ".$row['ablaufsdatum']."<br>" ;
    } else {
        echo "<br>"."Benutzer ist nicht registriert"."<br>";
    }
    //var_dump($ablaufsdatum);
    // Zeigt die REG-Key 
    // if (mysqli_num_rows($result)) {
    //     // Lesen jede Zeile
    //     while($row = $result->fetch_assoc()) {
    //         echo " REG-Key: " . $row["keyUsers"]." registriert am: ".$row["registriert_am"].  "<br>";
    //     }
    //     mysqli_free_result($result);
    // } else {
    //     echo "0 results";
    // }
    // function linenzCheck($regdatum,$ablaufsdatum){
    //     if($regdatum<=$ablaufsdatum){
    //         echo "Lizenz ist abgelaugen!!! Bitte erneuern";
    //     }else{
    //         echo "Lizenz ist aktiv !!!";

    //     }
    // }
}