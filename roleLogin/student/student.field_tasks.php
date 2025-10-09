<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';
?>
<link rel="stylesheet" href="student.submissions.css">

<div class="main-content">
    <div class="submissions-header">
        <h1>Field Tasks</h1>
        <p>View and manage your assigned field work tasks.</p>
    </div>
    
    <!-- View Options -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; border-bottom: 1px solid #e5e7eb;">
            <button id="listViewBtn" onclick="switchView('list')" style="padding: 8px 16px; border: none; background: #2563eb; color: white; border-radius: 4px 4px 0 0; cursor: pointer;">List View</button>
        </div>
    </div>

    <!-- List View -->
    <div id="listView" class="view-content">
        <?php
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
        
        if ($tasksRes && $tasksRes->num_rows > 0) {
            while ($task = $tasksRes->fetch_assoc()) {
                $statusColor = '';
                $statusText = '';
                switch ($task['status']) {
                    case 'pending':
                        $statusColor = '#f59e0b';
                        $statusText = 'Pending';
                        break;
                    case 'in_progress':
                        $statusColor = '#3b82f6';
                        $statusText = 'In Progress';
                        break;
                    case 'completed':
                        $statusColor = '#10b981';
                        $statusText = 'Completed';
                        break;
                    case 'cancelled':
                        $statusColor = '#ef4444';
                        $statusText = 'Cancelled';
                        break;
                }
                
                echo '<div class="task-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">';
                echo '<div style="flex: 1;">';
                echo '<h4 style="margin: 0 0 8px 0; color: #1f2937; font-size: 18px;">' . htmlspecialchars($task['title']) . '</h4>';
                echo '<p style="margin: 0 0 12px 0; color: #6b7280; line-height: 1.5;">' . htmlspecialchars($task['description']) . '</p>';
                echo '</div>';
                echo '<div style="text-align: right;">';
                echo '<span style="background: ' . $statusColor . '; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase;">' . $statusText . '</span>';
                echo '</div>';
                echo '</div>';
                
                echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">';
                echo '<div><strong>Date:</strong> ' . date('M j, Y', strtotime($task['dueDate'])) . '</div>';
                echo '<div><strong>Location:</strong> ' . htmlspecialchars($task['location']) . ' </div>';
                echo '<div><strong>Instructor:</strong> ' . htmlspecialchars($task['instructorName']) . '</div>';
                echo '</div>';
                
                if ($task['requiredEquipment']) {
                    echo '<div style="margin-bottom: 16px;">';
                    echo '<strong>Required Equipment:</strong><br>';
                    $equipment = explode(',', $task['requiredEquipment']);
                    foreach ($equipment as $item) {
                        $item = trim($item);
                        if (strpos($item, '(Qty:') !== false) {
                            // Equipment with quantity
                            echo '<span style="display: inline-block; background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; margin: 2px; font-size: 12px; font-weight: 500;">' . htmlspecialchars($item) . '</span>';
                        } else {
                            // Equipment without quantity
                            echo '<span style="display: inline-block; background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 4px; margin: 2px; font-size: 12px;">' . htmlspecialchars($item) . '</span>';
                        }
                    }
                    echo '</div>';
                }
                
                if ($task['instructions']) {
                    echo '<div style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border-radius: 6px;">';
                    echo '<strong>Instructions:</strong><br>';
                    echo '<span style="color: #374151;">' . htmlspecialchars($task['instructions']) . '</span>';
                    echo '</div>';
                }
                
                if ($task['pdfPath']) {
                    echo '<div style="margin-bottom: 16px;">';
                    echo '<strong>📄 Task Document:</strong><br>';
                    echo '<a href="' . htmlspecialchars($task['pdfPath']) . '" target="_blank" style="display: inline-flex; align-items: center; background: #dc2626; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-top: 8px;">';
                    echo '📄 Download PDF</a>';
                    echo '</div>';
                }
                
                echo '<div style="display: flex; gap: 8px;">';
                echo '';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div style="text-align: center; padding: 40px; color: #6b7280;">No field tasks assigned to your batch yet.</div>';
        }
        ?>
    </div>

<script>
function switchView(view) {
    // Hide all views
    document.getElementById('listView').style.display = 'none';
    
    // Reset button styles
    document.getElementById('listViewBtn').style.background = '#f3f4f6';
    document.getElementById('listViewBtn').style.color = '#374151';
    document.getElementById('calendarViewBtn').style.background = '#f3f4f6';
    document.getElementById('calendarViewBtn').style.color = '#374151';
    document.getElementById('mapViewBtn').style.background = '#f3f4f6';
    document.getElementById('mapViewBtn').style.color = '#374151';
    
    // Show selected view
    if (view === 'list') {
        document.getElementById('listView').style.display = 'block';
        document.getElementById('listViewBtn').style.background = '#2563eb';
        document.getElementById('listViewBtn').style.color = 'white';
    } else if (view === 'calendar') {
        document.getElementById('calendarView').style.display = 'block';
        document.getElementById('calendarViewBtn').style.background = '#2563eb';
        document.getElementById('calendarViewBtn').style.color = 'white';
    } else if (view === 'map') {
        document.getElementById('mapView').style.display = 'block';
        document.getElementById('mapViewBtn').style.background = '#2563eb';
        document.getElementById('mapViewBtn').style.color = 'white';
    }
}

function viewTaskDetails(taskId) {
    alert('View task details for ID: ' + taskId);
}
</script>
