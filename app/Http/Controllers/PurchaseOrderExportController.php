<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderExportController extends Controller
{
    /**
     * Compute breakdown details for purchase order items exactly as displayed in the UI modal.
     */
    public static function getOrderBreakdown(PurchaseOrder $order)
    {
        $costMultiplier = floatval($order->cost_multiplier ?? ($order->supplier?->cost_multiplier ?? 1.0));
        if ($costMultiplier <= 0) $costMultiplier = 1.0;
        $sellMultiplier = floatval($order->sell_multiplier ?? ($order->supplier?->sell_multiplier ?? 0));

        $itemsData = [];
        $totalSupplierSum = 0;
        $totalSystemSum = 0;
        $totalSellingSum = 0;
        $totalOrderQty = 0;
        $totalReceivedQty = 0;

        foreach ($order->items as $idx => $item) {
            $product = $item->product;

            $costMult = floatval($item->cost_multiplier ?? ($product && $product->price ? $product->price->cost_multiplier : $costMultiplier));
            if ($costMult <= 0) $costMult = 1.0;

            $supplierCost = $item->supplier_cost !== null && floatval($item->supplier_cost) > 0
                ? floatval($item->supplier_cost)
                : ($product && $product->price && floatval($product->price->supplier_price) > 0
                    ? floatval($product->price->supplier_price)
                    : floatval($item->unit_price ?? 0));

            if ($item->system_cost !== null && floatval($item->system_cost) > 0 && (abs(floatval($item->system_cost) - $supplierCost) > 0.01 || $costMult == 1.0)) {
                $systemCost = floatval($item->system_cost);
            } elseif ($product && $product->price && floatval($product->price->system_cost) > 0) {
                $systemCost = floatval($product->price->system_cost);
            } else {
                $systemCost = round($supplierCost * $costMult, 2);
            }

            $sellingPrice = 0;
            if ($product && $product->price) {
                if (floatval($product->price->selling_price) > 0) {
                    $sellingPrice = floatval($product->price->selling_price);
                } elseif (floatval($product->price->retail_price) > 0) {
                    $sellingPrice = floatval($product->price->retail_price);
                }
            }
            if ($sellingPrice <= 0 && $item->selling_price !== null && floatval($item->selling_price) > 0) {
                $sellingPrice = floatval($item->selling_price);
            }

            $discount = floatval($item->discount ?? 0);
            $discountAmount = ($systemCost * $discount) / 100;
            $netSystemCost = $systemCost - $discountAmount;

            $orderQty = floatval($item->quantity ?? 0);
            $recQty = floatval($item->received_quantity ?? 0);
            $activeQty = floatval($item->received_quantity ?? ($item->quantity ?? 0));

            $itemSupplierTotal = $supplierCost * $activeQty;
            $itemSystemTotal = $netSystemCost * $activeQty;
            $itemSellingTotal = $sellingPrice * $activeQty;

            $totalSupplierSum += $itemSupplierTotal;
            $totalSystemSum += $itemSystemTotal;
            $totalSellingSum += $itemSellingTotal;
            $totalOrderQty += $orderQty;
            $totalReceivedQty += $recQty;

            $productName = $item->display_name ?? ($product ? $product->name : 'N/A');
            $code = $product ? ($product->code ?? '') : '';
            $size = $product ? ($product->size ?? '') : '';
            $barcode = $product ? ($product->barcode ?? '') : '';

            $itemsData[] = [
                'index' => $idx + 1,
                'code' => $code,
                'product' => $productName,
                'size' => $size,
                'barcode' => $barcode,
                'status' => ucfirst($item->status ?? 'pending'),
                'order_qty' => $orderQty,
                'received_qty' => $recQty,
                'supplier_cost' => $supplierCost,
                'system_cost' => $systemCost,
                'cost_multiplier' => $costMult,
                'discount' => $discount,
                'selling_price' => $sellingPrice,
                'total_supplier' => $itemSupplierTotal,
                'total_system' => $itemSystemTotal,
                'total_selling' => $itemSellingTotal,
            ];
        }

        return [
            'order' => $order,
            'items' => $itemsData,
            'costMultiplier' => $costMultiplier,
            'sellMultiplier' => $sellMultiplier,
            'totalSupplierSum' => $totalSupplierSum,
            'totalSystemSum' => $totalSystemSum,
            'totalSellingSum' => $totalSellingSum,
            'totalOrderQty' => $totalOrderQty,
            'totalReceivedQty' => $totalReceivedQty,
            'estProfit' => max(0, $totalSellingSum - $totalSystemSum),
        ];
    }

    /**
     * Download Purchase Order as Excel (.xlsx)
     */
    public function exportExcel($id)
    {
        $order = PurchaseOrder::with([
            'supplier',
            'items' => function ($q) {
                $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc')
                  ->with(['product.price', 'product.variant', 'variant']);
            }
        ])->findOrFail($id);

        $breakdown = self::getOrderBreakdown($order);
        $orderCode = $order->order_code ?: ('PO-' . $order->id);
        $safeOrderCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $orderCode);
        $fileName = 'Purchase_Order_' . $safeOrderCode . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($breakdown, $order, $orderCode) {
            $prevErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);
            if (ob_get_length()) {
                ob_clean();
            }

            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('Jaffna Gold POS')
                ->setTitle('Purchase Order ' . $orderCode)
                ->setSubject('Purchase Order Details');

            $sheet = $spreadsheet->getActiveSheet();
            $sheetTitle = substr(preg_replace('/[^A-Za-z0-9_-]/', '', $orderCode), 0, 28) ?: 'PO';
            $sheet->setTitle($sheetTitle);
            $sheet->setShowGridLines(true);

            // Row 1: Company Title
            $sheet->setCellValue('A1', 'JAFFNA GOLD POS - PURCHASE ORDER');
            $sheet->mergeCells('A1:L1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Row 2: Subtitle
            $orderDateStr = $order->order_date ? date('M d, Y', strtotime($order->order_date)) : '-';
            $receivedDateStr = $order->received_date ? date('M d, Y', strtotime($order->received_date)) : 'Not Received Yet';
            $sheet->setCellValue('A2', "Order Code: {$orderCode}  |  Status: " . strtoupper($order->status ?? 'PENDING') . "  |  Ordered: {$orderDateStr}  |  Received: {$receivedDateStr}");
            $sheet->mergeCells('A2:L2');
            $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF4B5563'));

            // Row 4-6: Supplier & Rates Info Box
            $sheet->setCellValue('A4', 'SUPPLIER:');
            $sheet->setCellValue('B4', $order->supplier->name ?? 'N/A');
            $sheet->setCellValue('A5', 'PHONE:');
            $sheet->setCellValue('B5', $order->supplier->phone ?? 'N/A');
            $sheet->setCellValue('A6', 'BUSINESS:');
            $sheet->setCellValue('B6', $order->supplier->businessname ?? ($order->supplier->address ?? 'N/A'));

            $sheet->setCellValue('E4', 'ORDER DATE:');
            $sheet->setCellValue('F4', $orderDateStr);
            $sheet->setCellValue('E5', 'RECEIVED DATE:');
            $sheet->setCellValue('F5', $receivedDateStr);
            $sheet->setCellValue('E6', 'TOTAL ITEMS:');
            $sheet->setCellValue('F6', count($breakdown['items']) . ' items / ' . $breakdown['totalOrderQty'] . ' ordered');

            $sheet->setCellValue('I4', 'COST RATE:');
            $sheet->setCellValue('J4', $breakdown['costMultiplier'] . 'x (Supplier -> System)');
            $sheet->setCellValue('I5', 'SELL RATE:');
            $sheet->setCellValue('J5', ($breakdown['sellMultiplier'] > 0 ? $breakdown['sellMultiplier'] . 'x' : 'Manual / None'));
            $sheet->setCellValue('I6', 'STATUS:');
            $sheet->setCellValue('J6', ucfirst($order->status ?? 'pending'));

            $infoStyle = [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '475569']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ];
            $sheet->getStyle('A4:A6')->applyFromArray($infoStyle);
            $sheet->getStyle('E4:E6')->applyFromArray($infoStyle);
            $sheet->getStyle('I4:I6')->applyFromArray($infoStyle);

            $infoValStyle = [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
            ];
            $sheet->getStyle('B4:B6')->applyFromArray($infoValStyle);
            $sheet->getStyle('F4:F6')->applyFromArray($infoValStyle);
            $sheet->getStyle('J4:J6')->applyFromArray($infoValStyle);

            // Table Header on Row 8
            $headerRow = 8;
            $headers = [
                '#',
                'Code',
                'Product Name',
                'Status',
                'Order Qty',
                'Received Qty',
                'Supplier Cost (Rs.)',
                'System Cost (Rs.)',
                'Selling Price (Rs.)',
                'Total Supplier (Rs.)',
                'Total System (Rs.)',
                'Total Selling (Rs.)',
            ];

            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
            foreach ($headers as $colIdx => $headerText) {
                $sheet->setCellValue($colLetters[$colIdx] . $headerRow, $headerText);
            }

            // Header Style
            $headerRange = "A{$headerRow}:L{$headerRow}";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B'], // Dark slate
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(28);

            // Data rows
            $currentRow = 9;
            foreach ($breakdown['items'] as $item) {
                $sheet->setCellValue('A' . $currentRow, $item['index']);
                $sheet->setCellValueExplicit('B' . $currentRow, $item['code'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $currentRow, $item['product']);
                $sheet->setCellValue('D' . $currentRow, $item['status']);
                $sheet->setCellValue('E' . $currentRow, $item['order_qty']);
                $sheet->setCellValue('F' . $currentRow, $item['received_qty']);
                $sheet->setCellValue('G' . $currentRow, $item['supplier_cost']);
                $sheet->setCellValue('H' . $currentRow, $item['system_cost']);
                $sheet->setCellValue('I' . $currentRow, $item['selling_price']);
                $sheet->setCellValue('J' . $currentRow, $item['total_supplier']);
                $sheet->setCellValue('K' . $currentRow, $item['total_system']);
                $sheet->setCellValue('L' . $currentRow, $item['total_selling']);

                // Subtle alternating row background
                if ($currentRow % 2 === 0) {
                    $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F8FAFC');
                }

                $currentRow++;
            }

            $lastDataRow = $currentRow - 1;

            // Formats for data
            if ($lastDataRow >= 9) {
                $sheet->getStyle("A9:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B9:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C9:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("D9:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E9:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G9:L{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Number formats
                $sheet->getStyle("E9:F{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("G9:L{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Borders for table
                $sheet->getStyle("A8:L{$lastDataRow}")->getBorders()->getAllBorders()->applyFromArray([
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ]);
            }

            // Summary Totals Row
            $totalRow = $currentRow;
            $sheet->setCellValue('A' . $totalRow, 'TOTAL');
            $sheet->mergeCells("A{$totalRow}:D{$totalRow}");
            $sheet->setCellValue('E' . $totalRow, "=SUM(E9:E{$lastDataRow})");
            $sheet->setCellValue('F' . $totalRow, "=SUM(F9:F{$lastDataRow})");
            $sheet->setCellValue('G' . $totalRow, '');
            $sheet->setCellValue('H' . $totalRow, '');
            $sheet->setCellValue('I' . $totalRow, '');
            $sheet->setCellValue('J' . $totalRow, "=SUM(J9:J{$lastDataRow})");
            $sheet->setCellValue('K' . $totalRow, "=SUM(K9:K{$lastDataRow})");
            $sheet->setCellValue('L' . $totalRow, "=SUM(L9:L{$lastDataRow})");

            $sheet->getStyle("A{$totalRow}:L{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '475569']],
                    'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '475569']],
                ],
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$totalRow}:F{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$totalRow}:L{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$totalRow}:F{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("J{$totalRow}:L{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getRowDimension($totalRow)->setRowHeight(24);

            // Bottom Summary Cards block (matching the UI modal)
            $cardRow = $totalRow + 2;
            $sheet->setCellValue("A{$cardRow}", 'TOTAL SUPPLIER COST:');
            $sheet->setCellValue("B{$cardRow}", "Rs. " . number_format($breakdown['totalSupplierSum'], 2));
            $sheet->setCellValue("D{$cardRow}", 'TOTAL SYSTEM COST:');
            $sheet->setCellValue("E{$cardRow}", "Rs. " . number_format($breakdown['totalSystemSum'], 2));
            $sheet->setCellValue("G{$cardRow}", 'EXPECTED SELLING VALUE:');
            $sheet->setCellValue("H{$cardRow}", "Rs. " . number_format($breakdown['totalSellingSum'], 2));
            $sheet->setCellValue("J{$cardRow}", 'EST. PROFIT:');
            $sheet->setCellValue("K{$cardRow}", "+ Rs. " . number_format($breakdown['estProfit'], 2));

            $summaryLabelStyle = [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '475569']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ];
            $sheet->getStyle("A{$cardRow}")->applyFromArray($summaryLabelStyle);
            $sheet->getStyle("D{$cardRow}")->applyFromArray($summaryLabelStyle);
            $sheet->getStyle("G{$cardRow}")->applyFromArray($summaryLabelStyle);
            $sheet->getStyle("J{$cardRow}")->applyFromArray($summaryLabelStyle);

            $sheet->getStyle("B{$cardRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0284C7')); // Info blue
            $sheet->getStyle("E{$cardRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F172A')); // Dark
            $sheet->getStyle("H{$cardRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF16A34A')); // Green
            $sheet->getStyle("K{$cardRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF16A34A')); // Green

            // Auto-size columns with sensible min/max
            foreach ($colLetters as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(36); // Product name wide

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');

            error_reporting($prevErrorReporting);
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Download Purchase Order as PDF (A4 Landscape, complete breakdown)
     */
    public function downloadPdf($id)
    {
        $order = PurchaseOrder::with([
            'supplier',
            'items' => function ($q) {
                $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc')
                  ->with(['product.price', 'product.variant', 'variant']);
            }
        ])->findOrFail($id);

        $breakdown = self::getOrderBreakdown($order);
        $orderCode = $order->order_code ?: ('PO-' . $order->id);
        $safeOrderCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $orderCode);
        $fileName = 'Purchase_Order_' . $safeOrderCode . '.pdf';

        $html = view('pdf.purchase-order-details', $breakdown)->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('defaultFont', 'sans-serif');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }
}
