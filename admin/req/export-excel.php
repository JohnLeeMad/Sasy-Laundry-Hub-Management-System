<!-- < ?php
require_once '../../config/db_conn.php';
// require_once '../../vendor/autoload.php'; // If using Composer for PhpSpreadsheet

// Alternative: If not using Composer, include PhpSpreadsheet directly
require_once '../../PhpSpreadsheet-master/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('Laundry Management System')
    ->setTitle('Financial Report')
    ->setSubject('Financial Report')
    ->setDescription('Financial report generated from laundry management system');

// Set sheet title
$sheet->setTitle('Financial Report');

$row = 1;

// Report Header
$sheet->setCellValue('A' . $row, 'Financial Report');
$sheet->getStyle('A' . $row)->getFont()->setSize(16)->setBold(true);
$sheet->mergeCells('A' . $row . ':C' . $row);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row++;

$sheet->setCellValue('A' . $row, 'Period: ' . date('F d, Y', strtotime($startDate)) . ' - ' . date('F d, Y', strtotime($endDate)));
$sheet->mergeCells('A' . $row . ':C' . $row);
$sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$row += 2;

// Revenue Section
$sheet->setCellValue('A' . $row, 'REVENUE');
$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
$row++;

$sheet->setCellValue('A' . $row, 'Total Laundry Revenue');
$sheet->setCellValue('C' . $row, '₱' . number_format($totalProfit, 2));
$sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
$sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$row += 2;

// Expenses Section
$sheet->setCellValue('A' . $row, 'EXPENSES');
$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
$row++;

// Expenses Headers
$sheet->setCellValue('A' . $row, 'Category');
$sheet->setCellValue('B' . $row, 'Description');
$sheet->setCellValue('C' . $row, 'Amount');
$sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
$sheet->getStyle('A' . $row . ':C' . $row)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('E0E0E0');
$row++;

// Supply Expenses
foreach ($supplyExpenses as $supply) {
    $sheet->setCellValue('A' . $row, 'Supply');
    $sheet->setCellValue('B' . $row, $supply['name'] . ' (' . $supply['quantity'] . ' units)');
    $sheet->setCellValue('C' . $row, '₱' . number_format($supply['total_cost'], 2));
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $row++;
}

// Utility Bills
foreach ($utilityBills as $bill) {
    $sheet->setCellValue('A' . $row, 'Utility');
    $sheet->setCellValue('B' . $row, $bill['type']);
    $sheet->setCellValue('C' . $row, '₱' . number_format($bill['amount'], 2));
    $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $row++;
}

// Total Expenses
$sheet->setCellValue('A' . $row, 'Total Expenses');
$sheet->mergeCells('A' . $row . ':B' . $row);
$sheet->setCellValue('C' . $row, '₱' . number_format($totalExpenses, 2));
$sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
$sheet->getStyle('A' . $row . ':C' . $row)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('E0E0E0');
$sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$row += 2;

// Summary Section
$sheet->setCellValue('A' . $row, 'SUMMARY');
$sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
$row++;

$netProfit = $totalProfit - $totalExpenses;
$sheet->setCellValue('A' . $row, 'Net Profit/Loss');
$sheet->mergeCells('A' . $row . ':B' . $row);
$sheet->setCellValue('C' . $row, '₱' . number_format($netProfit, 2));
$sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);

// Set borders for all data
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle('A1:C' . $row)->applyFromArray($styleArray);

// Generate filename
$filename = 'Financial_Report_' . date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate)) . '.xlsx';

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Create writer and output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?> -->