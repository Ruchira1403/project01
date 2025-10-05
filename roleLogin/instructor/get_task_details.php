<?php
session_start();
include_once '../../includes/dbh.inc.php';
// Set content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskId'])) {
    $taskId = (int)$_POST['taskId'];
    $instructorId = $_SESSION['userid'];
    
    // Debug logging
    error_log("Getting task details for taskId: $taskId, instructorId: $instructorId");
    
    // Get task details
    $taskRes = $conn->query("SELECT * FROM field_tasks WHERE taskId = $taskId AND instructorId = $instructorId");
    
    if ($taskRes && $taskRes->num_rows > 0) {
        $task = $taskRes->fetch_assoc();
        
        // Debug logging
        error_log("Task found: " . json_encode($task));
        
        // Return task data as JSON
        echo json_encode([
            'success' => true,
            'task' => [
                'title' => $task['title'],
                'description' => $task['description'],
                'location' => $task['location'],
                'batch' => $task['batch'],
                'semester' => $task['semester'],
                'assignedDate' => $task['assignedDate'],
                'dueDate' => $task['dueDate'],
                'status' => $task['status'],
                'requiredEquipment' => $task['requiredEquipment'],
                'instructions' => $task['instructions']
            ]
        ]);
    } else {
        error_log("Task not found for taskId: $taskId, instructorId: $instructorId");
        echo json_encode(['success' => false, 'message' => 'Task not found']);
    }
} else {
    error_log("Invalid request - method: " . $_SERVER['REQUEST_METHOD'] . ", taskId: " . ($_POST['taskId'] ?? 'not set'));
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
