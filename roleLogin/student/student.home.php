<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<link rel="stylesheet" href="dashboard.css">
<div class="main-content">
   
    <div class="dashboard-stats-row">
        <div class="dashboard-stat">
            <div class="stat-title">Total Tasks</div>
            <div class="stat-value">12</div>
            <div class="stat-desc">This semester</div>
        </div>
        <div class="dashboard-stat">
            <div class="stat-title">Completed</div>
            <div class="stat-value" style="color:#059669;">8</div>
            <div class="stat-desc">67% completion</div>
        </div>
        <div class="dashboard-stat">
            <div class="stat-title">Pending</div>
            <div class="stat-value" style="color:#f59e42;">3</div>
            <div class="stat-desc">Due this week</div>
        </div>
        <div class="dashboard-stat">
            <div class="stat-title">Attendance</div>
            <div class="stat-value" style="color:#2563eb;">94%</div>
            <div class="stat-desc">85/90 sessions</div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-col dashboard-tasks">
            <h2>Recent Tasks</h2>
            <div class="task-card">
                <div class="task-title">Topographic Survey - Week 4</div>
                <div class="task-meta">Field Survey I &nbsp;|&nbsp; Due: 2024-02-15</div>
                <span class="task-status pending">Pending</span>
            </div>
            <div class="task-card">
                <div class="task-title">Level Book Submission</div>
                <div class="task-meta">Field Survey I &nbsp;|&nbsp; Due: 2024-02-10</div>
                <span class="task-status completed">Completed</span> <span class="task-grade">Grade: A</span>
            </div>
            <div class="task-card">
                <div class="task-title">Site Plan Drawing</div>
                <div class="task-meta">Engineering Drawing &nbsp;|&nbsp; Due: 2024-02-08</div>
                <span class="task-status overdue">Overdue</span>
            </div>
            <div class="view-all-tasks"><a href="#">View All Tasks</a></div>
        </div>
        <div class="dashboard-col dashboard-progress">
            <h2>Progress Overview</h2>
            <div class="progress-bar-label">Overall Completion</div>
            <div class="progress-bar"><div class="progress-bar-fill" style="width:67%;"></div></div>
            <div class="progress-bar-label">Attendance Rate</div>
            <div class="progress-bar"><div class="progress-bar-fill" style="width:94%; background:#2563eb;"></div></div>
            <div class="quick-actions">
                <div><span>&#128190;</span> Upload Assignment</div>
                <div><span>&#128197;</span> View Schedule</div>
            </div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-col dashboard-notifications">
            <h2>Recent Notifications</h2>
            <div class="notification-card assignment">
                <div class="notification-title">Assignment Due Soon</div>
                <div class="notification-desc">Site Plan Drawing is due in 2 days</div>
            </div>
            <div class="notification-card feedback">
                <div class="notification-title">Feedback Available</div>
                <div class="notification-desc">Your Level Book submission has been graded</div>
            </div>
        </div>
    </div>
</div>


