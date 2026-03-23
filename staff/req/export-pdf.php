<?php
require_once '../../config/db_conn.php';
require_once('../../vendor/TCPDF-main/tcpdf.php');

// Set UTF-8 encoding for database connection
mysqli_set_charset($conn, "utf8mb4");

// Set Philippine timezone
date_default_timezone_set('Asia/Manila');

// Get date filters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get profits (paid laundry)
$profitQuery = "SELECT SUM(total_price) as total_profit 
                FROM laundry_lists 
                WHERE payment_status = 'Paid'
                AND DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($profitQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$profitResult = $stmt->get_result()->fetch_assoc();
$totalProfit = $profitResult['total_profit'] ?? 0;

// Get supply expenses
$supplyQuery = "SELECT sp.name, sp.price, st.quantity, (sp.price * st.quantity) as total_cost
                FROM supply_transactions st 
                JOIN supply_products sp ON st.product_id = sp.id
                WHERE st.type = 'IN'
                AND DATE(st.created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($supplyQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$supplyExpenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get utility bills
$billsQuery = "SELECT * FROM utility_bills WHERE DATE(bill_date) BETWEEN ? AND ?";
$stmt = $conn->prepare($billsQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$utilityBills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total expenses
$totalExpenses = 0;
foreach ($supplyExpenses as $supply) {
    $totalExpenses += $supply['total_cost'];
}
foreach ($utilityBills as $bill) {
    $totalExpenses += $bill['amount'];
}

// Create PDF with proper font support
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Laundry Management System');
$pdf->SetAuthor('Sasy Laundry Hub');
$pdf->SetTitle('Financial Report');
$pdf->SetSubject('Financial Report');

// Set default header data with logo
$logoPath = '../../logo.jpg'; // Ensure this path is correct
$pdf->SetHeaderData($logoPath, 15, 'Financial Report', 'Period: ' . date('F d, Y', strtotime($startDate)) . ' - ' . date('F d, Y', strtotime($endDate)) . "\nSasy Laundry Hub");

// Set header and footer fonts
$pdf->setHeaderFont(array('dejavusans', 'B', 12));
$pdf->setFooterFont(array('dejavusans', '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, 30, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Set Unicode-compatible font
$pdf->SetFont('dejavusans', '', 11);

// Define colors
$primaryColor = '#644499'; // Primary color
$accentColor = '#c7345c';  // Accent color
$lightGray = '#f0f0f0';

// Revenue Section
$html = '<h2 style="color: ' . $primaryColor . '; border-bottom: 2px solid ' . $accentColor . '; padding-bottom: 5px;">Revenue</h2>';
$html .= '<table border="0" cellpadding="5" cellspacing="0">
    <tr style="background-color: ' . $lightGray . ';">
        <td width="70%"><strong style="color: ' . $primaryColor . ';">Total Laundry Revenue</strong></td>
        <td width="30%" align="right"><strong style="color: ' . $accentColor . ';">₱' . number_format($totalProfit, 2) . '</strong></td>
    </tr>
</table><br><br>';

// Expenses Section
$html .= '<h2 style="color: ' . $primaryColor . '; border-bottom: 2px solid ' . $accentColor . '; padding-bottom: 5px;">Expenses</h2>';
$html .= '<table border="0" cellpadding="5" cellspacing="0">
    <tr style="background-color: ' . $lightGray . '; color: ' . $primaryColor . ';">
        <td width="25%"><strong>Category</strong></td>
        <td width="50%"><strong>Description</strong></td>
        <td width="25%" align="right"><strong>Amount</strong></td>
    </tr>';

// Supply Expenses
foreach ($supplyExpenses as $supply) {
    $html .= '<tr>
        <td>Supply</td>
        <td>' . htmlspecialchars($supply['name'], ENT_QUOTES, 'UTF-8') . ' (' . $supply['quantity'] . ' stocks)</td>
        <td align="right" style="color: ' . $accentColor . ';">₱' . number_format($supply['total_cost'], 2) . '</td>
    </tr>';
}

// Utility Bills
foreach ($utilityBills as $bill) {
    $html .= '<tr>
        <td>Utility</td>
        <td>' . htmlspecialchars($bill['type'], ENT_QUOTES, 'UTF-8') . '</td>
        <td align="right" style="color: ' . $accentColor . ';">₱' . number_format($bill['amount'], 2) . '</td>
    </tr>';
}

$html .= '<tr style="background-color: ' . $lightGray . ';">
        <td colspan="2"><strong style="color: ' . $primaryColor . ';">Total Expenses</strong></td>
        <td align="right"><strong style="color: ' . $accentColor . ';">₱' . number_format($totalExpenses, 2) . '</strong></td>
    </tr>';
$html .= '</table><br><br>';

// Summary Section
$netProfit = $totalProfit - $totalExpenses;
$netColor = ($netProfit >= 0) ? $primaryColor : '#FF0000'; // Red for loss
$html .= '<h2 style="color: ' . $primaryColor . '; border-bottom: 2px solid ' . $accentColor . '; padding-bottom: 5px;">Summary</h2>';
$html .= '<table border="0" cellpadding="5" cellspacing="0">
    <tr style="background-color: ' . $lightGray . ';">
        <td width="70%"><strong style="color: ' . $primaryColor . ';">Net Profit/Loss</strong></td>
        <td width="30%" align="right"><strong style="color: ' . $netColor . ';">₱' . number_format($netProfit, 2) . '</strong></td>
    </tr>
</table>';

// Add footer
$html .= '<div style="text-align: center; margin-top: 20px; color: ' . $primaryColor . '; font-size: 10px;">
    <hr style="border-top: 1px solid ' . $accentColor . ';">
    Generated by Sasy Laundry Hub on ' . date('F j, Y g:i A') . '
</div>';

// Print HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Generate filename
$filename = 'Financial_Report_' . date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate)) . '.pdf';

// Output PDF
$pdf->Output($filename, 'D');
