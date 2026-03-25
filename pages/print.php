<?php
/**
 * Форма печати квитанции (Texmobile style)
 */

require_once __DIR__ . '/../config/functions.php';

 $repairId = intval($_GET['id'] ?? 0);
if (!$repairId) {
    die('Не указан ID ремонта');
}

 $repair = getRepair($repairId);
if (!$repair) {
    die('Ремонт не найден');
}

 $branch = [
    'company' => 'SIA TEXMOBILE',
    'address' => 'Rīga, Krišjana Barona 39, LV-1011',
    'phone' => '(+371) 20137060',
    'site' => 'www.texmobile.lv',
    'email' => 'texmobile@inbox.lv',
    'work_time' => "I-V:10:00-19:00\nVI: 10:00-17:00\nVII:brīv.",
    'legal_info' => 'SIA Texmobile, PVN reģ. Nr. LV40103567462, Latvija, Rīga, Rudens iela 6-75, LV-1082, SwedBank A/S HABALV22, Konts: LV53HABA0551033559802'
];

function formatPrintDate($date) {
    if (!$date) return '-';
    return (new DateTime($date))->format('d.m.Y');
}

function getComplectation($battery, $charger) {
    $items = [];
    if ($battery) $items[] = 'baterija';
    if ($charger) $items[] = 'lādētājs';
    return empty($items) ? 'nav' : implode(', ', $items);
}

 $repairCostOnly = ($repair['repair_price'] ?? 0);
 $diagnosticCost = ($repair['diagnostic_price'] ?? 0);
 $totalCost = $repairCostOnly + $diagnosticCost;
 $paid = ($repair['client_paid'] ?? 0);
 $remainder = $totalCost - $paid;

 $warrantyLabels = [
    '14' => '14 dienas', '30' => '30 dienas', '60' => '60 dienas',
    '90' => '90 dienas', '180' => '6 mēneši', '365' => '1 gads'
];
 $warranty = $warrantyLabels[$repair['warranty_period']] ?? ($repair['warranty_period'] ? $repair['warranty_period'] . ' dienas' : '-');
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <!--<title>Remonta kvitancija #<?php echo htmlspecialchars($repair['repair_number']); ?></title>-->
	<title>Remonta kvitancija<</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #000; background: #fff; padding: 10px; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
        
        .print-buttons { position: fixed; top: 10px; right: 10px; display: flex; gap: 10px; z-index: 1000; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn-primary { background: #e67e22; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .main-table td { vertical-align: top; padding: 0; }
        .left-col { width: 60%; }
        .right-col { width: 40%; text-align: right; }
        .block { margin-bottom: 8px; }
        .block p { margin-bottom: 2px; }
        
        .cost-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .cost-table td { padding: 4px 6px; border: 1px solid #000; font-size: 11px; }
        .cost-table .col-desc { width: 75%; }
        .cost-table .col-price { width: 25%; text-align: right; white-space: nowrap; }
        .cost-table .note-cell { border: none; border-top: 1px solid #000; font-size: 9px; vertical-align: top; padding-top: 4px; }
        .cost-table .total-label { text-align: right; font-weight: bold;  border: none}
        .paid-row .col-desc, .remainder-row .col-desc { text-align: right; border-left: 1px solid #000; }
        
        .notes-box { border: 1px solid #000; padding: 5px; margin-bottom: 10px; min-height: 30px; }
        .notes-box legend { font-weight: bold; padding: 0 5px; font-size: 11px; }
        
        .rules-section { margin-bottom: 10px; }
        .rules-section > p { font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        .rules-section ol { margin-left: 18px; font-size: 12px; line-height: 1.4; }
        .rules-section li { margin-bottom: 2px; }
        
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .signature-table td { padding: 2px 4px; vertical-align: bottom; font-size: 11px; }
        .signature-table .underline { border-bottom: 1px solid #000; min-width: 130px; text-align: center; padding-bottom: 2px; }
        .signature-table .small-text { font-size: 8px; color: #666; text-align: center; padding-top: 1px; }
        
        .footer-info { font-size: 8px; color: #666; text-align: center; margin-top: 10px; padding-top: 5px; border-top: 1px solid #ccc; }
        .defect-table { border: none; margin: 3px 0; }
        .defect-table td { padding: 0; border: none; vertical-align: top; }
        .right-col p { margin-bottom: 2px; font-size: 11px; }
    </style>
</head>
<body>
	<HR><br>

<div class="print-buttons no-print">
    <button class="btn btn-primary" onclick="window.print()">🖨️ Печать</button>
    <a href="/pages/repair-view.php?id=<?php echo $repairId; ?>" class="btn btn-secondary">← Назад</a>
</div>

<table class="main-table">
<tr>
<td class="left-col">
    <div class="block">
        <p><b>Kvīts Nr.:</b> <?php echo htmlspecialchars($repair['repair_number']); ?></p>
        <p><b>Klients:</b> <?php echo htmlspecialchars($repair['client_name']); ?> </p>
		<p><b>Tel.:</b> <?php echo htmlspecialchars($repair['client_phone']); ?></p>
    </div>
    <div class="block">
        <p><b>Ierīces modelis:</b> <?php echo htmlspecialchars(trim(($repair['device_brand'] ?? '') . ' ' . ($repair['device_model'] ?? ''))); ?></p>
        <p><b>Ierīce SN:</b> <?php echo htmlspecialchars($repair['device_serial'] ?? '-'); ?></p>
        <p><b>Drošības kods:</b> <?php echo htmlspecialchars($repair['security_code'] ?? '-'); ?></p>
        <p><b>Komplektācijā:</b> <?php echo getComplectation($repair['battery'] ?? 0, $repair['charger'] ?? 0); ?></p>
        <table class="defect-table"><tr>
            <td><b>Defekta apraksts:</b>&nbsp;</td>
            <td><?php echo htmlspecialchars($repair['problem_description'] ?? '-'); ?></td>
        </tr></table>
    </div>
    <div class="block">
        <p><b>Garantijas periods:</b> <?php echo $warranty; ?></p>
        <p><b>Pieņemšanas datums:</b> <?php echo formatPrintDate($repair['received_at']); ?></p>
        <p><b>Aptuvenais remonta datums:</b> <?php echo formatPrintDate($repair['estimated_repair_date']); ?></p>
    </div>
</td>
<td class="right-col">
    <div class="block">
        <p><b>Firma:</b> <?php echo htmlspecialchars($branch['company']); ?></p>
        <p><b>Filials:</b> <?php echo htmlspecialchars($branch['address']); ?></p>
        <p><b>Tel.:</b> <?php echo htmlspecialchars($branch['phone']); ?></p>
        <p><b>Mājaslapa:</b> <?php echo htmlspecialchars($branch['site']); ?></p>
        <p><b>E-pasts:</b> <?php echo htmlspecialchars($branch['email']); ?></p>
        <p><b>Darba laiks:</b><br><span style="white-space: pre-line;"><?php echo htmlspecialchars($branch['work_time']); ?></span></p>
    </div>
    <fieldset class="notes-box">
        <legend>&nbsp;Piezīmes&nbsp;</legend>
        <div style="font-size: 10px;"><?php echo htmlspecialchars($repair['notes'] ?? ''); ?></div>
    </fieldset>
</td>
</tr>
</table>

<table class="cost-table">
<?php if ($diagnosticCost > 0): ?>
<tr>
    <td class="col-desc"  colspan="2">&nbsp;Diagnostika</td>
    <td class="col-price"><?php echo number_format($diagnosticCost, 2, '.', ' '); ?>&nbsp;€&nbsp;</td>
</tr>
<?php endif; ?>
<?php if ($repairCostOnly > 0): ?>
<tr>
    <td class="col-desc"  colspan="2">&nbsp;Remonts (detaļas cena ir iekļauta)</td>
    <td class="col-price"><?php echo number_format($repairCostOnly, 2, '.', ' '); ?>&nbsp;€&nbsp;</td>
</tr>
<?php endif; ?>
<tr>
    <td class="note-cell">*cenas ir noradītas ar PVN</td>
    <td class="total-label">&nbsp;Kopēja cena (ar PVN):</td>
    <td class="col-price"><?php echo number_format($totalCost, 2, '.', ' '); ?>&nbsp;€&nbsp;</td>
</tr>
<tr class="paid-row">
    <td class="total-label" colspan="2">&nbsp;Samaksāts</td>
    <td class="col-price"><?php echo number_format($paid, 2, '.', ' '); ?>&nbsp;€&nbsp;</td>
</tr>
<tr class="remainder-row">
    <td class="total-label" colspan="2">&nbsp;Atlikums</td>
    <td class="col-price"><?php echo number_format($remainder, 2, '.', ' '); ?>&nbsp;€&nbsp;</td>
</tr>
</table>

<div class="rules-section">
    <p><b>Nododot ierīci remontā klients piekrīt firmas noteikumiem:</b></p>
    <ol>
		<li>Firma ir privāta vienības struktūra, kas neattiecas uz garantijas servisu. Nododot savu ierīci remontā, Jūs uzņematies atbildību par visa veida riskiem, kas var rasties remonta laikā.</li>
		<li>Firma nenes atbildību par slēptiem bojājumiem, kas var raksties veicot remontu (ierīcei ar kritiena defektu, “ūdens” ierīcei vai pēc cita meistara (darbnīcas) veikta remonta.</li>
		<li>Garantijas periods attiecas tikai uz Firmas samainītām detaļām veicot remontu.</li>
		<li>Garantija neattiecas uz saplēstām vai fiziski bojātām detaļām Klienta vainas dēļ (garantijas darbības periodā).</li>
		<li>Garantija neattiecas uz Klienta detaļām (kas tika iegādātas pie cita izplatītāja), veicot Klienta ierīces remontu.</li>
		<li>Gadījumā, ja Firma 2 (divu) mēnešu laikā nesaņem pilnu maksu par Firmas veikto remontu, Klienta ierīce pāriet Firmas īpašumā kā maksa par veikto remontu.</li>
		<li>Gadījuma, ja Klients nav izņēmis savu ierīci 2 (divu) mēnešu laikā (pat, ja remonts bija apmaksāts) un Klients nesniedza nekāda brīdinājuma par izņemšanas aizkavēšanos, tad Klienta ierīce pāriet Firmas īpašumā.</li>
		<li>Remonta cena vai diagnostikas cena var mainīties atkarībā no noteiktās remonta vai diagnostikas summas pēc abpusējās vienošanās (tajā skaitā – mutiskās vienošanās) ar Klientu.</li>
		<li>Remonta cenā ir iekļauta: detaļas maksa un meistara darbs.</li>
		<li>Gadījumā, ja Klients atsakās veikt ierīces remontu par piedāvāto cenu pēc veiktas diagnostikas, Klientam jāapmaksā veiktā ierīces diagnostikas summa pēc Firmas noteiktā cenrāža vai iepriekš norunāto ar Firmas pārstāvi diagnostikas summu.</li>
		<li>Ja Klients veica pirmo iemaksu, tad ierīces saņemšanas brīdī Klientam jāsamaksā tikai starpība no pilnas remonta summas.</li>
    </ol>
</div>

<table class="signature-table">
<tr>
    <td>Nodevu:</td>
    <td class="underline"><?php echo htmlspecialchars($repair['client_name']); ?></td>
    <td></td>
    <td>Datums:</td>
    <td class="underline"><?php echo date("d.m.Y"); ?></td>
    <td></td>
    <td>Paraksts:</td>
    <td class="underline"></td>
</tr>
<tr>
    <td></td>
    <td class="small-text">(vārds, uzvārds)</td>
    <td colspan="2"></td>
    <td class="small-text">(datums)</td>
    <td colspan="2"></td>
    <td class="small-text">(paraksts)</td>
</tr>
<tr><td colspan="8" style="height: 10px;"></td></tr>
<tr>
    <td>Izrakstīja:</td>
    <td class="underline"><?php echo htmlspecialchars($repair['invoice_issuer'] ?? '-'); ?></td>
    <td></td>
    <td>Datums:</td>
    <td class="underline"><?php echo date("d.m.Y"); ?></td>
    <td></td>
    <td>Paraksts:</td>
    <td class="underline"></td>
</tr>
<tr>
    <td></td>
    <td class="small-text">(vārds, uzvārds)</td>
    <td colspan="2"></td>
    <td class="small-text">(datums)</td>
    <td colspan="2"></td>
    <td class="small-text">(paraksts)</td>
</tr>
<tr><td colspan="8" style="height: 10px;"></td></tr>
<tr>
    <td>Saņemu:</td>
    <td class="underline"></td>
    <td></td>
    <td>Datums:</td>
    <td class="underline"></td>
    <td></td>
    <td>Paraksts:</td>
    <td class="underline"></td>
</tr>
<tr>
    <td></td>
    <td class="small-text">(vārds, uzvārds)</td>
    <td colspan="2"></td>
    <td class="small-text">(datums)</td>
    <td colspan="2"></td>
    <td class="small-text">(paraksts)</td>
</tr>
</table>

<div class="footer-info">
    <?php echo htmlspecialchars($branch['legal_info']); ?>
</div>

</body>
</html>