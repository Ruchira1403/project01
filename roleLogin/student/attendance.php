<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "student") {
    header("location: ../../login.php");
    exit();
}
require_once '../../includes/dbh.inc.php';

$studentUid = $_SESSION["useruid"];
$studentId = $_SESSION["userid"];

// Get student's attendance data
$attendanceQuery = "SELECT * FROM attendance WHERE userUid = '$studentUid' ORDER BY attendanceDate DESC, startTime DESC";
$attendanceResult = $conn->query($attendanceQuery);

// Calculate statistics
$totalSessions = 0;
$presentCount = 0;
$absentCount = 0;
$attendanceRecords = [];

if ($attendanceResult && $attendanceResult->num_rows > 0) {
    while ($row = $attendanceResult->fetch_assoc()) {
        $attendanceRecords[] = $row;
        $totalSessions++;
        if ($row['status'] === 'present') {
            $presentCount++;
        } else {
            $absentCount++;
        }
    }
}

$attendanceRate = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 1) : 0;

// Get monthly breakdown
$monthlyData = [];
$currentYear = date('Y');
for ($month = 1; $month <= 12; $month++) {
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    $monthSessions = 0;
    $monthPresent = 0;
    
    foreach ($attendanceRecords as $record) {
        $recordMonth = date('n', strtotime($record['attendanceDate']));
        if ($recordMonth == $month) {
            $monthSessions++;
            if ($record['status'] === 'present') {
                $monthPresent++;
            }
        }
    }
    
    if ($monthSessions > 0) {
        $monthlyData[$monthName] = [
            'total' => $monthSessions,
            'present' => $monthPresent,
            'rate' => round(($monthPresent / $monthSessions) * 100, 1)
        ];
    }
}

// Calculate consecutive present days
$consecutivePresent = 0;
$maxConsecutive = 0;
$sortedRecords = array_reverse($attendanceRecords);
foreach ($sortedRecords as $record) {
    if ($record['status'] === 'present') {
        $consecutivePresent++;
        $maxConsecutive = max($maxConsecutive, $consecutivePresent);
    } else {
        $consecutivePresent = 0;
    }
}

// Calculate total field hours
$totalFieldHours = 0;
foreach ($attendanceRecords as $record) {
    if ($record['status'] === 'present' && $record['startTime'] && $record['endTime']) {
        $start = strtotime($record['startTime']);
        $end = strtotime($record['endTime']);
        $hours = ($end - $start) / 3600;
        $totalFieldHours += $hours;
    }
}

// Get next session (if any)
$nextSessionQuery = "SELECT * FROM attendance WHERE userUid = '$studentUid' AND attendanceDate >= CURDATE() ORDER BY attendanceDate ASC, startTime ASC LIMIT 1";
$nextSessionResult = $conn->query($nextSessionQuery);
$nextSession = $nextSessionResult ? $nextSessionResult->fetch_assoc() : null;

// Get class average (all students in same batch)
$batchQuery = "SELECT batch FROM users WHERE usersUid = '$studentUid'";
$batchResult = $conn->query($batchQuery);
$studentBatch = $batchResult ? $batchResult->fetch_assoc()['batch'] : null;

$classAverage = 0;
if ($studentBatch) {
    $classAvgQuery = "SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_rate 
                      FROM attendance a 
                      JOIN users u ON u.usersUid = a.userUid 
                      WHERE u.batch = '$studentBatch'";
    $classAvgResult = $conn->query($classAvgQuery);
    if ($classAvgResult) {
        $classAverage = round($classAvgResult->fetch_assoc()['avg_rate'], 1);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - GeoSurvey</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .main-content {
            margin-left: 220px;
            padding: 32px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: #64748b;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .card-title {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-label {
            font-size: 0.9rem;
            color: #64748b;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }

        .attendance-progress {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-icon {
            margin-right: 8px;
        }

        .progress-item {
            margin-bottom: 20px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .monthly-breakdown {
            margin-top: 24px;
        }

        .month-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
        }

        .month-name {
            font-weight: 500;
            color: #374151;
        }

        .month-stats {
            font-size: 0.9rem;
            color: #64748b;
        }

        .quick-stats {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .stat-item {
            margin-bottom: 20px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #64748b;
        }

        .stat-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .stat-box.green {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .stat-box.orange {
            background: #fff7ed;
            border-color: #fed7aa;
        }

        .next-session {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .next-session-time {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .next-session-label {
            font-size: 0.9rem;
            color: #64748b;
        }

        .attendance-history {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .history-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        .history-table td {
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-present {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-absent {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .text-green {
            color: #10b981;
        }

        .text-red {
            color: #ef4444;
        }

        .text-blue {
            color: #3b82f6;
        }

        .text-orange {
            color: #f59e0b;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 16px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-cards {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Attendance</h1>
            <p class="page-subtitle">Track your field session attendance and participation.</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #dbeafe; color: #3b82f6;">👥</div>
                    <div>
                        <div class="card-title">Total Sessions</div>
                        <div class="card-value"><?php echo $totalSessions; ?></div>
                        <div class="card-label">This semester</div>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #dcfce7; color: #16a34a;">✓</div>
                    <div>
                        <div class="card-title">Present</div>
                        <div class="card-value text-green"><?php echo $presentCount; ?></div>
                        <div class="card-label">Sessions attended</div>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #fef2f2; color: #dc2626;">✗</div>
                    <div>
                        <div class="card-title">Absent</div>
                        <div class="card-value text-red"><?php echo $absentCount; ?></div>
                        <div class="card-label">Sessions missed</div>
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <div class="card-header">
                    <div class="card-icon" style="background-color: #f0f9ff; color: #3b82f6;">📊</div>
                    <div>
                        <div class="card-title">Attendance Rate</div>
                        <div class="card-value text-green"><?php echo $attendanceRate; ?>%</div>
                        <div class="card-label">Current rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Attendance Progress -->
            <div class="attendance-progress">
                <h2 class="section-title">
                    <span class="section-icon">📊</span>
                    Attendance Progress
                </h2>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Overall Attendance</span>
                        <span><?php echo $attendanceRate; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $attendanceRate; ?>%"></div>
                    </div>
                </div>

                <div class="monthly-breakdown">
                    <h3 style="margin-bottom: 16px; font-size: 1.1rem; color: #374151;">Monthly Breakdown</h3>
                    <?php foreach ($monthlyData as $month => $data): ?>
                    <div class="month-item">
                        <span class="month-name"><?php echo $month; ?></span>
                        <div class="month-stats">
                            <span><?php echo $data['present']; ?>/<?php echo $data['total']; ?> (<?php echo $data['rate']; ?>%)</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <h2 class="section-title">
                    <span class="section-icon">📅</span>
                    Quick Stats
                </h2>

                <div class="stat-item">
                    <div class="stat-box green">
                        <div class="stat-value text-green"><?php echo $maxConsecutive; ?></div>
                        <div class="stat-label">Consecutive Present</div>
                    </div>
                </div>

                <div class="stat-item">
                    <div class="stat-box orange">
                        <div class="stat-value text-orange"><?php echo round($totalFieldHours, 1); ?>h</div>
                        <div class="stat-label">Total Field Hours</div>
                    </div>
                </div>

                <?php if ($nextSession): ?>
                <div class="stat-item">
                    <div class="next-session">
                        <div class="next-session-time">🕐 <?php echo date('M j, g:i A', strtotime($nextSession['attendanceDate'] . ' ' . $nextSession['startTime'])); ?></div>
                        <div class="next-session-label">Next Session</div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="stat-item">
                    <div class="stat-box">
                        <div class="stat-value text-blue"><?php echo $classAverage; ?>%</div>
                        <div class="stat-label">Class Average</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance History -->
        <div class="attendance-history">
            <h2 class="section-title">
                <span class="section-icon">📅</span>
                Attendance History
            </h2>
            
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Session Name</th>
                        <th>Duration</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendanceRecords)): ?>
                        <?php foreach ($attendanceRecords as $record): ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($record['attendanceDate'])); ?></td>
                            <td><?php echo htmlspecialchars($record['topic'] ?: 'Field Session'); ?></td>
                            <td>
                                <?php 
                                if ($record['startTime'] && $record['endTime']) {
                                    $start = strtotime($record['startTime']);
                                    $end = strtotime($record['endTime']);
                                    $hours = ($end - $start) / 3600;
                                    echo round($hours, 1) . ' hours';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($record['location'] ?: 'Field Location'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $record['status'] === 'present' ? 'status-present' : 'status-absent'; ?>">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 32px;">
                                No attendance records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
