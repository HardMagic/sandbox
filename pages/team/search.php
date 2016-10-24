<?php 
   //database configuration
    $dbHost = 'localhost';
    $dbUsername = 'root';
    $dbPassword = '';
    $dbName = 'c9';
    
    //connect with the database
    $db = new mysqli($dbHost,$dbUsername,$dbPassword,$dbNamte);
    
    //get search term
    $searchTerm = $_GET['term'];
    
    //get matched data from skills table
    $query = $db->query("SELECT username FROM clients WHERE username LIKE '%".$searchTerm."%' ORDER BY fname ASC");
    while ($row = $query->fetch_assoc()) {
        $data[] = $row['username'];
    }
    
    //return json data
    echo json_encode($data);
?>