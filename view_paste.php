<?php
// Include database connection
include 'db_connection.php';

// Initialize variables
$title = "Paste not found";
$content = "The paste you are trying to view does not exist, may have been deleted, or is not found.";
$view_count = 0;
$showContent = false; // Variable to determine whether content should be shown
$pasteLink = ""; // Variable to store the link of the paste

// Retrieve paste ID from URL parameter
if(isset($_GET['id']) && $_GET['id'] != '') {
    $random_id = $_GET['id'];

    // Check if pairing key is provided
    if(isset($_GET['pair']) && $_GET['pair'] != '') {
        $pairing_key = $_GET['pair'];

        // Retrieve paste from database based on random_id
        $sql = "SELECT * FROM Paste WHERE random_id='$random_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $title = $row["title"];
            $content = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], htmlspecialchars($row["content"]));
            $view_count = $row["views"];

            // Check if device has already viewed the paste
            if (!isset($_COOKIE[$random_id])) {
                // Increment view count
                $view_count++;

                // Set cookie to prevent additional views from the same device
                setcookie($random_id, true, time() + (86400 * 30), "/"); // 30 days expiration

                // Update view count in the database
                $update_sql = "UPDATE Paste SET views=$view_count WHERE random_id='$random_id'";
                $conn->query($update_sql);

                // Check privacy settings
                if ($row['type'] === 'PUBLIC' || ($row['type'] === 'UNLISTED' && $pairing_key === $row['link'])) {
                    // Public or unlisted paste, no need for pairing key
                    $showContent = true;
                } elseif ($row['type'] === 'PRIVATE' && $pairing_key === $row['link']) {
                    // Private paste with matching pairing key
                    $showContent = true;
                }
            } else {
                // Device has already viewed the paste
                $showContent = true;
            }

            // Construct paste link
            $pasteLink = "view_paste.php?id=" . $random_id;
            if ($pairing_key !== '') {
                $pasteLink .= "&pair=" . $pairing_key;
            }
        }
    } else {
        // No pairing key provided, check for public paste
        $sql = "SELECT * FROM Paste WHERE random_id='$random_id' AND type='PUBLIC'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $title = $row["title"];
            $content = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], htmlspecialchars($row["content"]));
            $view_count = $row["views"];

            // Check if device has already viewed the paste
            if (!isset($_COOKIE[$random_id])) {
                // Increment view count
                $view_count++;

                // Set cookie to prevent additional views from the same device
                setcookie($random_id, true, time() + (86400 * 30), "/"); // 30 days expiration

                // Update view count in the database
                $update_sql = "UPDATE Paste SET views=$view_count WHERE random_id='$random_id'";
                $conn->query($update_sql);

                // Public paste found
                $showContent = true;
            } else {
                // Device has already viewed the paste
                $showContent = true;
            }

            // Construct paste link
            $pasteLink = "view_paste.php?id=" . $random_id;
        }
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
    <title><?php echo $title; ?></title>
    <style>
        /* CSS styles here */
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $title; ?></h1>
        <?php if ($showContent) : ?>
            <!-- Show content if allowed -->
            <pre id="paste-content"><?php echo $content; ?></pre>
            <!-- Copy buttons -->
            <button onclick="copyText()">Copy Text</button>
            <button onclick="copyLink()">Copy Link</button>
        <?php else : ?>
            <!-- Show message if not allowed -->
            <p><?php echo $content; ?></p>
        <?php endif; ?>
        <div class="views-count">Views: <?php echo $view_count; ?></div>
    </div>

    <script>
        function copyText() {
            var textArea = document.createElement("textarea");
            textArea.value = document.getElementById("paste-content").innerText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            alert("Text copied to clipboard");
        }

        function copyLink() {
            var textArea = document.createElement("textarea");
            textArea.value = "<?php echo $pasteLink; ?>";
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            alert("Link copied to clipboard");
        }
    </script>
</body>
</html>
