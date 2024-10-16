<?php
// Include database connection
include 'db_connection.php';

// Handle form submission for paste creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create_paste') {
    // Retrieve form data
    $title = $_POST['title'];
    $content = $_POST['content'];
    $type = $_POST['type'];
    $category = strtoupper($_POST['category']); // Convert category to lowercase

    // Prepare and bind parameters
    $stmt = $conn->prepare("INSERT INTO Paste (random_id, title, content, type, category, link) VALUES (?, ?, ?, ?, ?, ?)");
    $random_id = generate_random_string(16);
    $link = generate_random_string(16); // Generate link
    $stmt->bind_param("ssssss", $random_id, $title, $content, $type, $category, $link);

    // Execute the statement
    if ($stmt->execute()) {
        // If it's a private paste, add the link to the redirection URL
        if ($type === 'PRIVATE') {
            header("Location: view_paste.php?id=" . $random_id . "&pair=" . $link);
        } else {
            // Redirect user to view the newly created paste
            header("Location: view_paste.php?id=" . $random_id);
        }
        exit(); // Ensure no further code execution after redirection
    } else {
        echo "Error: " . $conn->error;
    }

    // Close statement
    $stmt->close();
}

// Generate random string function
function generate_random_string($length) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Retrieve categories from the database
$sql = "SELECT DISTINCT category FROM Paste"; // Assuming 'Paste' is your table name
$result = $conn->query($sql);

// Initialize an array to store categories
$categories = array();

// Fetch categories
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
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
    <title>Create Paste</title>
    <style>
        /* CSS styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .nav-button {
            display: inline-block;
            margin: 0 5px 20px 5px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .nav-button:hover {
            background-color: #0056b3;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 150px;
        }
        .radio-group {
            margin-bottom: 20px;
        }
        .radio-group label {
            font-weight: normal;
            margin-right: 10px;
        }
        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="pastes.php" class="nav-button">View Pastes</a>
        <h1>Create Paste</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="action" value="create_paste">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter title" required>
            <label for="content">Content:</label>
            <textarea id="content" name="content" placeholder="Enter content" required></textarea>
            <div class="radio-group">
                <label>Type:</label>
                <label><input type="radio" name="type" value="PUBLIC" required> Public</label>
                <label><input type="radio" name="type" value="PRIVATE" required> Private</label>
                <label><input type="radio" name="type" value="UNLISTED" required> Unlisted</label>
            </div>
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" placeholder="Enter category" required>
            <small><em>Examples: <?php echo implode(', ', $categories); ?></em></small>
            <button type="submit">Create Paste</button>
        </form>
    </div>
</body>
</html>
