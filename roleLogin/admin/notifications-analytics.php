<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
  header("location: ../../login.php");
  exit();
}
include '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="notifications-analytics.css">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<div class="main-content">
  <div class="notifications-header">
    <h1>Notifications</h1>
    <p>Send announcements and manage communications</p>
    <div class="notifications-tabs">
      <button onclick="window.location.href='notifications.php'">Compose</button>
      <button onclick="window.location.href='notifications-history.php'">History</button>
      <button class="active">Analytics</button>
    </div>
    <div class="analytics-row">
      <div class="analytics-metrics">
        <h2>Engagement Metrics</h2>
        <div class="metric-row"><span>Open Rate</span><span class="metric-value">91%</span></div>
        <div class="metric-row"><span>Average Response Time</span><span class="metric-value">2.3 hours</span></div>
        <div class="metric-row"><span>Most Active Time</span><span class="metric-value">2-4 PM</span></div>
      </div>
      <div class="analytics-performance">
        <h2>Recent Performance</h2>
        <div class="performance-row">
          <span>System Maintenance</span>
          <div class="performance-bar">
            <div class="performance-fill" style="width:91%;"></div>
          </div>
          <span class="performance-label">142/156 viewed</span>
        </div>
        <div class="performance-row">
          <span>Grade Submission</span>
          <div class="performance-bar">
            <div class="performance-fill" style="width:83%;"></div>
          </div>
          <span class="performance-label">10/12 viewed</span>
        </div>
      </div>
    </div>
  </div>
</div>
