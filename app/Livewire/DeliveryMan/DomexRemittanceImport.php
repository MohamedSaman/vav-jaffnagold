<?php

namespace App\Livewire\DeliveryMan;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Title('Domex Remittance Excel Import & Reconciliation')]
#[Layout('components.layouts.delivery-man')]
class DomexRemittanceImport extends Component
{
    use WithFileUploads;

    public $excelFile;

    public string $fileName = '';

    public array $parsedRecords = [];

    public array $reconciliationKpis = [];

    public string $tabFilter = 'all'; // all, matched, variance, returns, unmatched

    public string $search = '';

    public bool $updateDeliveryCharges = true;

    public array $selectedTrackings = [];

    public bool $selectAll = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount(): void
    {
        // If Excel_Export.xlsx is present in the project root, automatically make it available for instant 1-click loading
        $defaultPath = base_path('Excel_Export.xlsx');
        if (file_exists($defaultPath) && empty($this->parsedRecords)) {
            $this->loadFromDiskPath($defaultPath, 'Excel_Export.xlsx');
        }
    }

    /**
     * Triggered when a new Excel file is uploaded via the file input.
     */
    public function updatedExcelFile(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $this->validate([
            'excelFile' => 'required|file|max:15360', // max 15MB
        ]);

        try {
            $tempPath = $this->excelFile->getRealPath();
            $originalName = $this->excelFile->getClientOriginalName();
            $this->loadFromDiskPath($tempPath, $originalName);
            $this->dispatch('show-toast', type: 'success', message: "File '{$this->fileName}' parsed successfully!");
        } catch (\Throwable $e) {
            Log::error('Domex Remittance Excel Parse Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->errorMessage = 'Failed to parse Excel file: '.$e->getMessage();
            $this->dispatch('show-toast', type: 'error', message: $this->errorMessage);
        }
    }

    /**
     * Quick-load from local disk path (e.g. sample Excel_Export.xlsx)
     */
    public function loadFromDiskPath(string $path, string $displayFilename = 'Excel_Export.xlsx'): void
    {
        if (! file_exists($path)) {
            $this->errorMessage = "File not found at: {$path}";

            return;
        }

        $this->fileName = $displayFilename;
        $this->errorMessage = null;

        // Suppress deprecation warnings on PHP 8.5 during PhpSpreadsheet parsing
        $prevErr = error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        try {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } finally {
            error_reporting($prevErr);
        }

        if (empty($rows)) {
            $this->errorMessage = 'The selected Excel file is empty.';

            return;
        }

        // Dynamically detect header row
        $headerIndex = -1;
        $colMap = [
            'tracking' => -1,
            'date' => -1,
            'name' => -1,
            'address' => -1,
            'value' => -1,
            'charge' => -1,
            'payable' => -1,
            'ref_no' => -1,
        ];

        foreach ($rows as $idx => $r) {
            foreach ($r as $colIdx => $val) {
                $clean = strtolower(trim((string) $val));
                if (in_array($clean, ['tracking no', 'tracking no.', 'tracking', 'waybill', 'wbno', 'awb'])) {
                    $headerIndex = $idx;
                    break 2;
                }
            }
        }

        if ($headerIndex === -1) {
            // Fallback: assume row 0 is header
            $headerIndex = 0;
        }

        foreach ($rows[$headerIndex] as $colIdx => $val) {
            $clean = strtolower(trim((string) $val));
            if (str_contains($clean, 'track') || str_contains($clean, 'waybill')) {
                $colMap['tracking'] = $colIdx;
            } elseif (str_contains($clean, 'invoiced date') || str_contains($clean, 'date')) {
                $colMap['date'] = $colIdx;
            } elseif (str_contains($clean, 'receiver name') || str_contains($clean, 'customer') || $clean === 'name') {
                $colMap['name'] = $colIdx;
            } elseif (str_contains($clean, 'receiver address') || str_contains($clean, 'address')) {
                $colMap['address'] = $colIdx;
            } elseif ($clean === 'value' || str_contains($clean, 'cod') || str_contains($clean, 'collected')) {
                $colMap['value'] = $colIdx;
            } elseif ($clean === 'charge' || str_contains($clean, 'fee') || str_contains($clean, 'courier')) {
                $colMap['charge'] = $colIdx;
            } elseif ($clean === 'payable' || str_contains($clean, 'net') || str_contains($clean, 'remit')) {
                $colMap['payable'] = $colIdx;
            } elseif (str_contains($clean, 'ref')) {
                $colMap['ref_no'] = $colIdx;
            }
        }

        // Default column fallbacks if headers weren't named identically to Domex standard export
        if ($colMap['tracking'] === -1) {
            $colMap['tracking'] = 2;
        }
        if ($colMap['date'] === -1) {
            $colMap['date'] = 4;
        }
        if ($colMap['name'] === -1) {
            $colMap['name'] = 5;
        }
        if ($colMap['address'] === -1) {
            $colMap['address'] = 6;
        }
        if ($colMap['value'] === -1) {
            $colMap['value'] = 7;
        }
        if ($colMap['charge'] === -1) {
            $colMap['charge'] = 8;
        }
        if ($colMap['payable'] === -1) {
            $colMap['payable'] = 9;
        }

        $parsed = [];
        $ignoredSummaryKeywords = ['totals summary', 'total of value', 'total of charge', 'total of payable', 'totals', 'description', 'amount'];

        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $tracking = trim((string) ($row[$colMap['tracking']] ?? ''));

            if (empty($tracking)) {
                continue;
            }

            // Exclude summary rows at bottom of Domex export
            $lowerTracking = strtolower($tracking);
            if (in_array($lowerTracking, $ignoredSummaryKeywords) || ! preg_match('/^[a-zA-Z0-9_-]+$/', $tracking)) {
                continue;
            }

            // If tracking looks like a number only and row 0 has text like "Total", skip
            if (is_numeric($tracking) && strlen($tracking) > 8) {
                // Large numeric tracking or summary total? Domex trackings typically start with "EC..."
                $firstColText = strtolower(trim((string) ($row[0] ?? $row[1] ?? '')));
                if (str_contains($firstColText, 'total')) {
                    continue;
                }
            }

            $dateStr = trim((string) ($row[$colMap['date']] ?? ''));
            $nameStr = trim((string) ($row[$colMap['name']] ?? ''));
            $addressStr = trim((string) ($row[$colMap['address']] ?? ''));
            $refNoStr = ($colMap['ref_no'] !== -1) ? trim((string) ($row[$colMap['ref_no']] ?? '')) : '';

            $domexValue = (float) str_replace([',', ' '], '', (string) ($row[$colMap['value']] ?? 0));
            $domexCharge = (float) str_replace([',', ' '], '', (string) ($row[$colMap['charge']] ?? 0));
            $domexPayable = (float) str_replace([',', ' '], '', (string) ($row[$colMap['payable']] ?? ($domexValue - $domexCharge)));

            // Query database for matched order
            $sale = Sale::with(['deliverySale', 'domexOrderDetail', 'customer'])
                ->where(function ($q) use ($tracking) {
                    $q->whereHas('domexOrderDetail', fn ($dq) => $dq->where('waybill_number', $tracking))
                        ->orWhereHas('deliverySale', fn ($dq) => $dq->where('tracking_number', $tracking))
                        ->orWhere('invoice_number', $tracking)
                        ->orWhere('sale_id', $tracking);
                })
                ->first();

            $isMatched = ! is_null($sale);
            $systemCharge = 450.0;
            $variance = 0.0;
            $varianceType = 'exact';
            $isPossibleReturn = ($domexValue <= 0 || $domexPayable < 0);
            $isAlreadyCompleted = false;
            $isAlreadyReturned = false;

            if ($isMatched) {
                $systemCharge = (float) ($sale->deliverySale->delivery_charge ?? 450.0);
                // Variance: System Estimated Charge - Domex Actual Charge
                // If systemCharge (450) > domexCharge (425) => Store estimated higher (+25 profit)
                // If systemCharge (450) < domexCharge (700) => Store undercharged (-250 loss)
                $variance = $systemCharge - $domexCharge;
                if ($variance > 0) {
                    $varianceType = 'saved';
                } elseif ($variance < 0) {
                    $varianceType = 'undercharged';
                } else {
                    $varianceType = 'exact';
                }

                $isAlreadyCompleted = ($sale->delivery_status === 'completed' && (float) $sale->due_amount <= 0);
                $isAlreadyReturned = ($sale->delivery_status === 'returned');
            }

            $parsed[] = [
                'excel_row_num' => $i + 1,
                'tracking_no' => $tracking,
                'invoiced_date' => $dateStr,
                'receiver_name' => $nameStr,
                'receiver_address' => $addressStr,
                'ref_no' => $refNoStr,
                'domex_value' => $domexValue,
                'domex_charge' => $domexCharge,
                'domex_payable' => $domexPayable,
                // Matched Order Info
                'is_matched' => $isMatched,
                'sale_id' => $sale?->id,
                'invoice_number' => $sale?->invoice_number,
                'customer_name' => $sale?->customer?->name ?: ($sale?->walking_customer_name ?: $nameStr),
                'customer_phone' => $sale?->customer?->phone ?: $sale?->walking_customer_phone,
                'system_total' => (float) ($sale?->total_amount ?? 0),
                'system_due' => (float) ($sale?->due_amount ?? 0),
                'system_charge' => $systemCharge,
                'variance' => $variance,
                'variance_type' => $varianceType,
                'delivery_status' => $sale?->delivery_status ?? 'unmatched',
                'payment_status' => $sale?->payment_status ?? 'unmatched',
                'is_possible_return' => $isPossibleReturn,
                'is_already_completed' => $isAlreadyCompleted,
                'is_already_returned' => $isAlreadyReturned,
                'status_action_note' => '',
            ];
        }

        $this->parsedRecords = $parsed;
        $this->computeReconciliationKpis();
    }

    /**
     * Compute summary KPIs across all parsed records.
     */
    protected function computeReconciliationKpis(): void
    {
        $totalRows = count($this->parsedRecords);
        $matchedCount = 0;
        $unmatchedCount = 0;
        $returnsCount = 0;
        $varianceCount = 0;
        $alreadyCompletedCount = 0;

        $totalDomexValue = 0.0;
        $totalDomexCharge = 0.0;
        $totalDomexPayable = 0.0;
        $totalSystemCharge = 0.0;
        $totalNetVariance = 0.0;

        foreach ($this->parsedRecords as $r) {
            $totalDomexValue += $r['domex_value'];
            $totalDomexCharge += $r['domex_charge'];
            $totalDomexPayable += $r['domex_payable'];

            if ($r['is_matched']) {
                $matchedCount++;
                $totalSystemCharge += $r['system_charge'];
                $totalNetVariance += $r['variance'];

                if ($r['variance_type'] !== 'exact') {
                    $varianceCount++;
                }
                if ($r['is_already_completed']) {
                    $alreadyCompletedCount++;
                }
            } else {
                $unmatchedCount++;
            }

            if ($r['is_possible_return']) {
                $returnsCount++;
            }
        }

        $this->reconciliationKpis = [
            'total_rows' => $totalRows,
            'matched_count' => $matchedCount,
            'unmatched_count' => $unmatchedCount,
            'returns_count' => $returnsCount,
            'variance_count' => $varianceCount,
            'already_completed_count' => $alreadyCompletedCount,
            'total_domex_value' => $totalDomexValue,
            'total_domex_charge' => $totalDomexCharge,
            'total_domex_payable' => $totalDomexPayable,
            'total_system_charge' => $totalSystemCharge,
            'total_net_variance' => $totalNetVariance,
        ];
    }

    /**
     * Settle a single matched order using Domex Remittance details.
     */
    public function applySingleSettlement(string $trackingNo): void
    {
        $index = $this->findRecordIndex($trackingNo);
        if ($index === null) {
            $this->dispatch('show-toast', type: 'error', message: "Record with tracking {$trackingNo} not found.");

            return;
        }

        $rec = $this->parsedRecords[$index];
        if (! $rec['is_matched'] || ! $rec['sale_id']) {
            $this->dispatch('show-toast', type: 'error', message: 'Cannot settle an unmatched order.');

            return;
        }

        try {
            DB::beginTransaction();

            $sale = Sale::with(['deliverySale', 'customer'])->findOrFail($rec['sale_id']);

            // 1. Update delivery charge to Domex actual charge if configured
            if ($this->updateDeliveryCharges && $sale->deliverySale) {
                $sale->deliverySale->update([
                    'delivery_charge' => $rec['domex_charge'],
                    'status' => 'Completed',
                ]);
            }

            // 2. Record courier remittance payment for any outstanding due amount
            $dueAmount = (float) $sale->due_amount;
            if ($dueAmount > 0) {
                Payment::create([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'amount' => $dueAmount,
                    'payment_method' => 'bank_transfer',
                    'payment_reference' => "Domex Remittance: {$trackingNo}",
                    'is_completed' => true,
                    'payment_date' => now(),
                    'status' => 'paid',
                    'collected_by' => Auth::id(),
                    'notes' => "Settled via Domex Excel import. Actual courier fee: Rs. {$rec['domex_charge']} (Variance: Rs. {$rec['variance']})",
                ]);
                $sale->due_amount = 0;
            }

            $sale->payment_status = 'paid';
            $sale->delivery_status = 'completed';
            if (! $sale->delivered_at) {
                $sale->delivered_at = now();
            }
            $sale->save();

            DB::commit();

            // Update in-memory state
            $this->parsedRecords[$index]['is_already_completed'] = true;
            $this->parsedRecords[$index]['delivery_status'] = 'completed';
            $this->parsedRecords[$index]['payment_status'] = 'paid';
            $this->parsedRecords[$index]['system_due'] = 0;
            $this->parsedRecords[$index]['system_charge'] = $rec['domex_charge'];
            $this->parsedRecords[$index]['variance'] = 0;
            $this->parsedRecords[$index]['variance_type'] = 'exact';
            $this->parsedRecords[$index]['status_action_note'] = 'Settled & Completed';

            $this->computeReconciliationKpis();
            $this->dispatch('show-toast', type: 'success', message: "Order #{$sale->invoice_number} settled & marked as Completed!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Domex Settlement Error: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Failed to settle: '.$e->getMessage());
        }
    }

    /**
     * Settle all eligible matched orders in bulk.
     */
    public function applyBulkSettlement(): void
    {
        $settledCount = 0;
        $settledAmount = 0.0;

        try {
            DB::beginTransaction();

            foreach ($this->parsedRecords as $idx => $rec) {
                if (! $rec['is_matched'] || ! $rec['sale_id']) {
                    continue;
                }
                // Skip if already completed or possible return with 0 value
                if ($rec['is_already_completed'] || $rec['is_possible_return']) {
                    continue;
                }

                $sale = Sale::with(['deliverySale', 'customer'])->find($rec['sale_id']);
                if (! $sale) {
                    continue;
                }

                if ($this->updateDeliveryCharges && $sale->deliverySale) {
                    $sale->deliverySale->update([
                        'delivery_charge' => $rec['domex_charge'],
                        'status' => 'Completed',
                    ]);
                }

                $due = (float) $sale->due_amount;
                if ($due > 0) {
                    Payment::create([
                        'sale_id' => $sale->id,
                        'customer_id' => $sale->customer_id,
                        'amount' => $due,
                        'payment_method' => 'bank_transfer',
                        'payment_reference' => "Domex Bulk Remittance: {$rec['tracking_no']}",
                        'is_completed' => true,
                        'payment_date' => now(),
                        'status' => 'paid',
                        'collected_by' => Auth::id(),
                        'notes' => "Bulk settled via Domex Excel import. Courier fee: Rs. {$rec['domex_charge']} (Variance: Rs. {$rec['variance']})",
                    ]);
                    $sale->due_amount = 0;
                    $settledAmount += $due;
                }

                $sale->payment_status = 'paid';
                $sale->delivery_status = 'completed';
                if (! $sale->delivered_at) {
                    $sale->delivered_at = now();
                }
                $sale->save();

                // Update in-memory record
                $this->parsedRecords[$idx]['is_already_completed'] = true;
                $this->parsedRecords[$idx]['delivery_status'] = 'completed';
                $this->parsedRecords[$idx]['payment_status'] = 'paid';
                $this->parsedRecords[$idx]['system_due'] = 0;
                $this->parsedRecords[$idx]['system_charge'] = $rec['domex_charge'];
                $this->parsedRecords[$idx]['variance'] = 0;
                $this->parsedRecords[$idx]['variance_type'] = 'exact';
                $this->parsedRecords[$idx]['status_action_note'] = 'Settled & Completed';

                $settledCount++;
            }

            DB::commit();

            $this->computeReconciliationKpis();
            $this->dispatch('show-toast', type: 'success', message: "Successfully settled {$settledCount} orders! Total: Rs. ".number_format($settledAmount, 2));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Domex Bulk Settlement Error: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Bulk settlement failed: '.$e->getMessage());
        }
    }

    /**
     * Manually confirm an order return (for items where Domex collected Value = 0).
     */
    public function confirmSingleReturn(string $trackingNo): void
    {
        $index = $this->findRecordIndex($trackingNo);
        if ($index === null) {
            $this->dispatch('show-toast', type: 'error', message: "Record with tracking {$trackingNo} not found.");

            return;
        }

        $rec = $this->parsedRecords[$index];
        if (! $rec['is_matched'] || ! $rec['sale_id']) {
            $this->dispatch('show-toast', type: 'error', message: 'Cannot process return for an unmatched tracking.');

            return;
        }

        try {
            DB::beginTransaction();

            $sale = Sale::with(['deliverySale', 'items'])->findOrFail($rec['sale_id']);

            // Restore jewelry stock to inventory
            $sale->restoreStock();

            $sale->delivery_status = 'returned';
            $sale->notes = ($sale->notes ? $sale->notes.' | ' : '')."Confirmed returned via Domex Remittance Excel. Domex Return Fee: Rs. {$rec['domex_charge']}";

            if ($sale->deliverySale) {
                $sale->deliverySale->update([
                    'status' => 'Returned',
                    'returned_at' => now(),
                    'delivery_charge' => $this->updateDeliveryCharges ? $rec['domex_charge'] : $sale->deliverySale->delivery_charge,
                    'return_reason' => "Domex return confirmed from remittance Excel (Fee: Rs. {$rec['domex_charge']})",
                ]);
            }

            $sale->save();

            DB::commit();

            // Update in-memory record
            $this->parsedRecords[$index]['is_already_returned'] = true;
            $this->parsedRecords[$index]['delivery_status'] = 'returned';
            $this->parsedRecords[$index]['status_action_note'] = 'Return Confirmed & Stock Restored';

            $this->computeReconciliationKpis();
            $this->dispatch('show-toast', type: 'success', message: "Order #{$sale->invoice_number} confirmed as Returned! Stock restored to inventory.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Domex Return Confirmation Error: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Failed to confirm return: '.$e->getMessage());
        }
    }

    /**
     * Confirm all detected returns in bulk.
     */
    public function confirmBulkReturns(): void
    {
        $count = 0;

        try {
            DB::beginTransaction();

            foreach ($this->parsedRecords as $idx => $rec) {
                if (! $rec['is_matched'] || ! $rec['sale_id'] || ! $rec['is_possible_return'] || $rec['is_already_returned']) {
                    continue;
                }

                $sale = Sale::with(['deliverySale', 'items'])->find($rec['sale_id']);
                if (! $sale) {
                    continue;
                }

                $sale->restoreStock();
                $sale->delivery_status = 'returned';
                $sale->notes = ($sale->notes ? $sale->notes.' | ' : '')."Bulk return confirmed via Domex Excel. Return fee: Rs. {$rec['domex_charge']}";

                if ($sale->deliverySale) {
                    $sale->deliverySale->update([
                        'status' => 'Returned',
                        'returned_at' => now(),
                        'delivery_charge' => $this->updateDeliveryCharges ? $rec['domex_charge'] : $sale->deliverySale->delivery_charge,
                        'return_reason' => "Domex bulk return confirmed via remittance Excel (Fee: Rs. {$rec['domex_charge']})",
                    ]);
                }

                $sale->save();

                $this->parsedRecords[$idx]['is_already_returned'] = true;
                $this->parsedRecords[$idx]['delivery_status'] = 'returned';
                $this->parsedRecords[$idx]['status_action_note'] = 'Return Confirmed';

                $count++;
            }

            DB::commit();

            $this->computeReconciliationKpis();
            $this->dispatch('show-toast', type: 'success', message: "Confirmed {$count} returned parcels! Stock restored to inventory.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Domex Bulk Return Error: '.$e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Bulk return confirmation failed: '.$e->getMessage());
        }
    }

    /**
     * Clear the current import and reset to upload view.
     */
    public function clearImport(): void
    {
        $this->excelFile = null;
        $this->fileName = '';
        $this->parsedRecords = [];
        $this->reconciliationKpis = [];
        $this->search = '';
        $this->tabFilter = 'all';
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function setTabFilter(string $tab): void
    {
        $this->tabFilter = $tab;
    }

    protected function findRecordIndex(string $trackingNo): ?int
    {
        foreach ($this->parsedRecords as $idx => $r) {
            if (strcasecmp($r['tracking_no'], $trackingNo) === 0) {
                return $idx;
            }
        }

        return null;
    }

    /**
     * Filter parsed records for the UI view based on active tab and search query.
     */
    public function getFilteredRecordsProperty(): array
    {
        return array_filter($this->parsedRecords, function ($rec) {
            // 1. Tab Filter
            if ($this->tabFilter === 'matched' && ! $rec['is_matched']) {
                return false;
            }
            if ($this->tabFilter === 'variance' && ($rec['variance_type'] === 'exact' || ! $rec['is_matched'])) {
                return false;
            }
            if ($this->tabFilter === 'returns' && ! $rec['is_possible_return']) {
                return false;
            }
            if ($this->tabFilter === 'unmatched' && $rec['is_matched']) {
                return false;
            }

            // 2. Search Filter
            if (! empty(trim($this->search))) {
                $term = strtolower(trim($this->search));
                $haystack = strtolower(
                    $rec['tracking_no'].' '.
                    $rec['receiver_name'].' '.
                    ($rec['invoice_number'] ?? '').' '.
                    ($rec['customer_name'] ?? '').' '.
                    ($rec['customer_phone'] ?? '').' '.
                    $rec['receiver_address']
                );

                return str_contains($haystack, $term);
            }

            return true;
        });
    }

    public function render()
    {
        return view('livewire.delivery-man.domex-remittance-import', [
            'records' => $this->filteredRecords,
        ]);
    }
}
