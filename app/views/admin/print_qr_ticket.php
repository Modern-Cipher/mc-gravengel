<?php
$r = $data['r'] ?? null;
if (!$r) { echo '<h3 style="padding:16px">No data.</h3>'; exit; }
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode($r->burial_id);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>QR Ticket – <?= e($r->burial_id) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  .box{max-width:900px;margin:24px auto;padding:24px;border:1px solid #ddd;border-radius:12px}
  .logo{width:120px;height:120px;object-fit:contain}
  .qr{width:180px;height:180px;object-fit:contain}
</style>
</head>
<body>
<div class="box d-flex align-items-start gap-3">
  <img src="<?= URLROOT ?>/public/img/bwlogo.png" class="logo" alt="Logo">
  <div class="flex-grow-1">
    <h4 class="mb-3">QR Ticket</h4>
    <p>This is the QR code issued by <em>Plaridel Public Cemetery</em>.
       Please ensure the IRH understands the importance of keeping a printed copy of the QR code.</p>

    <img src="<?= e($qrUrl) ?>" class="qr mb-3" alt="QR">

    <div><strong>Burial ID:</strong> <?= e($r->burial_id) ?></div>
    <div><strong>Transaction ID:</strong> <?= e($r->transaction_id ?? '') ?></div>
  </div>
</div>
<div class="text-end" style="max-width:900px;margin:0 auto 24px;">
  <button class="btn btn-secondary" onclick="window.print()">Print</button>
</div>
</body>
</html>
