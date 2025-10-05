<?php
session_start();
require_once 'topbar.php';
require_once 'sidebar.php';
?>
<link rel="stylesheet" href="instructor.home.css">
<div class="main-content">
    <div class="dashboard-header">
        <h1>Student Management</h1>
        <p>Monitor and manage your students' progress and performance.</p>
        <div class="dashboard-metrics-row">
            <div class="dashboard-metric">
                <div class="metric-title">Total Students</div>
                <div class="metric-value">5</div>
                <div class="metric-desc">Enrolled in course</div>
            </div>
            <div class="dashboard-metric">
                <div class="metric-title">Average GPA</div>
                <div class="metric-value">3.5</div>
                <div class="metric-desc">Class performance</div>
            </div>
            <div class="dashboard-metric">
                <div class="metric-title">Avg Attendance</div>
                <div class="metric-value" style="color:#059669;">84%</div>
                <div class="metric-desc">Class attendance</div>
            </div>
            <div class="dashboard-metric">
                <div class="metric-title">At Risk</div>
                <div class="metric-value" style="color:#dc2626;">1</div>
                <div class="metric-desc">Need attention</div>
            </div>
        </div>
        <div class="dashboard-search-row">
            <input type="text" class="dashboard-search" placeholder="Search students by name, email, or ID...">
            <button class="dashboard-export-btn">Export List</button>
        </div>
    </div>
    <div class="student-roster">
        <h2 class="roster-title">&#128101; Student Roster</h2>
        <table class="roster-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Contact</th>
                    <th>Academic Info</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><div class="roster-avatar">JD</div> John Doe<br><span class="roster-id">GEO2024001</span></td>
                    <td>john.doe@university.edu<br><span class="roster-phone">+1 (555) 123-4567</span></td>
                    <td>3rd Year<br>GPA: 3.8</td>
                    <td>Assignments: 10/12<br>Attendance: 92%</td>
                    <td><span class="roster-status active">Active</span></td>
                    <td><button class="roster-action">&#128196;</button> <button class="roster-action">&#128101;</button></td>
                </tr>
                <tr>
                    <td><div class="roster-avatar">JS</div> Jane Smith<br><span class="roster-id">GEO2024002</span></td>
                    <td>jane.smith@university.edu<br><span class="roster-phone">+1 (555) 234-5678</span></td>
                    <td>3rd Year<br>GPA: 3.9</td>
                    <td>Assignments: 12/12<br>Attendance: 95%</td>
                    <td><span class="roster-status active">Active</span></td>
                    <td><button class="roster-action">&#128196;</button> <button class="roster-action">&#128101;</button></td>
                </tr>
                <tr>
                    <td><div class="roster-avatar">MJ</div> Mike Johnson<br><span class="roster-id">GEO2024003</span></td>
                    <td>mike.johnson@university.edu<br><span class="roster-phone">+1 (555) 345-6789</span></td>
                    <td>3rd Year<br>GPA: 3.2</td>
                    <td>Assignments: 8/12<br>Attendance: 78%</td>
                    <td><span class="roster-status warning">Warning</span></td>
                    <td><button class="roster-action">&#128196;</button> <button class="roster-action">&#128101;</button></td>
                </tr>
                <tr>
                    <td><div class="roster-avatar">SW</div> Sarah Wilson<br><span class="roster-id">GEO2024004</span></td>
                    <td>sarah.wilson@university.edu<br><span class="roster-phone">+1 (555) 456-7890</span></td>
                    <td>3rd Year<br>GPA: 3.7</td>
                    <td>Assignments: 11/12<br>Attendance: 91%</td>
                    <td><span class="roster-status active">Active</span></td>
                    <td><button class="roster-action">&#128196;</button> <button class="roster-action">&#128101;</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


