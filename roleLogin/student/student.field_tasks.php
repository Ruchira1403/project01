<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';

// Get student information
        $studentId = $_SESSION['userid'];
        $studentBatch = '';
        $batchRes = $conn->query("SELECT batch FROM users WHERE usersId = $studentId LIMIT 1");
        if ($batchRes && $batchRes->num_rows > 0) {
            $studentBatch = $batchRes->fetch_assoc()['batch'];
        }
        
        // Get field tasks for this student's batch
        $tasksRes = $conn->query("SELECT ft.*, u.usersName as instructorName 
            FROM field_tasks ft 
            JOIN users u ON ft.instructorId = u.usersId 
            WHERE ft.batch = '" . $conn->real_escape_string($studentBatch) . "' 
            ORDER BY ft.assignedDate DESC");
        
$tasks = [];
        if ($tasksRes && $tasksRes->num_rows > 0) {
            while ($task = $tasksRes->fetch_assoc()) {
        $tasks[] = $task;
    }
}

// Calculate statistics
$totalTasks = count($tasks);
$pendingTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'pending'; }));
$inProgressTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'in_progress'; }));
$completedTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'completed'; }));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Tasks - GeoSurvey</title>
    <style>
        body {
            margin-top: 150px;
            padding: 0;
            background-color: #f8fafc;
            margin-left: 260px;
            margin-right: 40px;
        }
        
        .main-content {
            padding: 20px;
            max-width: 1600px;
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
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9em;
        }
        
        .tasks-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .tasks-section h2 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .tasks-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .task-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        
        .task-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .task-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            flex: 1;
        }
        
        .task-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-in-progress {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .task-description {
            color: #718096;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .task-meta {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4a5568;
        }
        
        .meta-item span:first-child {
            color: #a0aec0;
        }
        
        .equipment-section {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .equipment-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        
        .equipment-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .equipment-tag {
            background: #e2e8f0;
            color: #4a5568;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .instructions-section {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            width: 100%;
        }
        
        .instructions-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        
        .instructions-text {
            color: #4a5568;
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .task-actions {
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
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
        
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }
            
            .tasks-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>📋 Field Tasks</h1>
            <p>View and manage your assigned field work tasks.</p>
        </div>
        
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $totalTasks; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo $pendingTasks; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔄</div>
                <div class="stat-number"><?php echo $inProgressTasks; ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $completedTasks; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        
        <div class="tasks-section">
            <h2>Your Field Tasks (<?php echo $totalTasks; ?>)</h2>
            
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <h3>No field tasks assigned yet</h3>
                    <p>Your instructors haven't assigned any field tasks to your batch yet.</p>
                    <?php if (!empty($studentBatch)): ?>
                        <p><strong>Your Batch:</strong> <?php echo htmlspecialchars($studentBatch); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="tasks-grid">
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card">
                            <div class="task-header">
                                <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                                <span class="task-status status-<?php echo str_replace('_', '-', $task['status']); ?>">
                                    <?php 
                                    $statusText = ucwords(str_replace('_', ' ', $task['status']));
                                    echo $statusText;
                                    ?>
                                </span>
                            </div>
                            
                            <div class="task-description">
                                <?php echo htmlspecialchars($task['description']); ?>
                            </div>
                            
                            <div class="task-meta">
                                <div class="meta-item">
                                    <span>📅</span>
                                    <span><?php echo date('M d, Y', strtotime($task['dueDate'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span>📍</span>
                                    <span><?php echo htmlspecialchars($task['location']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span>👨‍🏫</span>
                                    <span><?php echo htmlspecialchars($task['instructorName']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <span>📦</span>
                                    <span><?php echo htmlspecialchars($task['batch']); ?></span>
                                </div>
                            </div>
                            
                            <?php if ($task['requiredEquipment']): ?>
                                <div class="equipment-section">
                                    <div class="equipment-title">Required Equipment:</div>
                                    <div class="equipment-tags">
                                        <?php 
                                        $equipment = explode(',', $task['requiredEquipment']);
                                        foreach ($equipment as $item): 
                                            $item = trim($item);
                                        ?>
                                            <span class="equipment-tag"><?php echo htmlspecialchars($item); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($task['instructions']): ?>
                                <div class="instructions-section">
                                    <div class="instructions-title">Instructions:</div>
                                    <div class="instructions-text"><?php echo htmlspecialchars($task['instructions']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="task-actions">
                                <?php if ($task['pdfPath']): ?>
                                    <a href="<?php echo htmlspecialchars($task['pdfPath']); ?>" target="_blank" class="btn btn-primary">
                                        📄 View PDF
                                    </a>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
function viewTaskDetails(taskId) {
            // You can implement a modal or redirect to a detailed view
    alert('View task details for ID: ' + taskId);
}
</script>
</body>
</html>
