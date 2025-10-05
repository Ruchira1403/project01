<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';
?>
<link rel="stylesheet" href="instructor.home.css">

<div class="main-content">
    <div class="dashboard-header">
        <h1>Viva Sessions</h1>
        <p>Manage oral examinations and view assessment results.</p>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php
        $instructorId = $_SESSION['userid'];
        
        // Auto-update status for past sessions
        $conn->query("UPDATE viva_sessions SET status = 'completed' WHERE instructorId = $instructorId AND status = 'scheduled' AND date < CURDATE()");
        
        // Get upcoming vivas count
        $upcomingRes = $conn->query("SELECT COUNT(*) as count FROM viva_sessions WHERE instructorId = $instructorId AND status = 'scheduled' AND date >= CURDATE()");
        $upcomingCount = $upcomingRes ? $upcomingRes->fetch_assoc()['count'] : 0;
        
        // Get completed vivas count
        $completedRes = $conn->query("SELECT COUNT(*) as count FROM viva_sessions WHERE instructorId = $instructorId AND status = 'completed'");
        $completedCount = $completedRes ? $completedRes->fetch_assoc()['count'] : 0;
        
        // Get total students assessed
        $studentsRes = $conn->query("SELECT COUNT(DISTINCT s.studentId) as count FROM viva_sessions v 
            JOIN viva_participants vp ON v.sessionId = vp.sessionId 
            JOIN users s ON vp.studentId = s.usersId 
            WHERE v.instructorId = $instructorId AND v.status = 'completed'");
        $studentsCount = $studentsRes ? $studentsRes->fetch_assoc()['count'] : 0;
        
        // Get total examiners
        $examinersRes = $conn->query("SELECT COUNT(DISTINCT instructorId) as count FROM viva_sessions WHERE instructorId = $instructorId");
        $examinersCount = $examinersRes ? $examinersRes->fetch_assoc()['count'] : 0;
        ?>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #3b82f6; color: white; padding: 8px; border-radius: 6px;">📅</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $upcomingCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Upcoming Vivas</div>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #10b981; color: white; padding: 8px; border-radius: 6px;">📋</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $completedCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Completed</div>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #f59e0b; color: white; padding: 8px; border-radius: 6px;">👥</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $studentsCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Students Assessed</div>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #8b5cf6; color: white; padding: 8px; border-radius: 6px;">👤</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $examinersCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Examiners</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div style="margin-bottom: 24px;">
        <div style="display: flex; gap: 8px; border-bottom: 1px solid #e5e7eb;">
            <button id="scheduleTab" onclick="switchTab('schedule')" style="padding: 12px 24px; border: none; background: #2563eb; color: white; border-radius: 4px 4px 0 0; cursor: pointer;">Schedule</button>
            <button id="resultsTab" onclick="switchTab('results')" style="padding: 12px 24px; border: none; background: #f3f4f6; color: #374151; border-radius: 4px 4px 0 0; cursor: pointer;">Results</button>
        </div>
    </div>

    <!-- Schedule Tab -->
    <div id="scheduleContent">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #1f2937;">Viva Schedule</h3>
            <div style="display: flex; gap: 12px;">
                <button onclick="exportSchedule()" style="padding: 8px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">Export Schedule</button>
                <button onclick="openCreateModal()" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Schedule New Viva</button>
            </div>
        </div>

        <!-- Viva Sessions List -->
        <div id="vivaSessionsList">
            <?php
            $sessionsRes = $conn->query("SELECT * FROM viva_sessions WHERE instructorId = $instructorId ORDER BY date ASC, time ASC");
            if ($sessionsRes && $sessionsRes->num_rows > 0) {
                while ($session = $sessionsRes->fetch_assoc()) {
                    $statusColor = '';
                    $statusText = '';
                    switch ($session['status']) {
                        case 'scheduled':
                            $statusColor = '#3b82f6';
                            $statusText = 'Scheduled';
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
                    
                    echo '<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">';
                    echo '<div style="flex: 1;">';
                    echo '<h4 style="margin: 0 0 8px 0; color: #1f2937; font-size: 18px;">' . htmlspecialchars($session['title']) . '</h4>';
                    if ($session['description']) {
                        echo '<p style="margin: 0 0 12px 0; color: #6b7280; line-height: 1.5;">' . htmlspecialchars($session['description']) . '</p>';
                    }
                    echo '</div>';
                    echo '<div style="text-align: right;">';
                    echo '<span style="background: ' . $statusColor . '; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase;">' . $statusText . '</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">';
                    echo '<div><strong>Date:</strong> ' . date('l F j, Y', strtotime($session['date'])) . '</div>';
                    echo '<div><strong>Time:</strong> ' . date('H:i', strtotime($session['time'])) . ' (' . $session['duration'] . ' minutes)</div>';
                    echo '<div><strong>Location:</strong> ' . htmlspecialchars($session['location']) . '</div>';
                    echo '<div><strong>Batch:</strong> ' . htmlspecialchars($session['batch']) . ' - Semester ' . $session['semester'] . '</div>';
                    if ($session['updatedAt']) {
                        echo '<div><strong>Last Updated:</strong> ' . date('M j, Y H:i', strtotime($session['updatedAt'])) . '</div>';
                    }
                    echo '</div>';
                    
                    if ($session['pdfPath']) {
                        echo '<div style="margin-bottom: 16px;">';
                        echo '<strong>📄 Viva Document:</strong><br>';
                        echo '<a href="' . htmlspecialchars($session['pdfPath']) . '" target="_blank" style="display: inline-flex; align-items: center; background: #dc2626; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-top: 8px;">';
                        echo '📄 Download PDF</a>';
                        echo '</div>';
                    }
                    
                    echo '<div style="display: flex; gap: 8px;">';
                    echo '<button onclick="viewSessionDetails(' . $session['sessionId'] . ')" style="padding: 6px 12px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer;">View Details</button>';
                    if ($session['status'] === 'scheduled') {
                        echo '<button onclick="editSession(' . $session['sessionId'] . ')" style="padding: 6px 12px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Edit</button>';
                        echo '<button onclick="changeStatus(' . $session['sessionId'] . ', \'completed\')" style="padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer;">Mark Complete</button>';
                        echo '<button onclick="changeStatus(' . $session['sessionId'] . ', \'cancelled\')" style="padding: 6px 12px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>';
                    }
                    if ($session['status'] === 'completed') {
                        echo '<button onclick="openResultUploadModal(' . $session['sessionId'] . ', \'' . htmlspecialchars($session['batch']) . '\')" style="padding: 6px 12px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer;">📄 Upload Results</button>';
                        echo '<button onclick="changeStatus(' . $session['sessionId'] . ', \'scheduled\')" style="padding: 6px 12px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Reopen</button>';
                    }
                    if ($session['status'] === 'cancelled') {
                        echo '<button onclick="changeStatus(' . $session['sessionId'] . ', \'scheduled\')" style="padding: 6px 12px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Reschedule</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #6b7280;">No viva sessions scheduled yet.</div>';
            }
            ?>
        </div>
    </div>

    <!-- Results Tab -->
    <div id="resultsContent" style="display: none;">
        <h3 style="margin: 0 0 20px 0; color: #1f2937;">Viva Results</h3>
        <div id="resultsList">
            <?php
            $resultsRes = $conn->query("SELECT * FROM viva_sessions WHERE instructorId = $instructorId AND status = 'completed' AND resultPdfPath IS NOT NULL ORDER BY date DESC");
            if ($resultsRes && $resultsRes->num_rows > 0) {
                while ($result = $resultsRes->fetch_assoc()) {
                    echo '<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">';
                    echo '<div style="flex: 1;">';
                    echo '<h4 style="margin: 0 0 8px 0; color: #1f2937; font-size: 18px;">' . htmlspecialchars($result['title']) . '</h4>';
                    echo '<p style="margin: 0 0 12px 0; color: #6b7280; line-height: 1.5;">Batch: ' . htmlspecialchars($result['batch']) . ' - Semester ' . $result['semester'] . '</p>';
                    echo '</div>';
                    echo '<div style="text-align: right;">';
                    echo '<span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase;">Results Available</span>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">';
                    echo '<div><strong>Date:</strong> ' . date('l F j, Y', strtotime($result['date'])) . '</div>';
                    echo '<div><strong>Time:</strong> ' . date('H:i', strtotime($result['time'])) . '</div>';
                    echo '<div><strong>Location:</strong> ' . htmlspecialchars($result['location']) . '</div>';
                    echo '<div><strong>Uploaded:</strong> ' . date('M j, Y H:i', strtotime($result['resultUploadedAt'] ?? $result['updatedAt'])) . '</div>';
                    echo '</div>';
                    
                    echo '<div style="margin-bottom: 16px;">';
                    echo '<strong>📄 Result Document:</strong><br>';
                    echo '<a href="' . htmlspecialchars($result['resultPdfPath']) . '" target="_blank" style="display: inline-flex; align-items: center; background: #10b981; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-top: 8px;">';
                    echo '📄 Download Results</a>';
                    echo '</div>';
                    
                    echo '<div style="display: flex; gap: 8px;">';
                    echo '<button onclick="viewResultDetails(' . $result['sessionId'] . ')" style="padding: 6px 12px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer;">View Details</button>';
                    echo '<button onclick="openResultUploadModal(' . $result['sessionId'] . ', \'' . htmlspecialchars($result['batch']) . '\')" style="padding: 6px 12px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Update Results</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #6b7280;">';
                echo '<p>No results uploaded yet.</p>';
                echo '<p>Complete viva sessions and upload result PDFs to see them here.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Create/Edit Viva Modal -->
<div id="vivaModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:24px; border-radius:8px; width:600px; max-width:90%; max-height:90%; overflow-y:auto;">
        <h3 style="margin:0 0 16px 0; color:#1f2937;">Schedule New Viva</h3>
        <form id="vivaForm" method="post" action="save_viva_session.php" enctype="multipart/form-data">
            <input type="hidden" id="sessionId" name="sessionId">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="title" style="display:block; margin-bottom:4px; font-weight:500;">Title:</label>
                    <input type="text" id="title" name="title" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
                <div>
                    <label for="location" style="display:block; margin-bottom:4px; font-weight:500;">Location:</label>
                    <input type="text" id="location" name="location" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:4px; font-weight:500;">Description:</label>
                <textarea id="description" name="description" rows="3" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; resize:vertical;"></textarea>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label for="vivaPdf" style="display:block; margin-bottom:4px; font-weight:500;">Viva Document (PDF):</label>
                <input type="file" id="vivaPdf" name="vivaPdf" accept=".pdf" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">Upload a PDF document with viva questions, guidelines, or instructions (Optional)</div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="date" style="display:block; margin-bottom:4px; font-weight:500;">Date:</label>
                    <input type="date" id="date" name="date" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
                <div>
                    <label for="time" style="display:block; margin-bottom:4px; font-weight:500;">Time:</label>
                    <input type="time" id="time" name="time" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
                <div>
                    <label for="duration" style="display:block; margin-bottom:4px; font-weight:500;">Duration (minutes):</label>
                    <input type="number" id="duration" name="duration" min="30" max="300" value="120" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label for="batch" style="display:block; margin-bottom:4px; font-weight:500;">Batch:</label>
                    <select id="batch" name="batch" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                        <option value="">Select Batch</option>
                        <?php
                        $batchesRes = $conn->query("SELECT DISTINCT batch FROM users WHERE usersRole = 'student' ORDER BY batch ASC");
                        if ($batchesRes && $batchesRes->num_rows > 0) {
                            while ($batch = $batchesRes->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($batch['batch']) . '">' . htmlspecialchars($batch['batch']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="semester" style="display:block; margin-bottom:4px; font-weight:500;">Semester:</label>
                    <select id="semester" name="semester" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                        <option value="">Select Semester</option>
                        <?php for ($i = 1; $i <= 8; $i++) { ?>
                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeVivaModal()" style="padding:8px 16px; border:1px solid #d1d5db; background:white; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer;">Save Viva</button>
            </div>
        </form>
    </div>
</div>

<!-- Result Upload Modal -->
<div id="resultUploadModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:24px; border-radius:8px; width:500px; max-width:90%; max-height:90%; overflow-y:auto;">
        <h3 style="margin:0 0 16px 0; color:#1f2937;">Upload Viva Results</h3>
        <form id="resultUploadForm" method="post" action="upload_viva_result.php" enctype="multipart/form-data">
            <input type="hidden" id="resultSessionId" name="sessionId">
            <input type="hidden" id="resultBatch" name="batch">
            
            <div style="margin-bottom: 16px;">
                <label for="resultPdf" style="display:block; margin-bottom:4px; font-weight:500;">Result PDF:</label>
                <input type="file" id="resultPdf" name="resultPdf" accept=".pdf" required style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">Upload the PDF file containing viva results for the selected batch</div>
            </div>
            
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeResultUploadModal()" style="padding:8px 16px; border:1px solid #d1d5db; background:white; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px; background:#10b981; color:white; border:none; border-radius:4px; cursor:pointer;">Upload Result</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    // Hide all content
    document.getElementById('scheduleContent').style.display = 'none';
    document.getElementById('resultsContent').style.display = 'none';
    
    // Reset button styles
    document.getElementById('scheduleTab').style.background = '#f3f4f6';
    document.getElementById('scheduleTab').style.color = '#374151';
    document.getElementById('resultsTab').style.background = '#f3f4f6';
    document.getElementById('resultsTab').style.color = '#374151';
    
    // Show selected content
    if (tab === 'schedule') {
        document.getElementById('scheduleContent').style.display = 'block';
        document.getElementById('scheduleTab').style.background = '#2563eb';
        document.getElementById('scheduleTab').style.color = 'white';
    } else if (tab === 'results') {
        document.getElementById('resultsContent').style.display = 'block';
        document.getElementById('resultsTab').style.background = '#2563eb';
        document.getElementById('resultsTab').style.color = 'white';
    }
}

function openCreateModal() {
    document.getElementById('vivaForm').reset();
    document.getElementById('sessionId').value = '';
    document.getElementById('vivaModal').style.display = 'block';
}

function closeVivaModal() {
    document.getElementById('vivaModal').style.display = 'none';
    document.getElementById('vivaForm').reset();
}

function viewSessionDetails(sessionId) {
    alert('View session details for ID: ' + sessionId);
}

function viewResultDetails(sessionId) {
    alert('View result details for ID: ' + sessionId);
}

function changeStatus(sessionId, newStatus) {
    if (confirm('Are you sure you want to change the status to ' + newStatus + '?')) {
        // Create a form and submit it
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'change_viva_status.php';
        
        var sessionIdInput = document.createElement('input');
        sessionIdInput.type = 'hidden';
        sessionIdInput.name = 'sessionId';
        sessionIdInput.value = sessionId;
        
        var statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        
        form.appendChild(sessionIdInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function editSession(sessionId) {
    alert('Edit session for ID: ' + sessionId);
}

function exportSchedule() {
    alert('Export schedule functionality will be implemented.');
}

function openResultUploadModal(sessionId, batch) {
    document.getElementById('resultSessionId').value = sessionId;
    document.getElementById('resultBatch').value = batch;
    document.getElementById('resultUploadForm').reset();
    document.getElementById('resultSessionId').value = sessionId;
    document.getElementById('resultBatch').value = batch;
    document.getElementById('resultUploadModal').style.display = 'block';
}

function closeResultUploadModal() {
    document.getElementById('resultUploadModal').style.display = 'none';
    document.getElementById('resultUploadForm').reset();
}

// Close modal when clicking outside
document.getElementById('vivaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVivaModal();
    }
});

document.getElementById('resultUploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeResultUploadModal();
    }
});
</script>
