<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bestcare";


//create connection
$conn = mysqli_connect($servername,$username,$password,$dbname);

//check connection
if(!$conn){
    die("connection faild".mysqli_connect_error());
}
// echo "Connected successfully";
?>