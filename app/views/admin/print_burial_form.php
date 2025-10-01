<?php
/** app/views/admin/print_burial_form.php */
$r = $data['r'] ?? null;
if (!$r) { echo '<h3 style="padding:16px">No data.</h3>'; exit; }

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$plotLabel = trim(($r->block_title ?? '').' '.($r->plot_number ?? ''));
$pay       = is_numeric($r->payment_amount ?? null) ? number_format((float)$r->payment_amount, 2) : '0.00';
$rentDisp  = !empty($r->rental_date) ? date('D-F d, Y \a\t h:i A', strtotime($r->rental_date)) : '';
$expDisp   = !empty($r->expiry_date) ? date('D-F d, Y \a\t h:i A', strtotime($r->expiry_date)) : '';
$qrUrl     = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data='.urlencode($r->burial_id);
$autoPrint = (isset($_GET['autoprint']) && $_GET['autoprint']==='1');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Burial Form – <?= e($r->burial_id) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
  :root{
    --ink:#101010;
    --muted:#707070;
    --accent:#991b1b;   /* deep red for headings/bullets */
  }

  /* ==== Print sizing (A4 & Letter) ==== */
  @page { margin: 10mm; }
  @media print {
    html,body{background:#fff}
  }

  /* Wrapper auto-scales to fit either paper: max width is 190mm (A4 inner), but
     we also clamp in inches for US Letter to avoid overflow. */
  .sheet{
    width: min(190mm, 7.8in);
    margin: 0 auto;
    min-height: calc(100vh - 20mm);
    position: relative;
    padding: 2mm 2mm 10mm;
  }

  /* Watermark */
  .sheet::after{
    content:"";
    position:absolute; inset:0;
    background:url('<?= URLROOT ?>/public/img/bglogo.png') center / 75% auto no-repeat;
    opacity:.06; pointer-events:none; z-index:0;
  }

  /* Title */
  h1{
    margin: 0 0 6mm;
    font: 700 16pt/1.15 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
    color: var(--accent);
    text-align:center;
    letter-spacing:.2px;
    position:relative; z-index:1;
  }

  /* Two columns */
  .grid{
    display:grid;
    grid-template-columns: 1fr 1fr;
    column-gap: 10mm;
    row-gap: 4mm;
    position:relative; z-index:1;
  }

  .section-title{
    color: var(--accent);
    font-weight:700; font-size:10pt;
    margin-bottom: 2mm;
  }

  /* label/value rows — NO borders, NO boxes */
  .field{display:grid; grid-template-columns: 26mm 1fr; column-gap:3mm; margin: 2mm 0;}
  .label{font-size:9pt; color:var(--muted)}
  .value{font-size:10.2pt; font-weight:600; line-height:1.26; color:var(--ink)}

  .pair{display:flex; gap:8mm}
  .muted{color:var(--muted)}

  .sig-wrap{margin-top:6mm}
  .sig-line{width:66mm; height:0; border-top:1px solid #bdbdbd; margin:7mm 0 1mm;}
  .sig-text{font-size:8.8pt; color:var(--muted)}

  /* Checklist bullets in red, no boxes */
  .checklist{list-style:none; padding:0; margin:3mm 0 0}
  .checklist li{position:relative; padding-left:5mm; margin: 1.2mm 0; font-size:9.6pt}
  .checklist li::before{
    content:"•"; position:absolute; left:0; top:-.1mm;
    color:var(--accent); font-size:14pt; line-height:1;
  }

  /* Bottom IDs + QR */
  .foot{
    position:absolute; left: 2mm; bottom: 6mm; z-index:1;
    font-size:10pt; line-height:1.3;
  }
  .qrbox{
    position:absolute; right: 2mm; bottom: 4mm; z-index:1; text-align:center;
  }
  .qrbox img{width: 34mm; height: 34mm}
  .qrbox small{color:var(--muted); font-size:9pt}

  /* Screen helper (hidden when autoprint) */
  .noprint{position:fixed;right:12px;bottom:12px;z-index:9}
  @media print {.noprint{display:none!important}}
</style>
</head>
<body<?= $autoPrint ? ' onload="window.print(); setTimeout(()=>window.close(), 150);"':'' ?>>
  <div class="sheet">

    <h1>Burial Form</h1>

    <div class="grid">

      <!-- LEFT -->
      <div>
        <div class="section-title">Burial</div>

        <div class="field"><div class="label">Plot ID:</div>        <div class="value"><?= e($plotLabel) ?></div></div>
        <div class="field"><div class="label">First Name:</div>     <div class="value"><?= e($r->deceased_first_name) ?></div></div>
        <div class="field"><div class="label">Middle Name:</div>    <div class="value"><?= e($r->deceased_middle_name) ?></div></div>
        <div class="field"><div class="label">Last Name:</div>      <div class="value"><?= e($r->deceased_last_name) ?></div></div>
        <div class="field"><div class="label">Suffix:</div>         <div class="value"><?= e($r->deceased_suffix) ?></div></div>

        <div class="field"><div class="label">Age &amp; Sex:</div>  <div class="value"><?= e(trim(($r->age ?? '').' '.$r->sex)) ?></div></div>

        <div class="pair">
          <div class="field" style="grid-template-columns:18mm 1fr">
            <div class="label">Born:</div><div class="value"><?= e($r->date_born) ?></div>
          </div>
          <div class="field" style="grid-template-columns:17mm 1fr">
            <div class="label">Died:</div><div class="value"><?= e($r->date_died) ?></div>
          </div>
        </div>

        <div class="field"><div class="label">Grave Lvl &amp; Type:</div>
          <div class="value"><?= e(($r->grave_level ?: '-').' / '.($r->grave_type ?: '-')) ?></div>
        </div>

        <div class="field"><div class="label">Cause of Death:</div> <div class="value"><?= e($r->cause_of_death) ?></div></div>

        <?php if (!empty($r->requirements)): ?>
          <div class="section-title" style="margin-top:4mm">Requirements</div>
          <ul class="checklist">
            <?php foreach (explode(',', $r->requirements) as $req): ?>
              <li><?= e(trim($req)) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- RIGHT -->
      <div>
        <div class="section-title">Interment Right Holder</div>

        <div class="field"><div class="label">Full Name:</div>      <div class="value"><?= e($r->interment_full_name) ?></div></div>
        <div class="field"><div class="label">Relationship:</div>   <div class="value"><?= e($r->interment_relationship) ?></div></div>
        <div class="field"><div class="label">Contact No.:</div>    <div class="value"><?= e($r->interment_contact_number) ?></div></div>
        <div class="field"><div class="label">Address:</div>        <div class="value"><?= e($r->interment_address) ?></div></div>

        <div class="field"><div class="label">Payment Amount:</div> <div class="value"><?= e($pay) ?></div></div>
        <div class="field"><div class="label">Rental Date:</div>    <div class="value"><?= e($rentDisp) ?></div></div>
        <div class="field"><div class="label">Expiry Date:</div>    <div class="value"><?= e($expDisp) ?></div></div>

        <div class="sig-wrap">
          <div class="sig-line"></div>
          <div class="sig-text">I certify the accuracy of everything written here. — Signature</div>
        </div>
      </div>

    </div>

    <!-- Bottom info + QR -->
    <div class="foot">
      <div><strong>Burial ID:</strong> <?= e($r->burial_id) ?></div>
      <?php if (!empty($r->transaction_id)): ?>
        <div><strong>Transaction ID:</strong> <?= e($r->transaction_id) ?></div>
      <?php endif; ?>
    </div>

    <div class="qrbox">
      <img src="<?= e($qrUrl) ?>" alt="QR">
      <small>Scan for Burial ID</small>
    </div>

    <?php if (!$autoPrint): ?>
      <div class="noprint">
        <button onclick="window.print()" style="padding:.55rem .9rem">Print</button>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
