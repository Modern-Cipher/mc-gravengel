<?php
/** app/views/admin/print_burial_form.php */
$r = $data['r'] ?? null;
if (!$r) {
    echo '<h3 style="padding:16px">No data found.</h3>';
    exit;
}

// Helper function to safely print data
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Prepare data for the template
$deceased_full_name = trim(e($r->deceased_first_name) . ' ' . e($r->deceased_middle_name) . ' ' . e($r->deceased_last_name) . ' ' . e($r->deceased_suffix));
$interment_full_name = e(mb_strtoupper($r->interment_full_name, 'UTF-8'));
$interment_address = e(mb_strtoupper($r->interment_address, 'UTF-8'));
$date_died = !empty($r->date_died) ? mb_strtoupper(date('F d, Y', strtotime($r->date_died)), 'UTF-8') : '';
$date_of_burial = !empty($r->rental_date) ? mb_strtoupper(date('F d, Y / h:i A', strtotime($r->rental_date)), 'UTF-8') : '';
$issued_day = date('d');
$issued_month = mb_strtoupper(date('F'), 'UTF-8');
$issued_year = date('Y');
$plot_details = e($r->block_title . ' (' . $r->plot_number . ')');
$payment_amount = is_numeric($r->payment_amount ?? null) ? 'P ' . number_format((float)$r->payment_amount, 2) : 'P 0.00';
$transaction_id = e($r->transaction_id ?? 'N/A');
$rental_date_only = !empty($r->rental_date) ? mb_strtoupper(date('F d, Y', strtotime($r->rental_date)), 'UTF-8') : '';

$autoPrint = (isset($_GET['autoprint']) && $_GET['autoprint']==='1');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pahintulot sa Paglilibing – <?= e($r->burial_id) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: 8.5in 11in; /* US Letter size */
            margin: 0.75in;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #000;
        }
        .sheet {
            max-width: 7in;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            padding: 0;
            text-decoration: underline;
        }
        .content p {
            text-align: justify;
            text-indent: 2em;
            margin-bottom: 20px;
        }
        .underline-value {
            text-decoration: underline;
            font-weight: bold;
            padding: 0 5px;
        }
        .data-grid {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 8px 15px;
            margin-left: 2em;
            margin-bottom: 20px;
        }
        .data-label {
            font-weight: normal;
        }
        .data-value {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            min-height: 1.2em;
        }
        .two-col-grid {
            grid-template-columns: 160px 1fr 100px 1fr;
        }
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
            font-size: 12pt;
        }
        .checkbox-area {
            display: flex;
            gap: 30px;
            margin-left: 2em;
            margin-bottom: 10px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
        }
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 1px solid #000;
            margin-right: 10px;
        }
        .footer {
            margin-top: 30px;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
        }
        .signature-block {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .print-btn-container { position: fixed; bottom: 20px; right: 20px; }
        .print-btn { padding: 10px 20px; font-size: 16px; cursor: pointer; background-color:#f0f0f0; border: 1px solid #ccc; border-radius: 4px; }
        @media print { .print-btn-container { display: none; } }
    </style>
</head>
<body<?= $autoPrint ? ' onload="window.print(); setTimeout(()=>window.close(), 150);"':'' ?>>

<div class="sheet">
    <div class="header">
        <h1>PAHINTULOT SA PAGLILIBING</h1>
    </div>

    <div class="content">
        <p>
            Binibigyang pahintulot si <span class="underline-value"><?= $interment_full_name ?></span>, naninirahan sa <span class="underline-value"><?= $interment_address ?></span> na makapaglibing sa Pambayang Libingan ng Plaridel sa Barangay Sto. Niño Plaridel, Bulacan.
        </p>
    </div>

    <div class="data-grid">
        <div class="data-label">Pangalan:</div>
        <div class="data-value"><?= $deceased_full_name ?></div>
        
        <div class="data-label">Petsa ng Pagkamatay:</div>
        <div class="data-value"><?= $date_died ?></div>
        
        <div class="data-label">Lugar ng Pagkamatay:</div>
        <div class="data-value"></div> <div class="data-label">Registry No:</div>
        <div class="data-value"></div> </div>

    <div class="data-grid two-col-grid">
        <div class="data-label">Lot No:</div>
        <div class="data-value"></div>
        <div class="data-label">Unit No:</div>
        <div class="data-value"></div>
        <div class="data-label">Apartment No.</div>
        <div class="data-value" style="grid-column: 2 / span 3;"><?= $plot_details ?></div>
    </div>
    
    <div class="data-grid">
        <div class="data-label">Sepulturero/Cemetery Caretaker:<br>(Pangalan at Lagda)</div>
        <div class="data-value"></div>
    </div>

    <p style="margin-left: 2em; margin-bottom: 5px;">Kung mayroon:</p>
    <div class="checkbox-area">
        <div class="checkbox-item"><div class="checkbox"></div> May exhumation permit</div>
        <div class="checkbox-item"><div class="checkbox"></div> Walang exhumation permit</div>
    </div>
    <div class="data-grid">
        <div class="data-label">Pangalan ng nakalibing:</div>
        <div class="data-value"></div>
        <div class="data-label">Petsa ng paglilibing:</div>
        <div class="data-value"><?= $date_of_burial ?></div>
    </div>

    <div style="border-top: 1px solid black; border-bottom: 1px solid black; padding: 10px 0; margin-bottom: 20px;">
        <div class="section-title">Detalye ng resibo:</div>
        <div class="data-grid" style="margin-left:0;">
            <div class="data-label">Cemetery Fee:</div><div class="data-value"></div>
            <div class="data-label">OR No.</div><div class="data-value"></div>
            <div class="data-label">Control No.</div><div class="data-value"></div>
            <div class="data-label">Petsa:</div><div class="data-value"></div>
        </div>
    </div>
    
    <div style="border-top: 1px solid black; border-bottom: 1px solid black; padding: 10px 0;">
        <div class="section-title">Burial Permit</div>
        <div class="data-grid" style="margin-left:0;">
            <div class="data-label">OR NO.:</div><div class="data-value"><?= $transaction_id ?></div>
            <div class="data-label">Petsa:</div><div class="data-value"><?= $rental_date_only ?></div>
            <div class="data-label">Halaga:</div><div class="data-value"><?= $payment_amount ?></div>
        </div>
    </div>

    <div class="footer">
        <p>Ibinigay ngayong ika-<span class="underline-value"><?= $issued_day ?></span> ng <span class="underline-value"><?= $issued_month ?></span> <span class="underline-value"><?= $issued_year ?></span> sa Plaridel, Bulacan.</p>
        
        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-line">MA. THERESA M. LEONZON</div>
                Municipal Treasurer
            </div>
            <div class="signature-block">
                <div class="signature-line">JOCELL AIMEE R. VISTAN-CASAJE</div>
                Punong Bayan
            </div>
        </div>
    </div>

</div>

<?php if (!$autoPrint): ?>
<div class="print-btn-container">
    <button class="print-btn" onclick="window.print()">Print Form</button>
</div>
<?php endif; ?>

</body>
</html>