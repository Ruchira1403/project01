<?php
session_start();
require_once 'sidebar.php';
require_once 'topbar.php';
require_once '../../includes/dbh.inc.php';

// Get instructor ID from session
$instructorId = $_SESSION['userid'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_resource'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $batch = $_POST['batch'];
    $semester = (int)$_POST['semester'];
    
    // Handle file upload
    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/resources/';
        $allowedTypes = ['application/pdf', 'application/dwg', 'image/vnd.dwg', 'application/acad', 'application/x-acad', 'application/autocad_dwg', 'application/octet-stream'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        $fileInfo = $_FILES['resource_file'];
        $fileName = $fileInfo['name'];
        $fileSize = $fileInfo['size'];
        $fileType = $fileInfo['type'];
        $fileTmpName = $fileInfo['tmp_name'];
        
        // Get file extension for validation
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        
        // Validate file type (PDF or DWG) - more flexible validation
        $isValidType = false;
        
        // Check by MIME type
        if (in_array($fileType, $allowedTypes)) {
            $isValidType = true;
        }
        
        // Check by file extension
        if (in_array($fileExtension, ['pdf', 'dwg'])) {
            $isValidType = true;
        }
        
        // Additional check for PDF files (sometimes they have different MIME types)
        if ($fileExtension === 'pdf' && (strpos($fileType, 'pdf') !== false || $fileType === 'application/octet-stream')) {
            $isValidType = true;
        }
        
        if (!$isValidType) {
            echo '<script>alert("Only PDF and DWG files are allowed! File type: ' . $fileType . ', Extension: ' . $fileExtension . '"); window.history.back();</script>';
            exit();
        }
        
        // Validate file size
        if ($fileSize > $maxSize) {
            echo '<script>alert("File size must be less than 50MB!"); window.history.back();</script>';
            exit();
        }
        
        // Generate unique filename
        $uniqueFileName = 'resource_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueFileName;
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            // Insert resource into database
            $sql = "INSERT INTO resources (instructorId, title, description, fileName, filePath, fileType, fileSize, batch, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                echo '<script>alert("Database error: ' . $conn->error . '. Please run setup_resources_tables.php first!"); window.history.back();</script>';
                exit();
            }
            
            $stmt->bind_param("isssssiss", $instructorId, $title, $description, $fileName, $uploadPath, $fileType, $fileSize, $batch, $semester);
            
            if ($stmt->execute()) {
                $resourceId = $conn->insert_id;
                
                // Grant access to all students in the selected batch
                $studentsQuery = "SELECT usersId FROM users WHERE batch = ? AND usersRole = 'student'";
                $studentsStmt = $conn->prepare($studentsQuery);
                
                if ($studentsStmt) {
                    $studentsStmt->bind_param("s", $batch);
                    $studentsStmt->execute();
                    $studentsResult = $studentsStmt->get_result();
                    
                    while ($student = $studentsResult->fetch_assoc()) {
                        $accessSql = "INSERT INTO resource_access (resourceId, studentId) VALUES (?, ?)";
                        $accessStmt = $conn->prepare($accessSql);
                        if ($accessStmt) {
                            $accessStmt->bind_param("ii", $resourceId, $student['usersId']);
                            $accessStmt->execute();
                            $accessStmt->close();
                        }
                    }
                    
                    $studentsStmt->close();
                }
                echo '<script>alert("Resource uploaded successfully! All students in batch ' . htmlspecialchars($batch) . ' can now access this resource."); window.location.href="instructor.resources.php";</script>';
            } else {
                echo '<script>alert("Error saving resource to database. Please try again."); window.history.back();</script>';
            }
            
            $stmt->close();
        } else {
            echo '<script>alert("Error uploading file!"); window.history.back();</script>';
        }
    } else {
        // Check what the file error is
        $fileError = $_FILES['resource_file']['error'] ?? 'No file uploaded';
        echo '<script>alert("File upload error: ' . $fileError . '. Please select a valid PDF or DWG file!"); window.history.back();</script>';
    }
}

// Get all resources uploaded by this instructor
$resourcesQuery = "SELECT r.*, COUNT(ra.studentId) as studentCount 
                   FROM resources r 
                   LEFT JOIN resource_access ra ON r.resourceId = ra.resourceId 
                   WHERE r.instructorId = ? 
                   GROUP BY r.resourceId 
                   ORDER BY r.uploadedAt DESC";
$resourcesStmt = $conn->prepare($resourcesQuery);

if ($resourcesStmt) {
    $resourcesStmt->bind_param("i", $instructorId);
    $resourcesStmt->execute();
    $resourcesResult = $resourcesStmt->get_result();
    $resources = [];
    while ($row = $resourcesResult->fetch_assoc()) {
        $resources[] = $row;
    }
    $resourcesStmt->close();
} else {
    $resources = [];
    // Show error message if tables don't exist
    if (strpos($conn->error, "doesn't exist") !== false) {
        echo '<script>alert("Database tables not found. Please run setup_resources_tables.php first!");</script>';
    }
}

// Get available batches
$batchesQuery = "SELECT DISTINCT batch FROM users WHERE usersRole = 'student' AND batch IS NOT NULL ORDER BY batch DESC";
$batchesResult = $conn->query($batchesQuery);
$batches = [];
while ($row = $batchesResult->fetch_assoc()) {
    $batches[] = $row['batch'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources - GeoSurvey</title>
    <style>
        body {
           
            background-color: #f8fafc;
            margin-left: 250px;
            margin-top: 100px;
        }
        
        .main-content {
            padding: 20px;
            max-width: 1560px;
            
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .upload-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .upload-section h2 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        
        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
        }
        
        .resources-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .resources-section h2 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .resource-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .resource-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .resource-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .resource-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }
        
        .file-type-badge {
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
        }
        
        .resource-description {
            color: #718096;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .resource-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
            color: #a0aec0;
        }
        
        .resource-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-danger {
            background: #e53e3e;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c53030;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #4a5568;
        }
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .resources-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>📚 Resources</h1>
            <p>Upload and manage educational resources for your students</p>
        </div>
        
        <div class="upload-section">
            <h2>Upload New Resource</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Resource Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="batch">Target Batch *</label>
                        <select id="batch" name="batch" required>
                            <option value="">Select Batch</option>
                            <?php foreach ($batches as $batch): ?>
                                <option value="<?php echo htmlspecialchars($batch); ?>"><?php echo htmlspecialchars($batch); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester">
                            <option value="">Select Semester</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Brief description of the resource..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="resource_file">Upload File *</label>
                        <input type="file" id="resource_file" name="resource_file" accept=".pdf,.dwg" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; background: white;">
                        <p style="font-size: 0.9em; color: #a0aec0; margin-top: 5px;">Maximum file size: 50MB</p>
                    </div>
                </div>
                <button type="submit" name="upload_resource" class="upload-btn">Upload Resource</button>
            </form>
        </div>
        
        <div class="resources-section">
            <h2>Your Resources (<?php echo count($resources); ?>)</h2>
            
            <?php if (empty($resources)): ?>
                <div class="empty-state">
                    <h3>No resources uploaded yet</h3>
                    <p>Upload your first resource to get started!</p>
                </div>
            <?php else: ?>
                <div class="resources-grid">
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <h3 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                <span class="file-type-badge"><?php echo strtoupper($resource['fileType']); ?></span>
                            </div>
                            
                            <?php if (!empty($resource['description'])): ?>
                                <div class="resource-description">
                                    <?php echo htmlspecialchars($resource['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="resource-meta">
                                <span>📅 <?php echo date('M d, Y', strtotime($resource['uploadedAt'])); ?></span>
                                <span>👥 <?php echo $resource['studentCount']; ?> students</span>
                            </div>
                            
                            <div class="resource-meta">
                                <span>📦 <?php echo $resource['batch']; ?></span>
                                <span>📊 <?php echo round($resource['fileSize'] / 1024 / 1024, 2); ?> MB</span>
                            </div>
                            
                            <div class="resource-actions">
                                <a href="<?php echo htmlspecialchars($resource['filePath']); ?>" target="_blank" class="btn btn-primary">View File</a>
                                <button onclick="deleteResource(<?php echo $resource['resourceId']; ?>)" class="btn btn-danger">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function deleteResource(resourceId) {
            if (confirm('Are you sure you want to delete this resource? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_resource.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'resource_id';
                input.value = resourceId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // File upload preview
        document.getElementById('resource_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Create or update file info display
                let fileInfo = document.getElementById('file-info');
                if (!fileInfo) {
                    fileInfo = document.createElement('div');
                    fileInfo.id = 'file-info';
                    fileInfo.style.cssText = 'margin-top: 10px; padding: 10px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; color: #0c4a6e;';
                    e.target.parentNode.appendChild(fileInfo);
                }
                fileInfo.innerHTML = `
                    <p style="margin: 0; font-weight: 600;">✅ Selected: ${file.name}</p>
                    <p style="margin: 5px 0 0 0; font-size: 0.9em;">Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                `;
            }
        });
    </script>
</body>
</html>
