<?php 
   //database configuration
    $dbHost = 'localhost';
    $dbUsername = 'root';
    $dbPassword = '';
    $dbName = 'u';
    
    //connect with the database
    $db = new mysqli($dbHost,$dbUsername,$dbPassword,$dbNamte);
    
    //get search term
    $searchTerm = $_GET['term'];
    
    //get matched data from skills table
    $query = $db->query("SELECT * FROM u WHERE fname LIKE '%".$searchTerm."%' ORDER BY fname ASC");
    while ($row = $query->fetch_assoc()) {
        $data[] = $row['fname'];
    }
    
    //return json data
    echo json_encode($data);
?>