<?php
$r = $data['r'] ?? null;
if (!$r) { echo '<h3 style="padding:16px">No data.</h3>'; exit; }
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Official Contract – <?= e($r->burial_id) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
  .box{max-width:900px;margin:24px auto;padding:24px;border:1px solid #ddd;border-radius:12px}
  .seal{width:160px;height:160px;object-fit:contain}
</style>
</head>
<body>
<div class="box d-flex align-items-start gap-3">
  <img src="<?= URLROOT ?>/public/img/seal.png" class="seal" alt="Seal">
  <div class="lh-lg">
    <h4 class="mb-3">Official Contract</h4>
    <p>
      This is an official contract issued to <strong><?= e($r->interment_full_name) ?></strong>
      by <em>Plaridel Public Cemetery</em>. You may print or download this receipt as proof of
      payment and record ownership. Please retain a copy for your records.
    </p>
  </div>
</div>
<div class="text-end" style="max-width:900px;margin:0 auto 24px;">
  <button class="btn btn-secondary" onclick="window.print()">Print</button>
</div>
</body>
</html>
