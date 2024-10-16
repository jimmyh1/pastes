<?php
// Include database connection
include 'db_connection.php';

// Retrieve all private pastes from the database, sorted by ID in descending order
$sql = "SELECT * FROM Paste WHERE type='PUBLIC' ORDER BY id DESC";
$result = $conn->query($sql);

// Initialize an array to store paste data
$pastes = array();

// Fetch paste data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pastes[] = $row;
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Public Pastes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .create-button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .create-button:hover {
            background-color: #0056b3;
        }
        .paste-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .paste-item:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>List of Public Pastes</h1>
        <a href="index.php" class="create-button">Create Paste</a>
        <?php foreach ($pastes as $paste) : ?>
            <div class="paste-item" onclick="viewPaste('<?php echo $paste['random_id']; ?>')">
                <h2><?php echo htmlspecialchars($paste['title']); ?></h2>
                <p><?php echo htmlspecialchars(substr($paste['content'], 0, 100)); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function viewPaste(id) {
            console.log("Viewing paste with ID: " + id); // Debugging message
            window.location = "view_paste.php?id=" + id;
        }
    </script>
</body>
</html>
