<?php 
   session_start();

  
 $con = mysqli_connect("localhost","root","","tribal_arts_db") or die("Couldn't connect");


   if(!isset($_SESSION['valid'])){
    echo "Run";
   }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=`, initial-scale=1.0">
    <title>Fetch Data</title>
    <style>
        .card_container {
        display: flex;
        justify-content: flex-start;
        margin-left: 5%;
        margin-top: -1%;
        z-index: -1;
        flex-direction: column;
    }

    </style>
</head>

<body>
    <?php
    // Fetch data from the database
    $query = "SELECT * FROM admin";
    $result = $con->query($query);

    $guideData = []; // Array to store guide data
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $guideData[] = $row; // Store each row in the array
        }
    } else {
        echo "No results found.";
    }

    // Close the connection
    $con->close();
    ?>

    <div class="card_container">
        <?php if (!empty($guideData)): ?>
        <?php foreach ($guideData as $guide): ?>
        <div class="trek_info">
            <h3>Guide name:
                <?php echo $guide["username"]; ?>
            </h3>
            <h4>Contact number: +91
                <?php echo $guide["password"]; ?>
            </h4>
           
            <p style="color: red;">*Go on images for location</p>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>No guide data found.</p>
        <?php endif; ?>
    </div>

</body>

</html>