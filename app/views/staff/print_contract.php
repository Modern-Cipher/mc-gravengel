<?php
// Main data object
$r = $data['r'] ?? null;
if (!$r) {
    echo '<h3 style="padding:16px">Burial record not found.</h3>';
    exit;
}

// Helper function to safely print data
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Helper function to convert number to Filipino words
function numberToWordsFilipino($number) {
    if (!is_numeric($number) || $number < 0) return '';
    $number = (int)$number;
    if ($number == 0) return 'ZERO';

    function num_to_words_chunk($num) {
        $words = [ 0 => '', 1 => 'ISA', 2 => 'DALAWA', 3 => 'TATLO', 4 => 'APAT', 5 => 'LIMA', 6 => 'ANIM', 7 => 'PITO', 8 => 'WALO', 9 => 'SIYAM', 10 => 'SAMPU', 11 => 'LABING-ISA', 12 => 'LABINDALAWA', 13 => 'LABINTATLO', 14 => 'LABING-APAT', 15 => 'LABINLIMA', 16 => 'LABING-ANIM', 17 => 'LABINGPITO', 18 => 'LABINGWALO', 19 => 'LABINGSIYAM' ];
        $tens = [2 => 'DALAWAMPU', 3 => 'TATLUMPU', 4 => 'APATNAPU', 5 => 'LIMAMPU', 6 => 'ANIMNAPU', 7 => 'PITUMPU', 8 => 'WALUMPU', 9 => 'SIYAMNAPU'];
        $string = '';
        $hundreds = floor($num / 100);
        $remainder = $num % 100;
        if ($hundreds > 0) $string .= $words[$hundreds] . ' NA RAAN';
        if ($remainder > 0) {
            if ($hundreds > 0) $string .= ' ';
            if ($remainder < 20) {
                $string .= $words[$remainder];
            } else {
                $ten = floor($remainder / 10);
                $one = $remainder % 10;
                $string .= $tens[$ten];
                if ($one > 0) $string .= "'T " . $words[$one];
            }
        }
        return $string;
    }

    $parts = [];
    $billions = floor($number / 1000000000);
    $millions = floor(($number % 1000000000) / 1000000);
    $thousands = floor(($number % 1000000) / 1000);
    $units = $number % 1000;
    if ($billions > 0) $parts[] = num_to_words_chunk($billions) . ' BILYON';
    if ($millions > 0) $parts[] = num_to_words_chunk($millions) . ' MILYON';
    if ($thousands > 0) $parts[] = ($thousands == 1 ? 'ISANG' : num_to_words_chunk($thousands)) . ' LIBO';
    if ($units > 0) $parts[] = num_to_words_chunk($units);
    return implode(' ', $parts);
}

// Prepare data for the template
$nitso_blg = e($r->plot_number);
$uupa_pangalan = e(mb_strtoupper($r->interment_full_name, 'UTF-8'));
$uupa_tirahan = e(mb_strtoupper($r->interment_address, 'UTF-8'));
$grave_type = e($r->grave_type);
$halaga_numero = e(number_format((float)$r->payment_amount, 2));
$halaga_salita = e(mb_strtoupper(numberToWordsFilipino((int)$r->payment_amount), 'UTF-8'));

// Check for autoprint parameter
$autoPrint = (isset($_GET['autoprint']) && $_GET['autoprint'] === '1');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>KASUNDUAN NG PAGPAPAUPA – <?= e($r->burial_id) ?></title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 0.5in;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 0;
        }
        .page {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 100%;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        .left-column {
            flex: 0 0 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
        }
        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-weight: bold;
            font-size: 8pt;
            white-space: nowrap;
            text-transform: uppercase;
        }
        .main-content {
            flex-grow: 1;
            padding-left: 20px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; text-underline-offset: 4px; }
        .fill-in { text-decoration: underline; text-underline-offset: 4px; font-weight: bold; padding: 0 4px; display: inline-block; min-width: 150px; text-align: center; }
        .header-title { font-size: 14pt; font-weight: bold; text-align: center; margin: 24px 0; }
        .content { text-align: justify; text-indent: 2em; }
        ol { padding-left: 3em; text-align: justify; }
        ol li { margin-bottom: 1em; }
        .signature-section { margin-top: 48px; display: flex; justify-content: space-around; }
        .signature-block { text-align: center; width: 45%; }
        .signature-line { border-top: 1px solid black; margin-top: 60px; font-weight: bold; }
        .witness-section { margin-top: 48px; }
        .notary-section { margin-top: 36px; }
        .print-btn-container { position: fixed; bottom: 20px; right: 20px; z-index: 100; }
        .print-btn { padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #f0f0f0; border: 1px solid #ccc; border-radius: 5px; }
        @media print { .print-btn-container { display: none; } }
    </style>
</head>
<body<?= $autoPrint ? ' onload="window.print();"' : '' ?>>

<div class="page">
    <div class="left-column">
        <div class="vertical-text">JOCELL AIMEE R. VISTAN-CASAJE – Municipal Mayor</div>
        <div class="vertical-text"><?= $uupa_pangalan ?> – Uupa</div>
        <div class="vertical-text">MA. THERESA M. LEONZON – Municipal Treasurer</div>
    </div>
    <div class="main-content">
        <div class="text-right font-bold">Nitso Blg. <span class="underline">&nbsp;&nbsp;<?= $nitso_blg ?>&nbsp;&nbsp;</span></div>
        <div class="header-title">KASUNDUAN NG PAGPAPAUPA NG NITSO/PUWESTO SA<br>PAMBAYANG LIBINGAN</div>
        <p class="font-bold">ALAMIN NG LAHAT:</p>
        <p class="content">Ang kasunduang ito na isinagawa at nilagdaan ng PAMAHALAANG BAYAN NG PLARIDEL, na dito ay kinakatawan ng PUNONG BAYAN <span class="font-bold">KGG. JOCELL AIMEE R. VISTAN-CASAJE</span> na makikilala sa kasunduang ito bilang <span class="font-bold">PAMAHALAAN</span>, at ni <span class="fill-in"><?= $uupa_pangalan ?></span>, may sapat na gulang, Pilipino, walang/may asawa, na naninirahan sa <span class="fill-in"><?= $uupa_tirahan ?></span> na makikilala bilang <span class="font-bold">UUPA</span>, ay nagpapatunay at nagsasalaysay, na:</p>
        <ol>
            <li>Ang PAMAHALAAN ay pumapayag na ipaupa sa uupa ang isang puwesto na <span class="font-bold underline">&nbsp;<?= e($grave_type) ?> type&nbsp;</span> sa Pambayang Libingan na may sukat na 1x1x2.54 metro kuwadrado sa halagang <span class="font-bold underline">&nbsp;<?= $halaga_salita ?> PISO (Php <?= $halaga_numero ?>)&nbsp;</span>. Ito ay magsisimula sa <span class="font-bold underline"><span class="print-month"></span> <span class="print-day"></span>, <span class="print-year"></span></span> at magtatapos pagkaraan ng LIMANG (5) taon. Maaari itong mag-renew ayon sa itinakdang bayarin at kung walang paglabag sa kasunduan at mga batas ng Pambayang Libingan.</li>
            <li>Ang bawat UUPA ay nangangakong magbabayad ng mga sumusunod:
                <div style="margin-left: 4em; display: grid; grid-template-columns: 1fr 1fr;">
                    <span>a. Application Fee</span><span>P50.00</span>
                    <span>b. Burial Fee</span><span>P30.00</span>
                    <span>c. Halaga o renta</span><span>P<?= $halaga_numero ?> (sa loob ng 5 taong upa)</span>
                    <span>d. Notary</span><span>P100.00</span>
                </div>
            </li>
            <li>Nangangako at naiintindihan ng UUPA na pagkatapos ng LIMANG (5) TAON kung hindi ito nag renew ng pag upa, binibigyang pahintulot ko ang Pamahalaang Bayan na ilipat ang mga buto na nasa loob nito sa isang pang maramihang Krypt o Nitso.</li>
            <li>Tinitiyak ng UUPA na hindi siya gagawa ng anumang pagbabago at pagsasaayos sa nitso o puwesto nang walang nasusulat na pahintulot mula sa PAMAHALAAN.</li>
            <li>Hindi maaaring ilipat, ipagbili, ipaubaya o ipaupa sa iba ang kanyang karapatang pag-upa sa ilalim ng kasunduang ito.</li>
            <li>Kaugnay ng naunang probisyon, hindi kikilalanin ng PAMAHALAAN ang sinumang pinagbilan, pinagpaubayaan, o pinagpaupahan ng UUPA kaugnay ng nitso o pwestong inuupahan.</li>
            <li>Ipinangangako ng UUPA na hindi niya sasakupin ang daanan at harapan ng bawat nitso o puwesto, na magiging sagabal sa katabing nitso o puwesto nito.</li>
            <li>Ang numero ng nitso o puwesto na nakalagay sa kanang bahagi nito ay pirmihang numero na inilagay ng Pamahalaang Bayan na hindi dapat takpan o alisin ng sinumang tao o uupa.</li>
            <li>Ipinapangako ng UUPA na pananatilihing malinis ang lugar o puwesto na naaayon sa pinag-uutos ng batas at mga kautusang bayan.</li>
            <li>Kung may paglabag na mangyari sa panig ng UUPA, ang PAMAHALAAN ay may karapatan ipawalang bisa ang kasunduang ito.</li>
        </ol>
        <p>NILAGDAAN ngayong ika <span class="underline">&nbsp;<span class="print-day"></span>&nbsp;</span> ng <span class="underline">&nbsp;<span class="print-month"></span>&nbsp;</span>, <span class="underline">&nbsp;<span class="print-year"></span>&nbsp;</span> sa Bayan ng Plaridel, Lalawigan ng Bulacan.</p>
        <div class="signature-section">
            <div class="signature-block">
                <div class="font-bold">PARA SA PAMAHALAAN</div>
                <div class="signature-line">JOCELL AIMEE R. VISTAN-CASAJE</div>
                <div>Punong Bayan</div>
            </div>
            <div class="signature-block">
                <div class="signature-line"><?= $uupa_pangalan ?></div>
                <div>Uupa</div>
            </div>
        </div>
    </div>
</div>

<div class="page">
    <div class="left-column">
        <div class="vertical-text">JOCELL AIMEE R. VISTAN-CASAJE – Municipal Mayor</div>
        <div class="vertical-text"><?= $uupa_pangalan ?> – Uupa</div>
        <div class="vertical-text">MA. THERESA M. LEONZON – Municipal Treasurer</div>
    </div>
    <div class="main-content">
        <div class="signature-section" style="margin-top: 1in; justify-content:space-between;">
            <div class="signature-block" style="width: 30%;"><div class="signature-line">MA. THERESA M. LEONZON</div><div>Municipal Treasurer</div></div>
            <div class="signature-block" style="width: 30%;"><div class="signature-line">JOCELL AIMEE R. VISTAN-CASAJE</div><div>Municipal Mayor</div></div>
            <div class="signature-block" style="width: 30%;"><div class="signature-line"><?= $uupa_pangalan ?></div><div>UUPA</div></div>
        </div>
        <div class="witness-section">
            <p class="font-bold">NILAGDAAN SA HARAP NINA:</p>
            <div style="display: flex; justify-content: space-around; margin-top: 60px;">
                <div style="width: 40%; text-align: center;"><div style="border-bottom: 1px solid black;"></div><p>Saksi</p></div>
                <div style="width: 40%; text-align: center;"><div style="border-bottom: 1px solid black;"></div><p>Saksi</p></div>
            </div>
        </div>
        <div class="notary-section">
            <h4 class="text-center font-bold">PAGPAPATUNAY</h4>
            <p class="content">REPUBLIKA NG PILIPINAS)<br>LALAWIGAN NG BULACAN ) S.S.</p>
            <p class="content">Sa HARAP KO, isang Notaryo Publiko, humarap sina Punong Bayan Kgg. <span class="font-bold">JOCELL AIMEE R. VISTAN-CASAJE</span> na may CTC Blg. <span class="underline">&nbsp;27933179&nbsp;</span> gawad sa <span class="underline">&nbsp;PLARIDEL, BULACAN&nbsp;</span> noong <span class="underline">&nbsp;ENERO 20, <span class="print-year"></span>&nbsp;</span> at si <span class="fill-in"><?= $uupa_pangalan ?></span> na may CTC Blg./Comelec ID.# <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> gawad sa <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> noong <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> naunang kasunduan ng pagpapa-upa ng Nitso/Puwesto sa Pambayang Libingan, at pinatutunayan nila sa harap ko na ay isinagawa nila ng bukal at naayon sa kanilang kalooban.</p>
            <p class="content">Ang kasunduang ito na may dalawang (2) pahina ay nilagdaan sa bawat pahina ng bawat panig at kanilang mga saksi.</p>
            <p class="content">NILAGDAAN ngayong ika-<span class="underline">&nbsp;<span class="print-day"></span>&nbsp;</span> ng <span class="underline">&nbsp;<span class="print-month"></span>&nbsp;</span>, <span class="underline">&nbsp;<span class="print-year"></span>&nbsp;</span>, sa Plaridel, Bulacan.</p>
            <div style="margin-top: 80px; text-align: right;">
                <div style="display: inline-block; text-align: center;">
                    <div style="border-bottom: 1px solid black; width: 250px; margin-bottom: 5px;"></div>
                    <span class="font-bold">NOTARYO PUBLIKO</span>
                </div>
            </div>
            <div style="margin-top: 36px;">
                <p>Dok. Blg. <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
                <p>Pahina Blg. <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
                <p>Aklat Blg. <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></p>
                <p>Taon ng <span class="underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>.</p>
            </div>
        </div>
    </div>
</div>

<?php if (!$autoPrint): ?>
<div class="print-btn-container">
    <button class="print-btn" onclick="window.print()">Print Contract</button>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawDateString = '<?= e($r->rental_date) ?>';
    if (!rawDateString) {
        return;
    }
    const monthsFilipino = [
        'ENERO', 'PEBRERO', 'MARSO', 'ABRIL', 'MAYO', 'HUNYO',
        'HULYO', 'AGOSTO', 'SETYEMBRE', 'OKTUBRE', 'NOBYEMBRE', 'DISYEMBRE'
    ];
    const date = new Date(rawDateString);
    const day = date.getDate();
    const month = monthsFilipino[date.getMonth()];
    const year = date.getFullYear();
    document.querySelectorAll('.print-day').forEach(el => el.textContent = day);
    document.querySelectorAll('.print-month').forEach(el => el.textContent = month);
    document.querySelectorAll('.print-year').forEach(el => el.textContent = year);
});
</script>

</body>
</html>