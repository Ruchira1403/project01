<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';
?>
<link rel="stylesheet" href="student.submissions.css">
<style>
/* Page Header Styles */
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
</style>

<div class="main-content" style="margin-left: 270px; margin-top: 140px; margin-right: 50px">
    <div class="page-header">
        <h1>🎤 Viva Sessions</h1>
        <p>View your scheduled oral examinations and results.</p>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php
        $studentId = $_SESSION['userid'];
        $studentBatch = '';
        $batchRes = $conn->query("SELECT batch FROM users WHERE usersId = $studentId LIMIT 1");
        if ($batchRes && $batchRes->num_rows > 0) {
            $studentBatch = $batchRes->fetch_assoc()['batch'];
        }
        
        // Get upcoming vivas for this student's batch
        $upcomingRes = $conn->query("SELECT COUNT(*) as count FROM viva_sessions WHERE batch = '" . $conn->real_escape_string($studentBatch) . "' AND status = 'scheduled' AND date >= CURDATE()");
        $upcomingCount = $upcomingRes ? $upcomingRes->fetch_assoc()['count'] : 0;
        
        // Get completed vivas for this student's batch
        $completedRes = $conn->query("SELECT COUNT(*) as count FROM viva_sessions WHERE batch = '" . $conn->real_escape_string($studentBatch) . "' AND status = 'completed'");
        $completedCount = $completedRes ? $completedRes->fetch_assoc()['count'] : 0;
        
        // Get results available count
        $resultsRes = $conn->query("SELECT COUNT(*) as count FROM viva_sessions WHERE batch = '" . $conn->real_escape_string($studentBatch) . "' AND status = 'completed' AND resultPdfPath IS NOT NULL");
        $resultsCount = $resultsRes ? $resultsRes->fetch_assoc()['count'] : 0;
        
        // Get total sessions for this batch
        $totalRes = $conn->query("SELECT COUNT(*) as count FROM viva_sessions WHERE batch = '" . $conn->real_escape_string($studentBatch) . "'");
        $totalCount = $totalRes ? $totalRes->fetch_assoc()['count'] : 0;
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
                <div style="background: #10b981; color: white; padding: 8px; border-radius: 6px;">✅</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $completedCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Completed</div>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #10b981; color: white; padding: 8px; border-radius: 6px;">📄</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $resultsCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Results Available</div>
                </div>
            </div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: #f59e0b; color: white; padding: 8px; border-radius: 6px;">📋</div>
                <div>
                    <div style="font-size: 24px; font-weight: bold; color: #1f2937;"><?php echo $totalCount; ?></div>
                    <div style="color: #6b7280; font-size: 14px;">Total Sessions</div>
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
        <h3 style="margin: 0 0 20px 0; color: #1f2937;">Your Viva Schedule</h3>
        
        <!-- Viva Sessions List -->
        <div id="vivaSessionsList">
            <?php
            if ($studentBatch) {
                $sessionsRes = $conn->query("SELECT vs.*, u.usersName as examinerName 
                    FROM viva_sessions vs 
                    JOIN users u ON vs.instructorId = u.usersId 
                    WHERE vs.batch = '" . $conn->real_escape_string($studentBatch) . "' 
                    ORDER BY vs.date ASC, vs.time ASC");
                
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
                        echo '<div><strong>Examiner:</strong> ' . htmlspecialchars($session['examinerName']) . '</div>';
                        echo '</div>';
                        
                        if ($session['pdfPath']) {
                            echo '<div style="margin-bottom: 16px;">';
                            echo '<strong>📄 Viva Document:</strong><br>';
                            echo '<a href="' . htmlspecialchars($session['pdfPath']) . '" target="_blank" style="display: inline-flex; align-items: center; background: #dc2626; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-top: 8px;">';
                            echo '📄 Download PDF</a>';
                            echo '</div>';
                        }
                        
                        // Show result PDF if available
                        if ($session['status'] === 'completed' && $session['resultPdfPath']) {
                            echo '<div style="margin-bottom: 16px;">';
                            echo '<strong>📄 Your Results:</strong><br>';
                            echo '<a href="' . htmlspecialchars($session['resultPdfPath']) . '" target="_blank" style="display: inline-flex; align-items: center; background: #10b981; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-top: 8px;">';
                            echo '📄 Download Results</a>';
                            echo '</div>';
                        }
                        
                        // Show additional info for upcoming sessions
                        if ($session['status'] === 'scheduled') {
                            $daysUntil = floor((strtotime($session['date']) - time()) / (60 * 60 * 24));
                            if ($daysUntil == 0) {
                                echo '<div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 12px; margin-top: 12px;">';
                                echo '<div style="display: flex; align-items: center; gap: 8px;">';
                                echo '<span style="color: #92400e; font-weight: bold;">⚠️ Today</span>';
                                echo '<span style="color: #92400e;">Your viva is scheduled for today!</span>';
                                echo '</div>';
                                echo '</div>';
                            } elseif ($daysUntil > 0 && $daysUntil <= 7) {
                                echo '<div style="background: #dbeafe; border: 1px solid #3b82f6; border-radius: 6px; padding: 12px; margin-top: 12px;">';
                                echo '<div style="display: flex; align-items: center; gap: 8px;">';
                                echo '<span style="color: #1e40af; font-weight: bold;">📅 ' . $daysUntil . ' days remaining</span>';
                                echo '<span style="color: #1e40af;">Prepare for your upcoming viva.</span>';
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                        
                        echo '</div>';
                    }
                } else {
                    echo '<div style="text-align: center; padding: 40px; color: #6b7280;">No viva sessions scheduled for your batch yet.</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #6b7280;">Unable to determine your batch. Please contact your instructor.</div>';
            }
            ?>
        </div>
    </div>

    <!-- Results Tab -->
    <div id="resultsContent" style="display: none;">
        <h3 style="margin: 0 0 20px 0; color: #1f2937;">Viva Results</h3>
        <div id="resultsList">
            <?php
            if ($studentBatch) {
                $resultsRes = $conn->query("SELECT vs.*, u.usersName as examinerName 
                    FROM viva_sessions vs 
                    JOIN users u ON vs.instructorId = u.usersId 
                    WHERE vs.batch = '" . $conn->real_escape_string($studentBatch) . "' 
                    AND vs.status = 'completed' 
                    AND vs.resultPdfPath IS NOT NULL 
                    ORDER BY vs.date DESC");
                
                if ($resultsRes && $resultsRes->num_rows > 0) {
                    while ($result = $resultsRes->fetch_assoc()) {
                        echo '<div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
                        echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">';
                        echo '<div style="flex: 1;">';
                        echo '<h4 style="margin: 0 0 8px 0; color: #1f2937; font-size: 18px;">' . htmlspecialchars($result['title']) . '</h4>';
                        echo '<p style="margin: 0 0 12px 0; color: #6b7280; line-height: 1.5;">Examiner: ' . htmlspecialchars($result['examinerName']) . '</p>';
                        echo '</div>';
                        echo '<div style="text-align: right;">';
                        echo '<span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; text-transform: uppercase;">Results Available</span>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">';
                        echo '<div><strong>Date:</strong> ' . date('l F j, Y', strtotime($result['date'])) . '</div>';
                        echo '<div><strong>Time:</strong> ' . date('H:i', strtotime($result['time'])) . '</div>';
                        if ($result['resultUploadedAt']) {
                            echo '<div><strong>Results Uploaded:</strong> ' . date('M j, Y H:i', strtotime($result['resultUploadedAt'])) . '</div>';
                        } else {
                            echo '<div><strong>Results Uploaded:</strong> ' . date('M j, Y H:i', strtotime($result['updatedAt'])) . '</div>';
                        }
                        echo '</div>';
                        
                        echo '<div style="margin-bottom: 16px;">';
                        echo '<strong>📄 Your Viva Results:</strong><br>';
                        echo '<a href="' . htmlspecialchars($result['resultPdfPath']) . '" target="_blank" style="display: inline-flex; align-items: center; background: #10b981; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; margin-top: 8px;">';
                        echo '📄 Download Results</a>';
                        echo '</div>';
                        
                        echo '<div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 12px; margin-top: 12px;">';
                        echo '<div style="display: flex; align-items: center; gap: 8px;">';
                        echo '<span style="color: #0c4a6e; font-weight: bold;">🎉 Results Ready!</span>';
                        echo '<span style="color: #0c4a6e;">Your viva results are now available for download.</span>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo '</div>';
                    }
                } else {
                    echo '<div style="text-align: center; padding: 40px; color: #6b7280;">';
                    echo '<div style="font-size: 48px; margin-bottom: 16px;">📋</div>';
                    echo '<h4 style="margin: 0 0 8px 0; color: #374151;">No Results Available Yet</h4>';
                    echo '<p style="margin: 0; color: #6b7280;">Your viva results will appear here once your instructor uploads them.</p>';
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #6b7280;">Unable to determine your batch. Please contact your instructor.</div>';
            }
            ?>
        </div>
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
</script>
