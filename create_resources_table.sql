-- Create resources table for storing instructor uploaded files
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
);

-- Create resource_access table to track which students can access which resources
CREATE TABLE IF NOT EXISTS resource_access (
    accessId INT AUTO_INCREMENT PRIMARY KEY,
    resourceId INT NOT NULL,
    studentId INT NOT NULL,
    grantedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resourceId) REFERENCES resources(resourceId) ON DELETE CASCADE,
    FOREIGN KEY (studentId) REFERENCES users(usersId) ON DELETE CASCADE,
    UNIQUE KEY unique_access (resourceId, studentId)
);
