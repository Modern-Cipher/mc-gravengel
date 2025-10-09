<?php
// Accept either $data or $report
$report = isset($report) ? $report : ($data ?? []);
$maroon = '#800000';
$dark   = '#5a0000';
$light_maroon = '#a75a5a';
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>System Report Notification</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{margin:0;background:#f5f5f7;font-family:Arial,Helvetica,sans-serif;color:#222;line-height:1.55}
  .wrap{max-width:640px;margin:24px auto;padding:0 12px}
  .card{background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.06);overflow:hidden;border:1px solid #eee}
  .bar{height:6px;background:<?php echo $maroon; ?>}
  .head{padding:18px 20px;border-bottom:1px solid #eee}
  .title{margin:0;font-size:20px;color:<?php echo $maroon; ?>}
  .badge{display:inline-block;background:<?php echo $maroon; ?>;color:#fff;border-radius:999px;padding:3px 10px;font-size:12px;margin-top:6px}
  .body{padding:18px 20px}
  .section{background:#fafafa;border:1px solid #eee;border-radius:8px;padding:12px 12px 6px;margin-bottom:14px}
  .section h3{margin:0 0 8px;font-size:14px;color:<?php echo $light_maroon; ?>;border-bottom:1px dashed #ddd;padding-bottom:6px}
  .row{margin:0 0 6px}
  .row strong{display:inline-block;width:130px;color:<?php echo $maroon; ?>}
  .msg{background:#fff;border:2px solid #eee;border-radius:8px;padding:12px;white-space:pre-wrap;word-wrap:break-word}
  .foot{padding:14px 20px;border-top:1px solid #eee;font-size:12px;color:#666;text-align:center}
  .link{color:<?php echo $maroon; ?>;text-decoration:none}
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="bar"></div>
      <div class="head">
        <h1 class="title">System Report</h1>
        <span class="badge"><?php echo h($report['subject'] ?? 'New System Inquiry'); ?></span>
      </div>
      <div class="body">
        <div class="section">
          <h3>Reporter Details</h3>
          <div class="row"><strong>Reported By:</strong> <?php echo h($report['reporter_name'] ?? 'N/A'); ?></div>
          <div class="row"><strong>Email:</strong> <a class="link" href="mailto:<?php echo h($report['reporter_email'] ?? ''); ?>"><?php echo h($report['reporter_email'] ?? 'N/A'); ?></a></div>
          <div class="row"><strong>Contact:</strong> <?php echo h($report['contact_number'] ?? 'N/A'); ?></div>
        </div>

        <div class="section">
          <h3>System User Info</h3>
          <div class="row"><strong>Logged User:</strong> <?php echo h($report['current_user'] ?? 'N/A'); ?></div>
          <div class="row"><strong>User ID:</strong> <?php echo h($report['current_user_id'] ?? 'N/A'); ?></div>
          <div class="row"><strong>Role:</strong> <?php echo h($report['current_user_role'] ?? 'N/A'); ?></div>
          <div class="row"><strong>Designation:</strong> <?php echo h($report['current_user_designation'] ?? 'N/A'); ?></div>
        </div>

        <div class="section">
          <h3>Issue Summary</h3>
          <div class="row"><strong>Subject/Type:</strong> <?php echo h($report['subject'] ?? 'N/A'); ?></div>
        </div>

        <div class="section">
          <h3>Detailed Message</h3>
          <div class="msg"><?php echo h($report['message'] ?? 'No detailed message provided.'); ?></div>
        </div>
      </div>
      <div class="foot">
        This is an automated system notification from the Plaridel Public Cemetery management application.<br>
        &copy; <?php echo date('Y'); ?> All rights reserved. |
        <a class="link" href="mailto:<?php echo h($report['developer_email'] ?? ''); ?>">Development Team</a>
      </div>
    </div>
  </div>
</body>
</html>
