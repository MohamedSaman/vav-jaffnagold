<div class="container-fluid py-3">

    {{-- Main Container Workspace Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        @if($payrollView == 1)
            <div>
                <h4 class="fw-semibold m-0" style="font-size: 14px;">Payroll — {{ now()->format('F Y') }}</h4>
                <small class="text-muted d-block" style="font-size: 11px;">{{ $employees->total() }} total employees tracking this active payroll period</small>
            </div>
            <div>
                <button class="btn btn-dark btn-sm px-3 border-0 rounded-1" wire:click="createEmployee" style="font-size: 12px;">
                    <i class="bi bi-plus-lg me-2"></i> Create Employee
                </button>
            </div>
        @else
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light rounded-1 px-2 py-1 fw-medium d-flex align-items-center gap-1" 
        style="font-size: 12px; border: 0.5px solid #b45309 !important; color: #b45309;" 
        wire:click="switchPayrollView(1)">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <div>
                    <h5 class="fw-semibold m-0" style="font-size: 14px;">{{ $activeHistoryEmployee->name }}</h5>
                    <small class="text-muted d-block" style="font-size: 11px;">Staff Associate — Ledger Records History</small>
                </div>
            </div>
        @endif
    </div>

    {{-- VIEW 1: EMPLOYEE PAYROLL TABLE --}}
    @if($payrollView == 1)
        <div class="card bg-white border rounded-1 shadow-none">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center" style="border-bottom-width: 0.5px !important;">
                <span class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Employee Registry Ledger</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size: 12px;">Show</span>
                    <select wire:model.live="perPage" class="form-select form-select-sm rounded-1" style="width: 70px; font-size: 12px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle m-0 table-borderless" style="font-family: 'Inter', sans-serif;">
                        <thead>
                            <tr class="bg-light border-bottom" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border-bottom-width: 0.5px !important;">
                                <th class="ps-3 py-2 text-muted">Employee</th>
                                <th class="py-2 text-muted">Daily Rate</th>
                                <th class="py-2 text-muted">Awaiting Payment</th>
                                <th class="text-center py-2 text-muted" style="width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $emp)
                                <tr class="border-bottom style-row-hover" style="border-bottom-width: 0.5px !important; transition: none; font-size: 13px; font-weight: 500;">
                                    <td class="ps-3 py-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center bg-light rounded-circle text-secondary" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600; border: 0.5px solid rgba(0,0,0,0.08); flex-shrink: 0;">
                                                {{ $emp->initials }}
                                            </div>
                                            <div>
                                                <div class="text-dark" style="font-size: 13px; font-weight: 600; line-height: 1.2;">{{ $emp->name }}</div>
                                            </div>
                                        </div>
                                       </div>
                                    </td>
                                    <td class="py-2" style="font-variant-numeric: tabular-nums; font-size: 13px; font-weight: 600;">
                                        Rs. {{ number_format($emp->basic_salary, 2) }}
                                    </td>
                                    <td class="py-2" style="font-variant-numeric: tabular-nums; font-size: 13px; font-weight: 600;">
                                        @if($emp->awaiting_payments > 0)
                                            <span style="color: #f59e0b; background: #fef3c7; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                                Rs. {{ number_format($emp->awaiting_payments, 2) }}
                                            </span>
                                        @else
                                            <span style="color: #10b981; background: #dcfce7; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                                Fully Paid
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center py-2">
                                        <div class="d-flex justify-content-center gap-1">
                                            @if($emp->balance_amount <= 0)
                                                <button class="btn btn-danger btn-sm rounded-1 px-2 py-1 fw-semibold" style="font-size: 11px; min-width: 60px; opacity: 0.65; cursor: not-allowed;" disabled title="Fully Settled">
                                                    <i class="bi bi-check-circle me-1"></i> Paid
                                                </button>
                                            @else
                                                <button class="btn btn-success btn-sm rounded-1 px-2 py-1 text-white fw-semibold" 
                                                    style="font-size: 11px; min-width: 60px;" 
                                                    wire:click="openPartialPayment({{ $emp->id }})"
                                                    title="Make Payment (Full or Partial)">
                                                    <i class="bi bi-cash-stack me-1"></i> Pay
                                                </button>
                                            @endif

                                            <button class="btn btn-light btn-sm border rounded-1 px-2 py-1" style="font-size: 11px; min-width: 32px; border-width: 0.5px !important;" wire:click="viewEmployeeDetails({{ $emp->id }})" title="View Details">
                                                <i class="bi bi-person-lines-fill"></i>
                                            </button>
                                            
                                            <button class="btn btn-light btn-sm border rounded-1 px-2 py-1" style="font-size: 11px; min-width: 32px; border-width: 0.5px !important;" wire:click="editEmployee({{ $emp->id }})" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
                                            <button class="btn btn-light text-danger btn-sm border rounded-1 px-2 py-1" style="font-size: 11px; min-width: 32px; border-width: 0.5px !important;" wire:click="confirmDelete({{ $emp->id }})" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            
                                            <button class="btn btn-sm bg-transparent border rounded-1 text-secondary px-2 py-1 btn-history-ghost" style="font-size: 11px; min-width: 32px; border-width: 0.5px !important;" wire:click="switchPayrollView(2, {{ $emp->id }})" title="History">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5" style="font-size: 13px;">
                                        <i class="bi bi-inbox d-block fs-4 mb-2"></i>
                                        <span>No employees found.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($employees->hasPages())
                    <div class="p-3 border-top" style="border-top-width: 0.5px !important;">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>

    {{-- VIEW 2: HISTORY TABLE --}}
    @elseif($payrollView == 2 && $activeHistoryEmployee)
        <div class="card border-0 shadow-sm overflow-hidden" style="background:#ffffff; border:1px solid #e2e8f0 !important; border-radius:12px;">
            <div class="d-flex align-items-center justify-content-between px-4 py-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom:1px solid #e2e8f0;">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width:46px;height:46px;background:#3b82f6;border:2px solid #ffffff;font-family:'Inter', sans-serif;font-size:15px;font-weight:600;color:#ffffff;flex-shrink:0;">
                        {{ $activeHistoryEmployee->initials }}
                    </div>
                    <div>
                        <div style="font-size:16px;font-weight:600;color:#0f172a;line-height:1.2;">{{ $activeHistoryEmployee->name }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:4px;font-weight: 500;">Staff Associate — Daily Rate: Rs. {{ number_format($activeHistoryEmployee->basic_salary, 2) }}/day</div>
                    </div>
                </div>
                @php
                    $lastBalance = count($historyData) > 0 ? end($historyData)['balance'] : 0;
                @endphp
                @if($lastBalance <= 0)
                    <span style="font-size:12px;padding:4px 12px;border-radius:99px;font-weight:600;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">✓ Fully Settled</span>
                @else
                    <span style="font-size:12px;padding:4px 12px;border-radius:99px;font-weight:600;background:#fef9c3;color:#a16207;border:1px solid #fef08a; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">⟳ Pending: Rs. {{ number_format($lastBalance, 2) }}</span>
                @endif
            </div>

            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table m-0 table-borderless" style="table-layout:fixed;width:100%; border-collapse: separate; border-spacing: 0;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr style="background:#b6b8bb;border:2px solid #000000;font-size:12px;text-transform:uppercase;letter-spacing:0.05em;color:#ffffff;font-weight:600;">
                            <th class="py-3 px-3 text-center" style="width: 12%;">Date</th>
                            <th class="py-3 px-3 text-center" style="width: 25%;">Details</th>
                            <th class="py-3 px-3 text-center" style="width: 12%;">Salary</th>
                            <th class="py-3 px-3 text-center" style="width: 15%;">Drawn Amount</th>
                            <th class="py-3 px-3 text-center" style="width: 15%;">Balance</th>
                            <th class="py-3 px-3 text-center" style="width: 21%;">Awaiting Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historyData as $index => $record)
                            @php
                                $salaryAmount = (float)($record['salary'] ?? 0);
                                $drawnAmount = (float)($record['drawn_amount'] ?? 0);
                                $paymentAmount = (float)($record['payment_amount'] ?? 0);
                                $balanceAmount = (float)($record['balance'] ?? 0);
                                $awaitingPayments = (float)($record['awaiting_payments'] ?? 0);
                                $isPayment = $record['is_payment'] ?? false;
                                $isDraw = $record['is_draw'] ?? false;
                                $bgColor = $record['background_color'] ?? '';
                                $textColor = $record['text_color'] ?? '';
                                $rowStyle = $bgColor ? 'background-color: ' . $bgColor . '; color: ' . $textColor . ';' : '';
                                
                                $showSalary = $salaryAmount > 0;
                                $showDrawnAmount = $drawnAmount > 0;
                                $showPaymentAmount = $paymentAmount > 0 && !$showDrawnAmount;
                            @endphp
                            <tr style="border-bottom:1px solid #f1f5f9; transition: background 0.2s; {{ $rowStyle }}" 
                                @if(!$bgColor) onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'" @endif>
                                <td class="px-3 py-3 text-center">
                                    <div style="font-family:'Inter', sans-serif;font-size:13px;font-weight:600;color:{{ $textColor ?: '#334155' }};">
                                        {{ $record['date'] }}
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span style="font-size:12px;font-weight:500;color:{{ $textColor ?: '#475569' }};">
                                        {{ $record['details'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($showSalary)
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;font-weight:600;color:#10b981; background: #dcfce7; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                            Rs. {{ number_format($salaryAmount, 2) }}
                                        </span>
                                    @else
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($showDrawnAmount)
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;font-weight:600;color:#ef4444; background: #fee2e2; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                            Rs. {{ number_format($drawnAmount, 2) }}
                                        </span>
                                    @elseif($showPaymentAmount)
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;font-weight:600;color:#f59e0b; background: #fef3c7; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                            Rs. {{ number_format($paymentAmount, 2) }}
                                        </span>
                                    @else
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;color:#94a3b8;">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span style="font-family:'Inter', sans-serif;font-size:14px;font-weight:700;color:{{ $balanceAmount <= 0 ? '#10b981' : ($balanceAmount > 500 ? '#ef4444' : '#f59e0b') }};">
                                        Rs. {{ number_format($balanceAmount, 2) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($awaitingPayments > 0)
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;font-weight:600;color:#f59e0b; background: #fef3c7; padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                            Rs. {{ number_format($awaitingPayments, 2) }}
                                        </span>
                                    @else
                                        <span style="font-family:'Inter', sans-serif;font-size:13px;color:#94a3b8;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5" style="color:#64748b;font-size:14px; background: #ffffff;">
                                    <div style="font-size: 28px; margin-bottom: 8px; opacity: 0.5;">📋</div>
                                    No transaction records found for this employee.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Create Modal --}}
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);" wire:click.self="closeModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-1 shadow-none">
                    <div class="modal-header border-bottom py-2 px-3"><h6 class="modal-title fw-bold m-0">Create Employee</h6><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body p-3">
                        <form wire:submit.prevent="saveEmployee">
                            <div class="mb-2"><label class="form-label mb-1 text-muted">Employee Name *</label><input type="text" class="form-control form-control-sm rounded-1 @error('name') is-invalid @enderror" wire:model="name" placeholder="Enter employee name">@error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="mb-2"><label class="form-label mb-1 text-muted">Contact Number *</label><input type="text" class="form-control form-control-sm rounded-1 @error('contactNumber') is-invalid @enderror" wire:model="contactNumber" placeholder="Enter contact number">@error('contactNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="mb-3"><label class="form-label mb-1 text-muted">Daily Salary Rate *</label><input type="number" class="form-control form-control-sm rounded-1 @error('basic_salary') is-invalid @enderror" wire:model="basic_salary" placeholder="0.00" step="0.01" min="0">@error('basic_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-light btn-sm border rounded-1 px-3" wire:click="closeModal">Cancel</button><button type="submit" class="btn btn-dark btn-sm rounded-1 px-3">Create</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);" wire:click.self="closeModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-1 shadow-none">
                    <div class="modal-header border-bottom py-2 px-3"><h6 class="modal-title fw-bold m-0">Edit Employee</h6><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body p-3">
                        <form wire:submit.prevent="updateEmployee">
                            <div class="mb-2"><label class="form-label mb-1 text-muted">Employee Name *</label><input type="text" class="form-control form-control-sm rounded-1 @error('editName') is-invalid @enderror" wire:model="editName" placeholder="Enter employee name">@error('editName') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="mb-2"><label class="form-label mb-1 text-muted">Contact Number *</label><input type="text" class="form-control form-control-sm rounded-1 @error('editContactNumber') is-invalid @enderror" wire:model="editContactNumber" placeholder="Enter contact number">@error('editContactNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="mb-2"><label class="form-label mb-1 text-muted">Daily Salary Rate *</label><input type="number" class="form-control form-control-sm rounded-1 @error('editBasicSalary') is-invalid @enderror" wire:model="editBasicSalary" placeholder="0.00" step="0.01" min="0">@error('editBasicSalary') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="mb-3"><label class="form-label mb-1 text-muted">Status *</label><select class="form-select form-select-sm rounded-1 @error('editStatus') is-invalid @enderror" wire:model="editStatus"><option value="active">Active</option><option value="inactive">Inactive</option></select>@error('editStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror</div>
                            <div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-light btn-sm border rounded-1 px-3" wire:click="closeModal">Cancel</button><button type="submit" class="btn btn-dark btn-sm rounded-1 px-3">Update</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);" wire:click.self="closeModal">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 rounded-1 shadow-none">
                    <div class="modal-body p-4 text-center"><div class="text-danger mb-3" style="font-size: 32px;"><i class="bi bi-trash"></i></div><h6 class="fw-bold mb-1">Delete Employee?</h6><p class="text-muted mb-3">This action cannot be undone.</p><div class="d-flex justify-content-center gap-2"><button class="btn btn-light btn-sm border rounded-1 px-3" wire:click="closeModal">Cancel</button><button class="btn btn-danger btn-sm rounded-1 px-3" wire:click="deleteEmployee">Delete</button></div></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Details Modal --}}
    @if($showDetailsModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);" wire:click.self="closeModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-1 shadow-none">
                    <div class="modal-header border-bottom py-2 px-3"><h6 class="modal-title fw-bold m-0">Employee Details</h6><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body p-3">
                        <table class="table table-sm table-borderless m-0">
                            <tr><td class="text-muted ps-0" style="width: 40%;">Name</td><td class="fw-semibold">{{ $viewEmployeeData['name'] ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-0">Contact</td><td class="fw-semibold">{{ $viewEmployeeData['contact'] ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-0">Daily Salary Rate</td><td class="fw-semibold">Rs. {{ isset($viewEmployeeData['basic_salary']) ? number_format($viewEmployeeData['basic_salary'], 2) : '0.00' }} / day</td></tr>
                            <tr><td class="text-muted ps-0">Status</td><td>@if(isset($viewEmployeeData['status']) && $viewEmployeeData['status'] === 'active')<span class="badge rounded-pill" style="background-color: rgba(25,135,84,0.1); color: rgb(21,115,71);">Active</span>@else<span class="badge rounded-pill" style="background-color: rgba(220,53,69,0.1); color: rgb(185,28,28);">Inactive</span>@endif</td></tr>
                            <tr><td class="text-muted ps-0">Joined</td><td class="fw-semibold">{{ $viewEmployeeData['joined'] ?? '—' }}</td></tr>
                        瞄准
                    </div>
                    <div class="modal-footer border-top py-2 px-3"><button class="btn btn-light btn-sm border rounded-1 px-3" wire:click="closeModal">Close</button></div>
                </div>
            </div>
        </div>
    @endif

    {{-- Partial Payment Modal --}}
    @if($showPartialPaymentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:click.self="closeModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-1 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header border-0 py-3" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white;">
                        <h5 class="modal-title fw-bold d-flex align-items-center"><i class="bi bi-cash-coin me-2 fs-5"></i>Make Payment</h5>
                        <button type="button" class="btn-close btn-close-white shadow-none" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="processPartialPayment">
                            <div class="mb-3 p-3 rounded-2" style="background-color: #f0f9ff; border: 1px solid #bae6fd;">
                                <div class="d-flex justify-content-between align-items-center"><span class="text-muted small">Employee</span><span class="fw-bold text-dark">{{ $partialPaymentEmployeeName }}</span></div>
                                <div class="d-flex justify-content-between align-items-center mt-2"><span class="text-muted small">Outstanding Balance</span><span class="fw-bold text-danger">Rs. {{ number_format($partialPaymentMaxAmount, 2) }}</span></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Payment Amount <span class="text-danger">*</span></label>
                                <div class="input-group"><span class="input-group-text border-end-0 bg-light text-muted" style="border-radius: 8px 0 0 8px;">Rs.</span><input type="number" step="0.01" class="form-control border-start-0 @error('partialPaymentAmount') is-invalid @enderror" style="border-radius: 0 8px 8px 0; padding: 12px 15px;" wire:model="partialPaymentAmount" placeholder="Enter amount"></div>
                                @error('partialPaymentAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                                <small class="text-muted">Enter any amount up to the outstanding balance. Enter full amount to settle completely.</small>
                            </div>
                            <div class="mb-3"><label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Payment Date <span class="text-danger">*</span></label><input type="date" class="form-control @error('partialPaymentDate') is-invalid @enderror" style="border-radius: 8px; padding: 12px 15px;" wire:model="partialPaymentDate">@error('partialPaymentDate') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                            <div class="mb-4"><label class="form-label fw-bold text-dark small text-uppercase tracking-wider">Note (Optional)</label><textarea class="form-control" style="border-radius: 8px; padding: 12px 15px;" wire:model="partialPaymentNote" rows="2" placeholder="Add payment reference or note..."></textarea></div>
                            <div class="d-grid gap-2"><button type="submit" class="btn btn-primary py-2 fw-bold shadow-sm d-flex justify-content-center align-items-center" style="border-radius: 8px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border: none;"><i class="bi bi-check-circle me-2"></i> Process Payment</button><button type="button" class="btn btn-light border py-2" wire:click="closeModal" style="border-radius: 8px;">Cancel</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>