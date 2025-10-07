<?php
/** app/views/staff/print_qr_ticket.php */
$r = $data['r'] ?? null;
if (!$r) {
    echo '<h3 style="padding:16px">Record not found.</h3>';
    exit;
}

// Helper function to safely print data
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// --- ITO YUNG BAHAGI NA KUMUKUHA NG DATA ---
// Kinukuha nito ang laman ng column na 'interment_full_name' mula sa database.
// Kung ano ang buong pangalan na naka-save doon, 'yun ang ilalagay niya sa variable na $interment_name.
$interment_name = e($r->interment_full_name); 
$burial_id = e($r->burial_id);
$transaction_id = e($r->transaction_id ?? 'N/A');
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($r->burial_id);
$seal_logo_url = URLROOT . '/public/img/seal.png';
$wing_logo_url = URLROOT . '/public/img/bwlogo.png';

// Check for autoprint parameter
$autoPrint = (isset($_GET['autoprint']) && $_GET['autoprint'] === '1');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Ticket – <?= e($r->burial_id) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-content: flex-start;
        }
        .ticket {
            width: 105mm; /* A6 width */
            height: 148.5mm; /* A6 height */
            box-sizing: border-box;
            padding: 8mm;
            display: flex;
            flex-direction: column;
            text-align: center;
            border: 1px dashed #888; /* Cutting guide */
        }
        
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 8mm;
        }
        .logo {
            width: 20mm;
            height: 20mm;
            object-fit: contain;
        }
        .header-text {
            text-align: center;
        }
        .header-text strong {
            font-size: 11pt;
            display: block;
        }
        .header-text small {
            font-size: 8pt;
            color: #555;
        }
        
        .ticket-body {
            flex-grow: 1;
        }
        .ticket-body .qr-code {
            width: 45mm;
            height: 45mm;
            margin: 0 auto 5mm;
        }
        .ticket-body .holder-name {
            margin-top: 5mm;
            font-size: 11pt;
        }
        .ticket-body .reminder {
            font-size: 9pt;
            color: #333;
            margin-top: 8mm;
            line-height: 1.4;
            text-align: justify;
        }

        .ticket-footer {
            margin-top: auto;
            text-align: left;
        }
        .ticket-footer p {
            font-size: 12pt;
            font-weight: bold;
            margin: 5mm 0 0;
        }
        .ticket-footer strong {
            font-family: monospace;
        }

        .print-btn-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
        }
        .print-btn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        @media print {
            .print-btn-container {
                display: none;
            }
        }
    </style>
</head>
<body<?= $autoPrint ? ' onload="window.print();"' : '' ?>>

    <?php for ($i = 0; $i < 4; $i++): ?>
    <div class="ticket">
        <div class="ticket-header">
            <img src="<?= $seal_logo_url ?>" alt="Seal" class="logo">
            <div class="header-text">
                <strong>Plaridel Public Cemetery</strong>
                <small>Sto. Nino, Plaridel, Bulacan</small>
            </div>
            <img src="<?= $wing_logo_url ?>" alt="Logo" class="logo">
        </div>

        <div class="ticket-body">
            <img src="<?= $qr_url ?>" alt="QR Code" class="qr-code">
            <p class="holder-name">
                Interment Right Holder: <strong><?= $interment_name ?></strong>
            </p>
            <p class="reminder">
                <strong>Reminder:</strong> This QR code serves as a verification tool to access and validate the official digital record of the burial. Please ensure this code is retained and presented during any renewal transaction.
            </p>
        </div>

        <div class="ticket-footer">
            <p>Burial ID: <strong><?= $burial_id ?></strong></p>
            <p>Transaction ID: <strong><?= $transaction_id ?></strong></p>
        </div>
    </div>
    <?php endfor; ?>

    <?php if (!$autoPrint): ?>
    <div class="print-btn-container">
        <button class="print-btn" onclick="window.print()">Print Tickets</button>
    </div>
    <?php endif; ?>

</body>
</html>