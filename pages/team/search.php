<?php 
   //database configuration
    $dbHost = 'localhost';
    $dbUsername = 'root';
    $dbPassword = '';
    $dbName = 'c9';
    
    //connect with the database
    $db = new mysqli($dbHost,$dbUsername,$dbPassword,$dbName);
    
    //get search term
    $searchTerm = $_GET['finds'];
    
    //get matched data from skills table
    $query = $db->query("SELECT * FROM clients WHERE username LIKE '%".$searchTerm."%' ORDER BY username ASC");
    while ($row = $query->fetch_assoc()) {
        $data[] = $row['username'];
    }
    
    //return json data
    echo json_encode($data);
?>