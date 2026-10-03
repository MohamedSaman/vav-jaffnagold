<div>
    @if($showModal)
    <style>
        .excel-table {
            border: 2px solid #333;
            border-collapse: collapse;
            width: 100%;
            background-color: #fff;
        }
        .excel-table td {
            border: 1px solid #ccc;
            padding: 10px 15px;
            font-size: 0.95rem;
            color: #333;
        }
        .excel-header {
            background-color: #e9ecef;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #333 !important;
        }
        .excel-label {
            font-weight: 600;
            background-color: #f8f9fa;
        }
        .excel-value {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            text-align: right;
            font-size: 1.1rem;
        }
        .excel-total-row {
            background-color: #fff;
            border-top: 2px solid #333 !important;
        }
        .excel-total-label {
            font-weight: 900;
            font-size: 1.2rem;
            text-transform: uppercase;
        }
        .excel-total-value {
            font-weight: 900;
            font-size: 1.4rem;
            color: #000;
            text-align: right;
        }
        .excel-input {
            border: 2px solid transparent;
            background-color: #fff;
            width: 100%;
            padding: 8px;
            text-align: center;
            font-weight: 800;
            font-size: 1.2rem;
            color: #161b97;
            transition: all 0.2s;
        }
        .excel-input:focus {
            outline: none;
            border-color: #161b97;
            background-color: #f0f2ff;
        }
        .expense-desc {
            padding-left: 25px !important;
            color: #555;
            font-style: italic;
        }
    </style>

    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1050;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 0;">
                <div class="modal-header bg-dark text-white border-0 py-2 px-3" style="border-radius: 0;">
                    <h6 class="modal-title fw-bold mb-0">DAILY ENTRY BOOK SUMMARY</h6>
                    <button type="button" wire:click="downloadDailySummary" class="btn btn-sm btn-light rounded-pill px-3 ms-auto me-3 d-flex align-items-center gap-1.5 fw-bold" style="color: #161b97; border: none; font-size: 12px; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                    </button>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal" style="font-size: 0.7rem;"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="excel-table shadow-sm">
                        <table class="w-100">
                            <tbody>
                                {{-- Income Header --}}
                                <tr>
                                    <td colspan="2" class="excel-header py-2 px-3">Income</td>
                                </tr>

                                {{-- Income Details --}}
                                <tr>
                                    <td class="excel-label ps-4 py-2">Today's Opening Balance</td>
                                    <td class="excel-value py-2 px-3 text-info">{{ number_format($summaryData['openingBalance'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-label ps-4 py-2">Cash Sale</td>
                                    <td class="excel-value py-2 px-3 text-success">{{ number_format($summaryData['cashSale'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-label ps-4 py-2">Online</td>
                                    <td class="excel-value py-2 px-3 text-primary">{{ number_format($summaryData['onlineSale'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-label ps-4 py-2">ATM</td>
                                    <td class="excel-value py-2 px-3 text-info">{{ number_format($summaryData['bankSale'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-label ps-4 py-2">Today received due</td>
                                    <td class="excel-value py-2 px-3 text-success">{{ number_format($summaryData['receivedDue'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="excel-label ps-4 py-2">Gold Plating / Services (Cash)</td>
                                    <td class="excel-value py-2 px-3 text-warning">{{ number_format($summaryData['platingCash'] ?? 0, 0) }}</td>
                                </tr>
                                @if(($summaryData['platingOnline'] ?? 0) > 0)
                                <tr>
                                    <td class="excel-label ps-4 py-2">Gold Plating / Services (Online)</td>
                                    <td class="excel-value py-2 px-3 text-primary">{{ number_format($summaryData['platingOnline'] ?? 0, 0) }}</td>
                                </tr>
                                @endif

                                {{-- Total Income --}}
                                <tr class="excel-total-row">
                                    <td class="excel-total-label py-2.5 px-3">Total Income</td>
                                    <td class="excel-total-value py-2.5 px-3 text-success">{{ number_format($summaryData['totalIncome'], 0) }}</td>
                                </tr>

                                {{-- Expenses Header --}}
                                <tr>
                                    <td colspan="2" class="excel-header py-2 px-3">Expenses</td>
                                </tr>

                                {{-- List Expenses --}}
                                @forelse($expenses as $expense)
                                <tr>
                                    <td class="expense-desc py-2">
                                        <div class="d-flex flex-column">
                                            @if($expense->expense_type === 'Salary Payout' && $expense->salaryEmployee)
                                                <span><i class="bi bi-dash me-1"></i> {{ $expense->salaryEmployee->name }} - Salary</span>
                                            @else
                                                <span><i class="bi bi-dash me-1"></i> {{ $expense->expense_type }}</span>
                                                @if($expense->description && $expense->description !== $expense->expense_type)
                                                    <small class="text-muted ps-3" style="font-size: 0.75rem;">{{ $expense->description }}</small>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="excel-value py-2 px-3 text-danger">
                                        - {{ number_format($expense->amount, 0) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted small italic">No expenses recorded today</td>
                                </tr>
                                @endforelse

                                {{-- Total Expenses --}}
                                <tr class="excel-total-row">
                                    <td class="excel-total-label py-2.5 px-3">Total Expenses</td>
                                    <td class="excel-total-value py-2.5 px-3 text-danger">- {{ number_format($summaryData['totalExpenses'], 0) }}</td>
                                </tr>

                                {{-- Net Income --}}
                                <tr class="excel-total-row" style="background-color: #f8fafc;">
                                    <td class="excel-total-label py-2.5 px-3">Net Income</td>
                                    <td class="excel-total-value py-2.5 px-3" style="color: #161b97;">{{ number_format($summaryData['netIncome'], 0) }}</td>
                                </tr>

                                {{-- Tomorrow opening Balance (Input) --}}
                                <tr class="excel-total-row">
                                    <td class="excel-total-label align-middle py-2 px-3" style="font-size: 0.95rem; text-transform: none; font-weight: bold;">Tomorrow opening Balance</td>
                                    <td class="p-0" style="background-color: #fff; width: 40%;">
                                        <input type="number" step="0.01" class="excel-input" 
                                               wire:model="tomorrowOpeningBalance">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-3 px-3" style="border-radius: 0;">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 fw-bold" wire:click="closeModal">CANCEL</button>
                    <button type="button" class="btn btn-sm btn-dark px-4 fw-bold shadow-sm" wire:click="saveAndCloseDay" style="letter-spacing: 0.05em;">
                        CONFIRM & CLOSE DAY
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
