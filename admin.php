<?php
// Include database connection
include 'db_connection.php';

// Initialize arrays to store paste data and categories
$pastes = [];
$categories = [];

// Retrieve all distinct categories
$sql_categories = "SELECT DISTINCT category FROM Paste";
$result_categories = $conn->query($sql_categories);

// Fetch categories
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Default category filter
$category_filter = 'ALL';

// Check if form is submitted and get category filter
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['category'])) {
    $category_filter = $_GET['category'];
}

// Sanitize input to prevent SQL injection
$category_filter = mysqli_real_escape_string($conn, $category_filter);

// Retrieve pastes based on selected category
$sql = "SELECT * FROM Paste";
if ($category_filter !== 'ALL') {
    $sql .= " WHERE category = '$category_filter'";
}
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

// Fetch paste data and calculate total lines
$total_lines = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pastes[] = $row;
        $total_lines += substr_count($row['content'], "\n") + 1; // Adding 1 for the last line without newline
    }
}

// Count the number of pastes found
$num_pastes = count($pastes);

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - View All Pastes</title>
    <style>
        /* CSS styles here */
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
        .paste-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: block;
            text-decoration: none;
            color: #333;
        }
        .paste-item:hover {
            background-color: #f5f5f5;
        }
        .filter-form {
            text-align: center;
            margin-bottom: 20px;
        }
        .summary {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel - View All Pastes</h1>
        
        <!-- Filter Form -->
        <form method="get" class="filter-form">
            <label for="category">Filter by Category:</label>
            <select name="category" id="category">
                <option value="ALL">All</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php if ($category_filter === $category) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter</button>
        </form>

        <!-- Summary Information -->
        <div class="summary">
            <p>Number of pastes found: <?php echo $num_pastes; ?></p>
            <p>Total number of lines: <?php echo $total_lines; ?></p>
        </div>

        <!-- Display Pastes -->
        <?php foreach ($pastes as $paste) : ?>
            <?php
                $pasteLink = "view_paste.php?id=" . $paste['random_id'];
                if ($paste['type'] === 'PRIVATE') {
                    $pasteLink .= "&pair=" . $paste['link'];
                }
            ?>
            <a href="<?php echo $pasteLink; ?>" class="paste-item">
                <div>
                    <h2><?php echo htmlspecialchars($paste['title']); ?></h2>
                    <p><?php echo htmlspecialchars(substr($paste['content'], 0, 100)); ?></p>
                    <p>Type: <?php echo htmlspecialchars($paste['type']); ?></p>
                    <p>Category: <?php echo htmlspecialchars($paste['category']); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</body>
</html>
