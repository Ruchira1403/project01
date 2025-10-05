<?php
session_start();
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="instructor.home.css">

<div class="main-content">
    <div class="dashboard-header">
        <h1>Field Tasks Management</h1>
        <p>Create and manage field work tasks for your students.</p>
    </div>

    <!-- Action Buttons -->
    <div style="margin-bottom: 24px; display: flex; gap: 12px;">
        <button onclick="openCreateTaskModal()" style="background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 500;">
            + Create New Field Task
        </button>
        <button onclick="openEquipmentModal()" style="background: #059669; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 500;">
            🔧 Manage Equipment
        </button>
    </div>

    <!-- Existing Tasks -->
    <div class="tasks-section">
        <h3 style="color: #1f2937; margin-bottom: 16px;">Your Field Tasks</h3>
        <div id="tasks-list">
            <?php
            $instructorId = $_SESSION['userid'];
            $tasksRes = $conn->query("SELECT ft.*, 
                COUNT(u.usersId) as totalStudents
                FROM field_tasks ft 
                LEFT JOIN users u ON ft.batch = u.batch AND u.usersRole = 'student'
                WHERE ft.instructorId = $instructorId 
                GROUP BY ft.taskId 
                ORDER BY ft.createdAt DESC");
            
            if ($tasksRes && $tasksRes->num_rows > 0) {
                while ($task = $tasksRes->fetch_assoc()) {
                    echo '<div class="task-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">';
                    echo '<div style="flex: 1;">';
                    echo '<h4 style="margin: 0 0 8px 0; color: #1f2937; font-size: 18px;">' . htmlspecialchars($task['title']) . '</h4>';
                    echo '<p style="margin: 0 0 12px 0; color: #6b7280; line-height: 1.5;">' . htmlspecialchars($task['description']) . '</p>';
                    echo '</div>';
                    echo '<div style="text-align: right;">';
                    echo '<span class="status-badge" style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase; background: #3b82f6; color: white;">' . ucfirst(str_replace('_', ' ', $task['status'])) . '</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">';
                    echo '<div><strong>Location:</strong> ' . htmlspecialchars($task['location']) . '</div>';
                    echo '<div><strong>Assigned:</strong> ' . date('M j, Y', strtotime($task['assignedDate'])) . '</div>';
                    echo '<div><strong>Due:</strong> ' . date('M j, Y', strtotime($task['dueDate'])) . '</div>';
                    echo '<div><strong>Batch:</strong> ' . htmlspecialchars($task['batch']) . '</div>';
                    echo '<div><strong>Semester:</strong> ' . $task['semester'] . '</div>';
                    echo '<div><strong>Students:</strong> ' . $task['totalStudents'] . ' students in batch</div>';
                    echo '</div>';
                    
                    if ($task['requiredEquipment']) {
                        echo '<div style="margin-bottom: 16px;">';
                        echo '<strong>Required Equipment:</strong><br>';
                        $equipment = explode(',', $task['requiredEquipment']);
                        foreach ($equipment as $item) {
                            echo '<span style="display: inline-block; background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 4px; margin: 2px; font-size: 12px;">' . htmlspecialchars(trim($item)) . '</span>';
                        }
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
                    echo '<button onclick="viewTaskDetails(' . $task['taskId'] . ')" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">View Details</button>';
                    echo '<button onclick="editTask(' . $task['taskId'] . ')" style="background: #059669; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">Edit</button>';
                    echo '<button onclick="deleteTask(' . $task['taskId'] . ')" style="background: #dc2626; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">Delete</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #6b7280;">No field tasks created yet.</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div id="createTaskModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:24px; border-radius:8px; width:600px; max-width:90%; max-height:90%; overflow-y:auto;">
        <h3 style="margin:0 0 20px 0; color:#1f2937;">Create New Field Task</h3>
        <form id="createTaskForm" method="post" action="save_field_task.php" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="title" style="display:block; margin-bottom:4px; font-weight:500;">Task Title:</label>
                    <input type="text" id="title" name="title" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
                <div>
                    <label for="location" style="display:block; margin-bottom:4px; font-weight:500;">Location:</label>
                    <input type="text" id="location" name="location" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:4px; font-weight:500;">Description:</label>
                <textarea id="description" name="description" rows="3" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; resize:vertical;"></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="batch" style="display:block; margin-bottom:4px; font-weight:500;">Batch:</label>
                    <select id="batch" name="batch" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                        <option value="">Select Batch</option>
                        <?php
                        $batchRes = $conn->query("SELECT DISTINCT batch FROM users WHERE usersRole = 'student' ORDER BY batch DESC");
                        while ($row = $batchRes->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['batch']) . '">' . htmlspecialchars($row['batch']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="semester" style="display:block; margin-bottom:4px; font-weight:500;">Semester:</label>
                    <select id="semester" name="semester" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                        <option value="">Select Semester</option>
                        <?php for ($i = 1; $i <= 8; $i++) { echo '<option value="' . $i . '">Semester ' . $i . '</option>'; } ?>
                    </select>
                </div>
                <div>
                    <label for="status" style="display:block; margin-bottom:4px; font-weight:500;">Status:</label>
                    <select id="status" name="status" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="assignedDate" style="display:block; margin-bottom:4px; font-weight:500;">Assigned Date:</label>
                    <input type="date" id="assignedDate" name="assignedDate" required value="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
                <div>
                    <label for="dueDate" style="display:block; margin-bottom:4px; font-weight:500;">Due Date:</label>
                    <input type="date" id="dueDate" name="dueDate" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label for="requiredEquipment" style="display:block; margin-bottom:4px; font-weight:500;">Required Equipment with Quantities:</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 8px; margin-bottom: 8px; max-height: 200px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; padding: 8px;">
                    <?php
                    $equipmentRes = $conn->query("SELECT * FROM equipment WHERE isActive = 1 ORDER BY category, name");
                    if ($equipmentRes && $equipmentRes->num_rows > 0) {
                        while ($equipment = $equipmentRes->fetch_assoc()) {
                            echo '<div style="display: flex; align-items: center; padding: 8px; border: 1px solid #e5e7eb; border-radius: 4px; background: #f9fafb;">';
                            echo '<input type="checkbox" name="equipment[]" value="' . htmlspecialchars($equipment['name']) . '" onchange="toggleQuantity(this)" style="margin-right: 8px;">';
                            echo '<div style="flex: 1;">';
                            echo '<span style="font-size: 14px; font-weight: 500;">' . htmlspecialchars($equipment['name']) . '</span>';
                            echo '<div style="font-size: 12px; color: #6b7280;">' . htmlspecialchars($equipment['category']) . '</div>';
                            echo '</div>';
                            echo '<div style="display: flex; align-items: center; gap: 4px;">';
                            echo '<label style="font-size: 12px; color: #6b7280;">Qty:</label>';
                            echo '<input type="number" name="quantity_' . $equipment['equipmentId'] . '" min="1" value="1" disabled style="width: 60px; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div style="color: #6b7280; padding: 8px;">No equipment available</div>';
                    }
                    ?>
                </div>
                <small style="color: #6b7280;">Select equipment and specify quantities needed for this field task</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="instructions" style="display:block; margin-bottom:4px; font-weight:500;">Special Instructions:</label>
                <textarea id="instructions" name="instructions" rows="3" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; resize:vertical;"></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="taskPdf" style="display:block; margin-bottom:4px; font-weight:500;">Task PDF Document (Optional):</label>
                <input type="file" id="taskPdf" name="taskPdf" accept=".pdf" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                <small style="color: #6b7280; font-size: 12px;">Upload a PDF document with detailed instructions, diagrams, or reference materials for this field task.</small>
            </div>
            
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeCreateTaskModal()" style="padding:8px 16px; border:1px solid #d1d5db; background:white; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer;">Create Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Equipment Management Modal -->
<div id="equipmentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:24px; border-radius:8px; width:700px; max-width:90%; max-height:90%; overflow-y:auto;">
        <h3 style="margin:0 0 20px 0; color:#1f2937;">Manage Equipment</h3>
        
        <!-- Add New Equipment -->
        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 12px 0; color: #1f2937;">Add New Equipment</h4>
            <form method="post" action="save_equipment.php">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label for="equipmentName" style="display:block; margin-bottom:4px; font-weight:500;">Equipment Name:</label>
                        <input type="text" id="equipmentName" name="equipmentName" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                    </div>
                    <div>
                        <label for="equipmentCategory" style="display:block; margin-bottom:4px; font-weight:500;">Category:</label>
                        <select id="equipmentCategory" name="equipmentCategory" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                            <option value="">Select Category</option>
                            <option value="Surveying">Surveying</option>
                            <option value="Navigation">Navigation</option>
                            <option value="Documentation">Documentation</option>
                            <option value="Support">Support</option>
                            <option value="Measuring">Measuring</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 12px;">
                    <label for="equipmentDescription" style="display:block; margin-bottom:4px; font-weight:500;">Description:</label>
                    <textarea id="equipmentDescription" name="equipmentDescription" rows="2" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; resize:vertical;"></textarea>
                </div>
                <button type="submit" style="background: #059669; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Add Equipment</button>
            </form>
        </div>
        
        <!-- Existing Equipment -->
        <div>
            <h4 style="margin: 0 0 12px 0; color: #1f2937;">Existing Equipment</h4>
            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 4px;">
                <?php
                $equipmentRes = $conn->query("SELECT * FROM equipment ORDER BY category, name");
                if ($equipmentRes && $equipmentRes->num_rows > 0) {
                    while ($equipment = $equipmentRes->fetch_assoc()) {
                        echo '<div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #e5e7eb;">';
                        echo '<div>';
                        echo '<div style="font-weight: 500; color: #1f2937;">' . htmlspecialchars($equipment['name']) . '</div>';
                        echo '<div style="font-size: 12px; color: #6b7280;">' . htmlspecialchars($equipment['category']) . '</div>';
                        if ($equipment['description']) {
                            echo '<div style="font-size: 12px; color: #6b7280; margin-top: 4px;">' . htmlspecialchars($equipment['description']) . '</div>';
                        }
                        echo '</div>';
                        echo '<div style="display: flex; gap: 8px;">';
                        if ($equipment['isActive']) {
                            echo '<span style="background: #10b981; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">Active</span>';
                        } else {
                            echo '<span style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">Inactive</span>';
                        }
                        echo '<button onclick="toggleEquipment(' . $equipment['equipmentId'] . ', ' . ($equipment['isActive'] ? 'false' : 'true') . ')" style="background: ' . ($equipment['isActive'] ? '#ef4444' : '#10b981') . '; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">' . ($equipment['isActive'] ? 'Deactivate' : 'Activate') . '</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="text-align: center; padding: 20px; color: #6b7280;">No equipment found</div>';
                }
                ?>
            </div>
        </div>
        
        <div style="display:flex; gap:12px; justify-content:flex-end; margin-top: 20px;">
            <button type="button" onclick="closeEquipmentModal()" style="padding:8px 16px; border:1px solid #d1d5db; background:white; border-radius:4px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<script>
function openCreateTaskModal() {
    document.getElementById('createTaskModal').style.display = 'block';
}

function closeCreateTaskModal() {
    document.getElementById('createTaskModal').style.display = 'none';
    document.getElementById('createTaskForm').reset();
    
    // Reset form action and title
    document.getElementById('createTaskForm').action = 'save_field_task.php';
    document.querySelector('#createTaskModal h3').textContent = 'Create New Field Task';
    document.querySelector('#createTaskModal button[type="submit"]').textContent = 'Create Task';
    
    // Remove any hidden taskId input
    const hiddenInput = document.querySelector('input[name="taskId"]');
    if (hiddenInput) {
        hiddenInput.remove();
    }
    
    // Uncheck all equipment checkboxes and disable quantity inputs
    const checkboxes = document.querySelectorAll('input[name="equipment[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
        const quantityInput = checkbox.parentElement.querySelector('input[type="number"]');
        if (quantityInput) {
            quantityInput.disabled = true;
            quantityInput.style.background = '#f3f4f6';
            quantityInput.value = 1;
        }
    });
}

function viewTaskDetails(taskId) {
    alert('View task details for ID: ' + taskId);
}

function editTask(taskId) {
    console.log('Editing task ID:', taskId);
    
    // First, clear any existing form data
    closeCreateTaskModal();
    
    // Fetch task details and populate edit modal
    fetch('get_task_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'taskId=' + taskId
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Task data received:', data);
        
        if (data.success) {
            // Populate form with existing data
            document.getElementById('title').value = data.task.title || '';
            document.getElementById('description').value = data.task.description || '';
            document.getElementById('location').value = data.task.location || '';
            document.getElementById('batch').value = data.task.batch || '';
            document.getElementById('semester').value = data.task.semester || '';
            document.getElementById('assignedDate').value = data.task.assignedDate || '';
            document.getElementById('dueDate').value = data.task.dueDate || '';
            document.getElementById('status').value = data.task.status || 'pending';
            document.getElementById('instructions').value = data.task.instructions || '';
            
            console.log('Form fields populated');
            
            // Handle equipment selection
            if (data.task.requiredEquipment) {
                console.log('Equipment data:', data.task.requiredEquipment);
                const equipment = data.task.requiredEquipment.split(',');
                equipment.forEach(function(item) {
                    item = item.trim();
                    console.log('Processing equipment:', item);
                    
                    // Check if it has quantity info
                    if (item.includes('(Qty:')) {
                        const parts = item.split('(Qty:');
                        const equipmentName = parts[0].trim();
                        const quantity = parts[1].replace(')', '').trim();
                        
                        console.log('Equipment with quantity:', equipmentName, 'Qty:', quantity);
                        
                        // Find and check the checkbox
                        const checkboxes = document.querySelectorAll('input[name="equipment[]"]');
                        checkboxes.forEach(function(checkbox) {
                            if (checkbox.value === equipmentName) {
                                console.log('Found matching checkbox for:', equipmentName);
                                checkbox.checked = true;
                                toggleQuantity(checkbox);
                                
                                // Set quantity
                                const quantityInput = checkbox.parentElement.querySelector('input[type="number"]');
                                if (quantityInput) {
                                    quantityInput.value = quantity;
                                    console.log('Set quantity to:', quantity);
                                }
                            }
                        });
                    } else {
                        // Equipment without quantity
                        console.log('Equipment without quantity:', item);
                        const checkboxes = document.querySelectorAll('input[name="equipment[]"]');
                        checkboxes.forEach(function(checkbox) {
                            if (checkbox.value === item) {
                                console.log('Found matching checkbox for:', item);
                                checkbox.checked = true;
                                toggleQuantity(checkbox);
                            }
                        });
                    }
                });
            }
            
            // Change form action to update
            document.getElementById('createTaskForm').action = 'update_field_task.php';
            
            // Add hidden input for taskId
            const existingHidden = document.querySelector('input[name="taskId"]');
            if (existingHidden) {
                existingHidden.remove();
            }
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'taskId';
            hiddenInput.value = taskId;
            document.getElementById('createTaskForm').appendChild(hiddenInput);
            
            // Change modal title and button
            document.querySelector('#createTaskModal h3').textContent = 'Edit Field Task';
            document.querySelector('#createTaskModal button[type="submit"]').textContent = 'Update Task';
            
            // Show modal
            document.getElementById('createTaskModal').style.display = 'block';
            console.log('Modal opened for editing');
        } else {
            console.error('Error loading task details:', data.message);
            alert('Error loading task details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading task details: ' + error.message);
    });
}

function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this field task? This action cannot be undone.')) {
        fetch('delete_field_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'taskId=' + taskId
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Field task deleted successfully!');
                location.reload();
            } else {
                alert('Error deleting field task');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting field task');
        });
    }
}

function openEquipmentModal() {
    document.getElementById('equipmentModal').style.display = 'block';
}

function closeEquipmentModal() {
    document.getElementById('equipmentModal').style.display = 'none';
}

function toggleEquipment(equipmentId, isActive) {
    if (confirm('Are you sure you want to ' + (isActive === 'true' ? 'activate' : 'deactivate') + ' this equipment?')) {
        fetch('toggle_equipment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'equipmentId=' + equipmentId + '&isActive=' + isActive
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                location.reload();
            } else {
                alert('Error updating equipment status');
            }
        });
    }
}

function toggleQuantity(checkbox) {
    const quantityInput = checkbox.parentElement.querySelector('input[type="number"]');
    if (checkbox.checked) {
        quantityInput.disabled = false;
        quantityInput.style.background = 'white';
    } else {
        quantityInput.disabled = true;
        quantityInput.style.background = '#f3f4f6';
    }
}

// Close modal when clicking outside
document.getElementById('createTaskModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateTaskModal();
    }
});
</script>