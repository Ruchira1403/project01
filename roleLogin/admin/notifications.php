<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
  header("location: ../../login.php");
  exit();
}

// Database connection
include '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get logged-in user's name from users table
  $userId = $_SESSION['userid'];
  $userName = '';
  $sqlUser = "SELECT usersName FROM users WHERE usersId = ?";
  $stmtUser = $conn->prepare($sqlUser);
  if ($stmtUser) {
    $stmtUser->bind_param('i', $userId);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    if ($rowUser = $resultUser->fetch_assoc()) {
      $userName = $rowUser['usersName'];
    }
    $stmtUser->close();
  } else {
    die("Prepare failed: " . $conn->error);
  }

  // Audience (multiple values with Choices.js)
  $audienceRole = isset($_POST['audience']) ? implode(',', $_POST['audience']) : '';
  $priority = $_POST['priority'];
  $title = $_POST['title'];
  $massege = $_POST['message'];
  $closeDate = isset($_POST['closed_date']) ? $_POST['closed_date'] : null;

  // Handle send/schedule
  $sendType = $_POST['send_type'] ?? 'send';

  if ($sendType === 'schedule' && !empty($_POST['hidden_schedule_date'])) {
    $sendDate = $_POST['hidden_schedule_date'] . ' 00:00:00';
  } else {
    $sendDate = date('Y-m-d H:i:s'); // send now
  }

  // Insert into DB
  $sql = "INSERT INTO notifications (userName, audienceRole, priority, title, massege, sendDate, closeDate) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssssss', $userName, $audienceRole, $priority, $title, $massege, $sendDate, $closeDate);
  $stmt->execute();
  $stmt->close();
}
?>
<link rel="stylesheet" href="topbar.css">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="fff.css">
<!-- Choices.js CSS for modern multi-select -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<?php include 'topbar.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-content" style="margin-left: 320px; padding: 20px; margin-top: 180px;">
  <div class="notifications-header">
    <h1>Notifications</h1>
    <p>Send announcements and manage communications</p>

    <div class="notifications-tabs">
      <button class="active">Compose</button>
      <button id="history-btn" type="button">History</button>
      <button>Analytics</button>
    </div>

    <div class="notifications-form-container">
      <h2>&#128227; Create New Notification</h2>
      <form class="notifications-form modern-form" method="post" action="">
        <div class="modern-row">
          <!-- Audience -->
          <div class="modern-field">
            <label for="audience-dropdown">Audience</label>
          <span style="color: red; font-size: 12px;">*</span>
            <div class="modern-input-icon-group audience-dropdown-wrapper">
              <select id="audience-dropdown" name="audience[]" class="audience-dropdown" multiple>
                <option value="student">Students</option>
                <option value="admin">Admins</option>
                <option value="instructor">Instructors</option>
              </select>
            </div>
          </div>

          <!-- Priority -->
          <div class="modern-field">
            <label for="priority">Priority</label>
          <span style="color: red; font-size: 12px;">*</span>
            <div class="modern-input-icon-group">
              <select id="priority" name="priority" class="priority-select modern-input">
                <option value="infomation">Information</option>
                <option value="warning">Warning</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Title -->
        <div class="modern-field">
          <label for="title">Title</label>
          <span style="color: red; font-size: 12px;">*</span>
          <input type="text" id="title" name="title" class="modern-input" placeholder="Enter notification title..." required>
        </div>

        <!-- Message -->
        <div class="modern-field">
          <label for="message">Message </label>
          <span style="color: red; font-size: 12px;">*</span>
          <textarea id="message" name="message" class="modern-input" placeholder="Enter notification message..." rows="4" required></textarea>
        </div>

        <!-- Closed Date -->
        <div class="modern-field">
          <label for="closed_date">Closed Date</label>
          <span style="color: red; font-size: 12px;">*</span>
          <input type="date" id="closed_date" name="closed_date" class="modern-input" required>
        </div>

        <!-- Actions -->
        <div class="modern-actions">
          <button class="modern-btn send" type="submit" name="send_type" value="send">&#128227; Send Now</button>
          <button class="modern-btn schedule" id="schedule-btn" type="button">&#128197; Schedule</button>
        </div>

        <!-- Hidden schedule field -->
        <input type="hidden" id="hidden-schedule-date" name="hidden_schedule_date">
        <input type="hidden" id="hidden-send-type" name="send_type" value="send">
      </form>

      <!-- Schedule Modal -->
      <div id="schedule-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); justify-content:center; align-items:center; z-index:9999;">
        <div style="background:#fff; padding:24px 18px; border-radius:8px; min-width:260px; box-shadow:0 2px 8px #0002; text-align:center;">
          <h3 style="margin-bottom:12px; font-size:16px;">Select Schedule Date</h3>
          <input type="date" id="schedule-date" style="padding:6px 10px; font-size:14px; margin-bottom:16px;" required>
          <br>
          <button id="schedule-ok" style="padding:6px 18px; background:#2563eb; color:#fff; border:none; border-radius:4px; font-size:14px;">OK</button>
          <button id="schedule-cancel" style="padding:6px 18px; background:#eee; color:#333; border:none; border-radius:4px; font-size:14px; margin-left:8px;">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Choices.js JS for modern multi-select -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Multi-select setup
    const audienceSelect = document.getElementById('audience-dropdown');
    if (audienceSelect) {
      new Choices(audienceSelect, {
        removeItemButton: true,
        searchEnabled: true,
        placeholderValue: 'Select audience...'
      });
    }

    // History button logic
    const historyBtn = document.getElementById('history-btn');
    if (historyBtn) {
      historyBtn.addEventListener('click', function() {
        window.location.href = 'notifications-history.php';
      });
    }

    // Schedule logic
    const scheduleBtn = document.getElementById('schedule-btn');
    const scheduleModal = document.getElementById('schedule-modal');
    const scheduleOk = document.getElementById('schedule-ok');
    const scheduleCancel = document.getElementById('schedule-cancel');
    const scheduleDateInput = document.getElementById('schedule-date');
    const closedDateInput = document.getElementById('closed_date');
    const hiddenScheduleDate = document.getElementById('hidden-schedule-date');
    const hiddenSendType = document.getElementById('hidden-send-type');
    const form = document.querySelector('.notifications-form');

    function setScheduleDateLimits() {
      const today = new Date().toISOString().split('T')[0];
      scheduleDateInput.min = today;
      if (closedDateInput.value) {
        scheduleDateInput.max = closedDateInput.value;
      } else {
        scheduleDateInput.removeAttribute('max');
      }
    }

    if (scheduleBtn) {
      scheduleBtn.addEventListener('click', function() {
        setScheduleDateLimits();
        scheduleModal.style.display = 'flex';
      });
    }

    if (closedDateInput) {
      closedDateInput.addEventListener('change', setScheduleDateLimits);
    }

    if (scheduleCancel) {
      scheduleCancel.addEventListener('click', function() {
        scheduleModal.style.display = 'none';
        scheduleDateInput.value = '';
      });
    }

    if (scheduleOk) {
      scheduleOk.addEventListener('click', function() {
        if (scheduleDateInput.value) {
          hiddenScheduleDate.value = scheduleDateInput.value;
          hiddenSendType.value = 'schedule'; // mark as schedule
          scheduleModal.style.display = 'none';
          form.submit();
        } else {
          scheduleDateInput.focus();
        }
      });
    }
  });
</script>
