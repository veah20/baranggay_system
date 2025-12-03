<?php
require_once 'config/config.php';
require_once 'config/database.php';

checkSession();

if (!isset($_GET['id'])) {
    die('Certificate ID not provided');
}

$cert_id = $_GET['id'];

$database = new Database();
$conn = $database->getConnection();

// Fetch certificate details
try {
    $stmt = $conn->prepare("
        SELECT c.*, 
               CONCAT(r.firstname, ' ', COALESCE(r.middlename, ''), ' ', r.lastname, ' ', COALESCE(r.suffix, '')) as resident_name,
               r.birthdate, r.gender, r.civil_status, r.purok, r.street, r.nationality,
               s.barangay_name, s.municipality, s.province
        FROM certificates c
        JOIN residents r ON c.resident_id = r.resident_id
        CROSS JOIN system_settings s
        WHERE c.cert_id = ?
    ");
    $stmt->execute([$cert_id]);
    $cert = $stmt->fetch();
    
    if (!$cert) {
        die('Certificate not found');
    }
    
    // Fetch barangay captain
    $stmt = $conn->query("SELECT name FROM officials WHERE position = 'Barangay Captain' AND status = 'Current' LIMIT 1");
    $captain = $stmt->fetch();
    
} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

$age = calculateAge($cert['birthdate']);
$address = ($cert['street'] ? $cert['street'] . ', ' : '') . 'Purok ' . $cert['purok'] . ', ' . $cert['barangay_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Certificate - <?php echo $cert['cert_number']; ?></title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background: white;
        }
        
        .certificate {
            width: 210mm;
            min-height: 297mm;
            padding: 30mm;
            margin: 0 auto;
            background: white;
            position: relative;
            border: 3px double #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }
        
        .header h1 {
            margin: 5px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 14px;
        }
        
        .office-title {
            text-align: center;
            margin: 30px 0;
        }
        
        .office-title h3 {
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            margin: 10px 0;
        }
        
        .content {
            margin: 40px 0;
            text-align: justify;
            line-height: 2;
            font-size: 14px;
        }
        
        .content p {
            text-indent: 50px;
            margin: 20px 0;
        }
        
        .resident-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 16px;
        }
        
        .purpose {
            font-weight: bold;
            text-decoration: underline;
        }
        
        .footer {
            margin-top: 60px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 80px;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: bold;
        }
        
        .position {
            font-size: 12px;
            font-style: italic;
        }
        
        .cert-number {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .date-issued {
            position: absolute;
            top: 40px;
            right: 30px;
            font-size: 12px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .certificate {
                border: none;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Certificate
    </button>
    
    <div class="certificate">
        <div class="cert-number">No. <?php echo $cert['cert_number']; ?></div>
        <div class="date-issued">Date: <?php echo formatDate($cert['date_issued']); ?></div>
        
        <div class="header">
            <p style="margin: 0;">Republic of the Philippines</p>
            <p style="margin: 0;">Province of <?php echo $cert['province']; ?></p>
            <p style="margin: 0;">Municipality of <?php echo $cert['municipality']; ?></p>
            <h2><?php echo strtoupper($cert['barangay_name']); ?></h2>
        </div>
        
        <div class="office-title">
            <h3>OFFICE OF THE BARANGAY CAPTAIN</h3>
            <h3><?php echo strtoupper($cert['cert_type']); ?></h3>
        </div>
        
        <div class="content">
            <p style="text-align: center; font-weight: bold; font-size: 16px;">TO WHOM IT MAY CONCERN:</p>
            
            <?php if ($cert['cert_type'] == 'Barangay Clearance'): ?>
                <p>
                    This is to certify that <span class="resident-name"><?php echo strtoupper($cert['resident_name']); ?></span>, 
                    <?php echo $age; ?> years old, <?php echo $cert['gender']; ?>, <?php echo $cert['civil_status']; ?>, 
                    Filipino citizen, and a bonafide resident of <?php echo $address; ?>, <?php echo $cert['municipality']; ?>, 
                    <?php echo $cert['province']; ?>.
                </p>
                <p>
                    This certification is issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($cert['purpose']); ?></span>.
                </p>
                <p>
                    This is to certify further that the above-named person is known to me to be of good moral character 
                    and a law-abiding citizen, and has no derogatory and/or criminal records filed in this barangay.
                </p>
                
            <?php elseif ($cert['cert_type'] == 'Certificate of Residency'): ?>
                <p>
                    This is to certify that <span class="resident-name"><?php echo strtoupper($cert['resident_name']); ?></span>, 
                    <?php echo $age; ?> years old, <?php echo $cert['gender']; ?>, <?php echo $cert['civil_status']; ?>, 
                    is a bonafide resident of <?php echo $address; ?>, <?php echo $cert['municipality']; ?>, 
                    <?php echo $cert['province']; ?>.
                </p>
                <p>
                    This certification is issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($cert['purpose']); ?></span>.
                </p>
                
            <?php elseif ($cert['cert_type'] == 'Certificate of Indigency'): ?>
                <p>
                    This is to certify that <span class="resident-name"><?php echo strtoupper($cert['resident_name']); ?></span>, 
                    <?php echo $age; ?> years old, <?php echo $cert['gender']; ?>, <?php echo $cert['civil_status']; ?>, 
                    is a bonafide resident of <?php echo $address; ?>, <?php echo $cert['municipality']; ?>, 
                    <?php echo $cert['province']; ?>.
                </p>
                <p>
                    This is to certify further that the above-named person belongs to an indigent family in this barangay.
                </p>
                <p>
                    This certification is issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($cert['purpose']); ?></span>.
                </p>
                
            <?php else: ?>
                <p>
                    This is to certify that <span class="resident-name"><?php echo strtoupper($cert['resident_name']); ?></span>, 
                    <?php echo $age; ?> years old, <?php echo $cert['gender']; ?>, <?php echo $cert['civil_status']; ?>, 
                    is a bonafide resident of <?php echo $address; ?>, <?php echo $cert['municipality']; ?>, 
                    <?php echo $cert['province']; ?>.
                </p>
                <p>
                    This certification is issued upon the request of the above-named person for 
                    <span class="purpose"><?php echo strtoupper($cert['purpose']); ?></span>.
                </p>
            <?php endif; ?>
            
            <p>
                Issued this <strong><?php echo date('jS', strtotime($cert['date_issued'])); ?></strong> day of 
                <strong><?php echo date('F Y', strtotime($cert['date_issued'])); ?></strong> at 
                <?php echo $cert['barangay_name']; ?>, <?php echo $cert['municipality']; ?>, <?php echo $cert['province']; ?>.
            </p>
        </div>
        
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        <?php echo strtoupper($captain['name'] ?? 'BARANGAY CAPTAIN'); ?>
                    </div>
                    <div class="position">Punong Barangay</div>
                </div>
            </div>
            
            <div style="margin-top: 40px; font-size: 12px;">
                <p><strong>Paid under O.R. No.:</strong> <?php echo $cert['or_number'] ?: 'N/A'; ?></p>
                <p><strong>Amount Paid:</strong> ₱<?php echo number_format($cert['amount_paid'], 2); ?></p>
            </div>
        </div>
    </div>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</body>
</html>
