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
<div class="main-content" style="margin-top: 100px">
  <div class="notifications-header">
  <div class="page-header1" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 1560px;">
    <h1 style="margin:0; font-size:2.5em; font-weight:300;color: white;">&#128276; Notifications</h1>
    <p style="margin:10px 0 0 0; opacity:0.9; font-size:1.1em; color: white;">Send announcements and manage communications.</p>
  </div>
  <div class="notifications-tabs" style="margin-bottom:50px">
      <button onclick="window.location.href='notifications.php'">Compose</button>
      <button class="active">History</button>
      <button onclick="window.location.href='notifications-analytics.php'" >Analytics</button>
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
