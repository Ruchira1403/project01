<?php
require_once 'includes/dbh.inc.php';

echo "<h2>Setting up Resources Tables...</h2>";

// Create resources table
$createResourcesTable = "
CREATE TABLE IF NOT EXISTS resources (
    resourceId INT AUTO_INCREMENT PRIMARY KEY,
    instructorId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    fileName VARCHAR(255) NOT NULL,
    filePath VARCHAR(500) NOT NULL,
    fileType VARCHAR(50) NOT NULL,
    fileSize INT NOT NULL,
    batch VARCHAR(50) NOT NULL,
    semester INT,
    uploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (instructorId) REFERENCES users(usersId) ON DELETE CASCADE
)";

if ($conn->query($createResourcesTable) === TRUE) {
    echo "<p style='color: green;'>✅ Resources table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating resources table: " . $conn->error . "</p>";
}

// Create resource_access table
$createResourceAccessTable = "
CREATE TABLE IF NOT EXISTS resource_access (
    accessId INT AUTO_INCREMENT PRIMARY KEY,
    resourceId INT NOT NULL,
    studentId INT NOT NULL,
    grantedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resourceId) REFERENCES resources(resourceId) ON DELETE CASCADE,
    FOREIGN KEY (studentId) REFERENCES users(usersId) ON DELETE CASCADE,
    UNIQUE KEY unique_access (resourceId, studentId)
)";

if ($conn->query($createResourceAccessTable) === TRUE) {
    echo "<p style='color: green;'>✅ Resource access table created successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating resource access table: " . $conn->error . "</p>";
}

// Create uploads directory
$uploadDir = 'uploads/resources/';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "<p style='color: green;'>✅ Uploads directory created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating uploads directory</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Uploads directory already exists</p>";
}

echo "<h3>Setup completed! You can now use the resources feature.</h3>";
echo "<p><a href='roleLogin/instructor/instructor.resources.php'>Go to Resources Page</a></p>";
?>
