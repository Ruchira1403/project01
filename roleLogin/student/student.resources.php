<?php
session_start();
require_once 'topbar.php';
require_once 'sidebar.php';
require_once '../../includes/dbh.inc.php';

// Get student ID and batch from session
$studentId = $_SESSION['userid'];

// Get student's batch
$studentBatch = '';
$batchRes = $conn->query("SELECT batch FROM users WHERE usersId = $studentId LIMIT 1");
if ($batchRes && $batchRes->num_rows > 0) {
    $studentBatch = $batchRes->fetch_assoc()['batch'];
}

// Get resources accessible to this student
$resourcesQuery = "SELECT r.*, u.usersName as instructorName 
                   FROM resources r 
                   JOIN resource_access ra ON r.resourceId = ra.resourceId 
                   JOIN users u ON r.instructorId = u.usersId 
                   WHERE ra.studentId = ? AND r.status = 'active' 
                   ORDER BY r.uploadedAt DESC";
$resourcesStmt = $conn->prepare($resourcesQuery);

if ($resourcesStmt) {
    $resourcesStmt->bind_param("i", $studentId);
    $resourcesStmt->execute();
    $resourcesResult = $resourcesStmt->get_result();
    $resources = [];
    while ($row = $resourcesResult->fetch_assoc()) {
        $resources[] = $row;
    }
    $resourcesStmt->close();
} else {
    $resources = [];
}

// Get resources by batch (fallback for students without proper access records)
if (empty($resources) && !empty($studentBatch)) {
    $batchResourcesQuery = "SELECT r.*, u.usersName as instructorName 
                           FROM resources r 
                           JOIN users u ON r.instructorId = u.usersId 
                           WHERE r.batch = ? AND r.status = 'active' 
                           ORDER BY r.uploadedAt DESC";
    $batchStmt = $conn->prepare($batchResourcesQuery);
    
    if ($batchStmt) {
        $batchStmt->bind_param("s", $studentBatch);
        $batchStmt->execute();
        $batchResult = $batchStmt->get_result();
        while ($row = $batchResult->fetch_assoc()) {
            $resources[] = $row;
        }
        $batchStmt->close();
    }
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
            
            padding: 0;
            background-color: #f8fafc;
            margin-left: 260px;
            margin-top: 140px;
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
            margin-bottom: 10px;
        }
        
        .resource-instructor {
            font-size: 0.9em;
            color: #4a5568;
            margin-bottom: 15px;
        }
        
        .resource-actions {
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
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
        
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .filter-section h3 {
            margin: 0 0 15px 0;
            color: #2d3748;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-controls select {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .filter-controls select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .resources-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>📚 Resources</h1>
            <p>Access educational resources shared by your instructors</p>
        </div>
        
        <?php if (!empty($resources)): ?>
            <div class="filter-section">
                <h3>Filter Resources</h3>
                <div class="filter-controls">
                    <select id="semesterFilter" onchange="filterResources()">
                        <option value="">All Semesters</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="typeFilter" onchange="filterResources()">
                        <option value="">All File Types</option>
                        <option value="pdf">PDF Files</option>
                        <option value="dwg">DWG Files</option>
                    </select>
                    <select id="instructorFilter" onchange="filterResources()">
                        <option value="">All Instructors</option>
                        <?php 
                        $instructors = array_unique(array_column($resources, 'instructorName'));
                        foreach ($instructors as $instructor): 
                        ?>
                            <option value="<?php echo htmlspecialchars($instructor); ?>"><?php echo htmlspecialchars($instructor); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="resources-section">
            <h2>Available Resources (<?php echo count($resources); ?>)</h2>
            
            <?php if (empty($resources)): ?>
                <div class="empty-state">
                    <h3>No resources available</h3>
                    <p>Your instructors haven't shared any resources yet, or you may not have access to any resources for your batch.</p>
                    <?php if (!empty($studentBatch)): ?>
                        <p><strong>Your Batch:</strong> <?php echo htmlspecialchars($studentBatch); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="resources-grid" id="resourcesGrid">
                    <?php foreach ($resources as $resource): ?>
                        <div class="resource-card" 
                             data-semester="<?php echo $resource['semester']; ?>" 
                             data-type="<?php echo strtolower(pathinfo($resource['fileName'], PATHINFO_EXTENSION)); ?>"
                             data-instructor="<?php echo htmlspecialchars($resource['instructorName']); ?>">
                            
                            <div class="resource-header">
                                <h3 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                <span class="file-type-badge"><?php echo strtoupper(pathinfo($resource['fileName'], PATHINFO_EXTENSION)); ?></span>
                            </div>
                            
                            <?php if (!empty($resource['description'])): ?>
                                <div class="resource-description">
                                    <?php echo htmlspecialchars($resource['description']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="resource-instructor">
                                <strong>👨‍🏫 Instructor:</strong> <?php echo htmlspecialchars($resource['instructorName']); ?>
                            </div>
                            
                            <div class="resource-meta">
                                <span>📅 <?php echo date('M d, Y', strtotime($resource['uploadedAt'])); ?></span>
                                <span>📦 <?php echo htmlspecialchars($resource['batch']); ?></span>
                            </div>
                            
                            <div class="resource-meta">
                                <span>📊 <?php echo round($resource['fileSize'] / 1024 / 1024, 2); ?> MB</span>
                                <?php if (!empty($resource['semester'])): ?>
                                    <span>🎓 Semester <?php echo $resource['semester']; ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="resource-actions">
                                <a href="<?php echo htmlspecialchars($resource['filePath']); ?>" target="_blank" class="btn btn-primary">📖 View File</a>
                                <a href="<?php echo htmlspecialchars($resource['filePath']); ?>" download="<?php echo htmlspecialchars($resource['fileName']); ?>" class="btn btn-secondary">💾 Download</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function filterResources() {
            const semesterFilter = document.getElementById('semesterFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const instructorFilter = document.getElementById('instructorFilter').value;
            const resourceCards = document.querySelectorAll('.resource-card');
            
            resourceCards.forEach(card => {
                const semester = card.dataset.semester;
                const type = card.dataset.type;
                const instructor = card.dataset.instructor;
                
                let show = true;
                
                if (semesterFilter && semester !== semesterFilter) {
                    show = false;
                }
                
                if (typeFilter && type !== typeFilter) {
                    show = false;
                }
                
                if (instructorFilter && instructor !== instructorFilter) {
                    show = false;
                }
                
                card.style.display = show ? 'block' : 'none';
            });
            
            // Update count
            const visibleCards = document.querySelectorAll('.resource-card[style*="block"], .resource-card:not([style*="none"])');
            const countElement = document.querySelector('.resources-section h2');
            const originalText = countElement.textContent;
            const baseText = originalText.split('(')[0].trim();
            countElement.textContent = `${baseText} (${visibleCards.length})`;
        }
    </script>
</body>
</html>
