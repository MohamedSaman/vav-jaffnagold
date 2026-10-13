<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PriceHelper;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\SmsTopup;
use App\Models\StaffTypePermission;
use App\Models\User;
use App\Services\SmsService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('System Settings')]
class Settings extends Component
{
    use WithDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $settings = [];

    public $key;

    public $value;

    public $showModal = false;

    public $isEdit = false;

    public $editingId = null;

    public $deleteId = null;

    // Expense Management
    public $expenses = [];

    public $expenseCategories = [];

    public $expenseTypes = [];

    public $expenseCategory = '';

    public $expenseType = '';

    public $expenseAmount = '';

    public $expenseDate = '';

    public $expenseStatus = 'pending';

    public $expenseDescription = '';

    public $showExpenseModal = false;

    public $isEditExpense = false;

    public $editingExpenseId = null;

    public $deleteExpenseId = null;

    // New Expense Category Management
    public $showCategoryModal = false;

    public $newExpenseCategory = '';

    public $newExpenseType = '';

    public $customExpenseCategory = '';

    public $isEditCategory = false;

    public $editingCategoryId = null;

    public $deleteCategoryTypeId = null;

    // Staff Type Permission Management
    public $selectedStaffType = 'salesman';

    public $staffTypePermissions = [];

    public $showStaffPermissionModal = false;

    // POS Settings
    public $warranty_min_amount = 1000;

    // Price Helper Management
    public $priceHelpers = [];

    public $showPriceHelperModal = false;

    public $isEditPriceHelper = false;

    public $editingPriceHelperId = null;

    public $priceHelperItemName = '';

    public $priceHelperCategory = '';

    public $priceHelperMinPrice = '';

    public $priceHelperMaxPrice = '';

    public $priceHelperNotes = '';

    public $priceHelperSortOrder = 0;

    public $priceHelperIsActive = true;

    // Print Label Settings  (horizontal hang-tag: body + right-side tail)
    public $label_width = 48;   // printable body width (mm)

    public $label_height = 12;   // label height / feed direction (mm)

    public $label_padding = 1;    // inner horizontal padding (mm)

    public $label_text_width = 35;   // left text section (fold line) (mm)

    public $label_tail_width = 22;   // tail length for preview (mm)

    public $label_tail_height = 4;    // tail strip height for preview (mm)

    public $label_font_family = 'Courier New';

    public $label_font_shop = 6;

    public $label_font_price = 8;

    public $label_font_barcode = 5;

    public $label_qr_size = 11;

    public $label_barcode_type = 'datamatrix'; // 'datamatrix', 'qrcode_l', 'qrcode_m', 'code128'

    public $label_show_shop = true;

    public $label_show_barcode_text = true;

    public $label_show_qr = true;

    // SMS Gateway & Usage Settings
    public $sms_rate = 0.85;

    public $sms_low_balance_threshold = 50.00;

    public $sms_topup_contact = '0759037101';

    public $sms_enabled = true;

    public $sms_sender_id = 'Sportynix';

    public $sms_gateway_balance = null;

    public $sms_gateway_status = null;

    public $sms_active_plan = null;

    public $smsSearch = '';

    public $smsStatusFilter = '';

    public $smsDateFilter = '';

    public $showTopupModal = false;

    public $topupAmount = 1000;

    public $topupNotes = '';

    public $showTestSmsModal = false;

    public $testSmsPhone = '';

    public $testSmsMessage = 'Hello, this is a test SMS from Jaffna Gold (Vavuniya Branch).';

    protected $listeners = [
        'deleteConfirmed' => 'deleteConfiguration',
        'deleteExpenseConfirmed' => 'deleteExpense',
        'deleteCategoryTypeConfirmed' => 'deleteCategoryType',
    ];

    protected $rules = [
        'key' => 'required|string|max:255|unique:settings,key',
        'value' => 'required|string|max:255',
    ];

    protected $messages = [
        'key.required' => 'The configuration key is required.',
        'key.unique' => 'This configuration key already exists. Please use a different key.',
        'key.max' => 'The configuration key cannot exceed 255 characters.',
        'value.required' => 'The configuration value is required.',
        'value.max' => 'The configuration value cannot exceed 255 characters.',
    ];

    public function mount()
    {
        $this->loadSettings();
        $this->loadExpenses();
        $this->loadExpenseCategories();
        $this->loadStaffTypePermissions();
        $this->loadPOSSettings();
        $this->loadLabelSettings();
        $this->loadPriceHelpers();
        $this->loadSmsSettings();
        $this->expenseDate = now()->format('Y-m-d');
    }

    public function loadExpenseCategories()
    {
        $this->expenseCategories = ExpenseCategory::select('expense_category')
            ->distinct()
            ->pluck('expense_category')
            ->toArray();
    }

    public function updatedExpenseCategory()
    {
        // When category changes, load its types
        $this->expenseTypes = ExpenseCategory::where('expense_category', $this->expenseCategory)
            ->pluck('type')
            ->toArray();
        $this->expenseType = ''; // Reset selected type
    }

    public function loadSettings()
    {
        $this->settings = Setting::orderBy('created_at', 'desc')->get();
    }

    public function loadPOSSettings()
    {
        $this->warranty_min_amount = Setting::where('key', 'warranty_min_amount')->value('value') ?? 1000;
    }

    public function loadLabelSettings()
    {
        $keys = [
            'label_width', 'label_height', 'label_padding',
            'label_text_width', 'label_tail_width', 'label_tail_height',
            'label_font_family',
            'label_font_shop', 'label_font_price', 'label_font_barcode',
            'label_qr_size', 'label_barcode_type', 'label_show_shop', 'label_show_barcode_text', 'label_show_qr',
        ];
        foreach ($keys as $key) {
            $val = Setting::where('key', $key)->value('value');
            if ($val !== null) {
                if (in_array($key, ['label_show_shop', 'label_show_barcode_text', 'label_show_qr'])) {
                    $this->$key = (bool) $val;
                } elseif (in_array($key, ['label_font_family', 'label_barcode_type'])) {
                    $this->$key = $val;
                } else {
                    $this->$key = (float) $val;
                }
            }
        }
    }

    public function saveLabelSettings(array $data = [])
    {
        // Accept all values in one call from JS to avoid multiple re-renders
        if (! empty($data)) {
            $this->label_width = $data['width'] ?? $this->label_width;
            $this->label_height = $data['height'] ?? $this->label_height;
            $this->label_padding = $data['padding'] ?? $this->label_padding;
            $this->label_text_width = $data['textWidth'] ?? $this->label_text_width;
            $this->label_tail_width = $data['tailWidth'] ?? $this->label_tail_width;
            $this->label_tail_height = $data['tailHeight'] ?? $this->label_tail_height;
            $allowed = ['Courier New', 'Consolas', 'Lucida Console', 'OCR A Extended', 'Arial Narrow', 'Arial', 'Verdana', 'Roboto Mono'];
            if (isset($data['fontFamily']) && in_array($data['fontFamily'], $allowed)) {
                $this->label_font_family = $data['fontFamily'];
            }
            $allowedTypes = ['datamatrix', 'qrcode_l', 'qrcode_m', 'code128'];
            if (isset($data['barcodeType']) && in_array($data['barcodeType'], $allowedTypes)) {
                $this->label_barcode_type = $data['barcodeType'];
            }
            $this->label_font_shop = $data['fontShop'] ?? $this->label_font_shop;
            $this->label_font_price = $data['fontPrice'] ?? $this->label_font_price;
            $this->label_font_barcode = $data['fontBarcode'] ?? $this->label_font_barcode;
            $this->label_qr_size = $data['qrSize'] ?? $this->label_qr_size;
            $this->label_show_shop = $data['showShop'] ?? $this->label_show_shop;
            $this->label_show_barcode_text = $data['showBarcodeText'] ?? $this->label_show_barcode_text;
            $this->label_show_qr = $data['showQR'] ?? $this->label_show_qr;
        }

        $this->validate([
            'label_width' => 'required|numeric|min:10|max:300',
            'label_height' => 'required|numeric|min:5|max:100',
            'label_padding' => 'required|numeric|min:0|max:10',
            'label_text_width' => 'required|numeric|min:1|max:299',
            'label_tail_width' => 'required|numeric|min:0|max:100',
            'label_tail_height' => 'required|numeric|min:0|max:50',
            'label_font_shop' => 'required|numeric|min:1|max:30',
            'label_font_price' => 'required|numeric|min:1|max:30',
            'label_font_barcode' => 'required|numeric|min:1|max:20',
            'label_qr_size' => 'required|numeric|min:2|max:50',
        ]);

        $settings = [
            'label_width' => $this->label_width,
            'label_height' => $this->label_height,
            'label_padding' => $this->label_padding,
            'label_text_width' => $this->label_text_width,
            'label_tail_width' => $this->label_tail_width,
            'label_tail_height' => $this->label_tail_height,
            'label_font_family' => $this->label_font_family,
            'label_font_shop' => $this->label_font_shop,
            'label_font_price' => $this->label_font_price,
            'label_font_barcode' => $this->label_font_barcode,
            'label_qr_size' => $this->label_qr_size,
            'label_barcode_type' => $this->label_barcode_type,
            'label_show_shop' => $this->label_show_shop ? '1' : '0',
            'label_show_barcode_text' => $this->label_show_barcode_text ? '1' : '0',
            'label_show_qr' => $this->label_show_qr ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'date' => now()]);
        }

        $this->js("Swal.fire('Saved!', 'Print label settings updated successfully.', 'success')");
        $this->loadSettings();
    }

    public function savePOSSettings()
    {
        try {
            $this->validate([
                'warranty_min_amount' => 'required|numeric|min:0',
            ]);

            Setting::updateOrCreate(
                ['key' => 'warranty_min_amount'],
                ['value' => $this->warranty_min_amount, 'date' => now()]
            );

            $this->js("Swal.fire('Success!', 'POS Settings updated successfully.', 'success')");
            $this->loadPOSSettings();
            $this->loadSettings(); // Refresh general list too
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update POS Settings. Please try again.', 'error')");
        }
    }

    public function resetForm()
    {
        $this->reset(['key', 'value', 'editingId', 'isEdit', 'deleteId']);
        $this->resetErrorBag();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEdit = false;
    }

    public function openEditModal($id)
    {
        $setting = Setting::findOrFail($id);
        $this->editingId = $id;
        $this->key = $setting->key;
        $this->value = $setting->value;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function saveConfiguration()
    {
        try {
            $this->validate();

            Setting::create([
                'key' => $this->key,
                'value' => $this->value,
                'date' => now(),
            ]);

            $this->closeModal();
            $this->loadSettings();

            $this->js("Swal.fire('Success!', 'Configuration has been added successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to add configuration. Please try again.', 'error')");
        }
    }

    public function updateConfiguration()
    {
        try {
            $this->validate([
                'key' => 'required|string|max:255|unique:settings,key,'.$this->editingId,
                'value' => 'required|string|max:255',
            ]);

            $setting = Setting::findOrFail($this->editingId);
            $setting->update([
                'key' => $this->key,
                'value' => $this->value,
            ]);

            $this->closeModal();
            $this->loadSettings();

            $this->js("Swal.fire('Success!', 'Configuration has been updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update configuration. Please try again.', 'error')");
        }
    }

    public function confirmDelete($id = null)
    {
        $this->deleteId = $id;
        $this->dispatch('swal:confirm-delete', ['id' => $id]);
    }

    public function deleteConfiguration($id = null)
    {
        try {
            $deleteId = $id ?? $this->deleteId;

            if (! $deleteId) {
                throw new \Exception('No configuration selected for deletion.');
            }

            $setting = Setting::findOrFail($deleteId);
            $setting->delete();
            $this->loadSettings();

            $this->deleteId = null;

            $this->js("Swal.fire('Success!', 'Configuration has been deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete configuration. Please try again.', 'error')");
        }
    }

    // Expense Management Methods
    public function loadExpenses()
    {
        $this->expenses = Expense::orderBy('date', 'desc')->get();
    }

    public function resetExpenseForm()
    {
        $this->reset(['expenseCategory', 'expenseType', 'expenseAmount', 'expenseDate', 'expenseStatus', 'expenseDescription', 'editingExpenseId', 'isEditExpense', 'deleteExpenseId']);
        $this->expenseDate = now()->format('Y-m-d');
        $this->expenseStatus = 'pending';
        $this->resetErrorBag();
    }

    public function openAddExpenseModal()
    {
        $this->resetExpenseForm();
        $this->showExpenseModal = true;
        $this->isEditExpense = false;
    }

    public function openEditExpenseModal($id)
    {
        $expense = Expense::findOrFail($id);
        $this->editingExpenseId = $id;
        $this->expenseCategory = $expense->category;

        // Load types for this category
        $this->expenseTypes = ExpenseCategory::where('expense_category', $this->expenseCategory)
            ->pluck('type')
            ->toArray();

        $this->expenseType = $expense->expense_type;
        $this->expenseAmount = $expense->amount;
        $this->expenseDate = $expense->date->format('Y-m-d');
        $this->expenseStatus = $expense->status;
        $this->expenseDescription = $expense->description;
        $this->isEditExpense = true;
        $this->showExpenseModal = true;
    }

    public function closeExpenseModal()
    {
        $this->showExpenseModal = false;
        $this->resetExpenseForm();
    }

    public function saveExpense()
    {
        try {
            $this->validate([
                'expenseCategory' => 'required|string|max:255',
                'expenseType' => 'required|string|max:255',
                'expenseAmount' => 'required|numeric|min:0',
                'expenseDate' => 'required|date',
                'expenseStatus' => 'required|in:pending,approved,rejected',
                'expenseDescription' => 'nullable|string|max:1000',
            ]);

            Expense::create([
                'category' => $this->expenseCategory,
                'expense_type' => $this->expenseType,
                'amount' => $this->expenseAmount,
                'date' => $this->expenseDate,
                'status' => $this->expenseStatus,
                'description' => $this->expenseDescription,
            ]);

            $this->closeExpenseModal();
            $this->loadExpenses();

            $this->js("Swal.fire('Success!', 'Expense has been added successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to add expense. Please try again.', 'error')");
        }
    }

    public function updateExpense()
    {
        try {
            $this->validate([
                'expenseCategory' => 'required|string|max:255',
                'expenseType' => 'required|string|max:255',
                'expenseAmount' => 'required|numeric|min:0',
                'expenseDate' => 'required|date',
                'expenseStatus' => 'required|in:pending,approved,rejected',
                'expenseDescription' => 'nullable|string|max:1000',
            ]);

            $expense = Expense::findOrFail($this->editingExpenseId);
            $expense->update([
                'category' => $this->expenseCategory,
                'expense_type' => $this->expenseType,
                'amount' => $this->expenseAmount,
                'date' => $this->expenseDate,
                'status' => $this->expenseStatus,
                'description' => $this->expenseDescription,
            ]);

            $this->closeExpenseModal();
            $this->loadExpenses();

            $this->js("Swal.fire('Success!', 'Expense has been updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update expense. Please try again.', 'error')");
        }
    }

    public function confirmDeleteExpense($id)
    {
        $this->deleteExpenseId = $id;
        $this->dispatch('swal:confirm-delete-expense', ['id' => $id]);
    }

    public function deleteExpense($id = null)
    {
        try {
            $deleteId = $id ?? $this->deleteExpenseId;

            if (! $deleteId) {
                throw new \Exception('No expense selected for deletion.');
            }

            $expense = Expense::findOrFail($deleteId);
            $expense->delete();
            $this->loadExpenses();

            $this->deleteExpenseId = null;

            $this->js("Swal.fire('Success!', 'Expense has been deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete expense. Please try again.', 'error')");
        }
    }

    // Expense Category Management Methods
    public function openAddCategoryModal()
    {
        $this->reset(['newExpenseCategory', 'newExpenseType', 'customExpenseCategory']);
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->reset(['newExpenseCategory', 'newExpenseType', 'customExpenseCategory']);
        $this->resetErrorBag();
    }

    public function saveCategoryType()
    {
        try {
            $rules = [
                'newExpenseType' => 'required|string|max:255',
            ];

            // If creating new category
            if ($this->newExpenseCategory === '__new__') {
                $rules['customExpenseCategory'] = 'required|string|max:255';
                $this->validate($rules);
                $categoryName = $this->customExpenseCategory;
            } else {
                $rules['newExpenseCategory'] = 'required|string|max:255';
                $this->validate($rules);
                $categoryName = $this->newExpenseCategory;
            }

            // Check if this combination already exists
            $exists = ExpenseCategory::where('expense_category', $categoryName)
                ->where('type', $this->newExpenseType)
                ->exists();

            if ($exists) {
                $this->js("Swal.fire('Warning!', 'This category and type combination already exists.', 'warning')");

                return;
            }

            // Create new expense category/type
            ExpenseCategory::create([
                'expense_category' => $categoryName,
                'type' => $this->newExpenseType,
            ]);

            $this->closeCategoryModal();
            $this->loadExpenseCategories();

            $this->js("Swal.fire('Success!', 'Expense category/type has been added successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to add category/type. Please try again.', 'error')");
        }
    }

    public function confirmDeleteCategoryType($id)
    {
        $this->deleteCategoryTypeId = $id;
        $this->dispatch('swal:confirm-delete-category-type', ['id' => $id]);
    }

    public function deleteCategoryType($id = null)
    {
        try {
            $deleteId = $id ?? $this->deleteCategoryTypeId;

            if (! $deleteId) {
                throw new \Exception('No category type selected for deletion.');
            }

            $categoryType = ExpenseCategory::findOrFail($deleteId);
            $categoryType->delete();
            $this->loadExpenseCategories();

            $this->deleteCategoryTypeId = null;

            $this->js("Swal.fire('Success!', 'Expense category/type has been deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete category/type. Please try again.', 'error')");
        }
    }

    // Staff Type Permission Management Methods
    public function loadStaffTypePermissions()
    {
        $this->staffTypePermissions = StaffTypePermission::getPermissions($this->selectedStaffType);

        // If no custom permissions are set, load defaults
        if (empty($this->staffTypePermissions)) {
            $this->staffTypePermissions = StaffTypePermission::defaultPermissions()[$this->selectedStaffType] ?? [];
        }
    }

    public function updatedSelectedStaffType()
    {
        $this->loadStaffTypePermissions();
    }

    public function togglePermission($permissionKey)
    {
        if (in_array($permissionKey, $this->staffTypePermissions)) {
            $this->staffTypePermissions = array_diff($this->staffTypePermissions, [$permissionKey]);
        } else {
            $this->staffTypePermissions[] = $permissionKey;
        }
        $this->staffTypePermissions = array_values($this->staffTypePermissions);
    }

    public function saveStaffTypePermissions()
    {
        try {
            StaffTypePermission::syncPermissions($this->selectedStaffType, $this->staffTypePermissions);
            $this->js("Swal.fire('Success!', 'Staff type permissions have been updated successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to save permissions. Please try again.', 'error')");
        }
    }

    public function resetStaffTypePermissions()
    {
        try {
            StaffTypePermission::resetToDefaults($this->selectedStaffType);
            $this->loadStaffTypePermissions();
            $this->js("Swal.fire('Success!', 'Permissions have been reset to defaults.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to reset permissions. Please try again.', 'error')");
        }
    }

    public function getStaffTypesProperty()
    {
        return StaffTypePermission::staffTypes();
    }

    public function getAvailablePermissionsProperty()
    {
        return StaffTypePermission::availablePermissions();
    }

    public function getPermissionCategoriesProperty()
    {
        return StaffTypePermission::permissionCategories();
    }

    // Price Helper Methods
    public function loadPriceHelpers()
    {
        $this->priceHelpers = PriceHelper::orderBy('sort_order', 'asc')->orderBy('item_name', 'asc')->get();
    }

    public function resetPriceHelperForm()
    {
        $this->reset([
            'priceHelperItemName',
            'priceHelperCategory',
            'priceHelperMinPrice',
            'priceHelperMaxPrice',
            'priceHelperNotes',
            'priceHelperSortOrder',
            'priceHelperIsActive',
            'editingPriceHelperId',
            'isEditPriceHelper',
        ]);
        $this->priceHelperSortOrder = 0;
        $this->priceHelperIsActive = true;
        $this->resetErrorBag();
    }

    public function openAddPriceHelperModal()
    {
        $this->resetPriceHelperForm();
        $this->isEditPriceHelper = false;
        $this->showPriceHelperModal = true;
    }

    public function editPriceHelper($id)
    {
        $item = PriceHelper::findOrFail($id);
        $this->editingPriceHelperId = $id;
        $this->priceHelperItemName = $item->item_name;
        $this->priceHelperCategory = $item->category ?? '';
        $this->priceHelperMinPrice = $item->min_price;
        $this->priceHelperMaxPrice = $item->max_price;
        $this->priceHelperNotes = $item->notes ?? '';
        $this->priceHelperSortOrder = $item->sort_order ?? 0;
        $this->priceHelperIsActive = (bool) $item->is_active;
        $this->isEditPriceHelper = true;
        $this->showPriceHelperModal = true;
    }

    public function closePriceHelperModal()
    {
        $this->showPriceHelperModal = false;
        $this->resetPriceHelperForm();
    }

    public function savePriceHelper()
    {
        $this->validate([
            'priceHelperItemName' => 'required|string|max:255',
            'priceHelperCategory' => 'nullable|string|max:100',
            'priceHelperMinPrice' => 'required|numeric|min:0',
            'priceHelperMaxPrice' => 'required|numeric|min:0|gte:priceHelperMinPrice',
            'priceHelperNotes' => 'nullable|string|max:500',
            'priceHelperSortOrder' => 'nullable|integer',
        ], [
            'priceHelperItemName.required' => 'Item name is required.',
            'priceHelperMinPrice.required' => 'Minimum price is required.',
            'priceHelperMaxPrice.required' => 'Maximum price is required.',
            'priceHelperMaxPrice.gte' => 'Maximum price must be greater than or equal to minimum price.',
        ]);

        try {
            $data = [
                'item_name' => trim($this->priceHelperItemName),
                'category' => trim($this->priceHelperCategory) ?: null,
                'min_price' => (float) $this->priceHelperMinPrice,
                'max_price' => (float) $this->priceHelperMaxPrice,
                'notes' => trim($this->priceHelperNotes) ?: null,
                'sort_order' => (int) ($this->priceHelperSortOrder ?? 0),
                'is_active' => (bool) $this->priceHelperIsActive,
            ];

            if ($this->isEditPriceHelper && $this->editingPriceHelperId) {
                $item = PriceHelper::findOrFail($this->editingPriceHelperId);
                $item->update($data);
                $message = 'Price Guide item updated successfully!';
            } else {
                PriceHelper::create($data);
                $message = 'Price Guide item created successfully!';
            }

            $this->loadPriceHelpers();
            $this->closePriceHelperModal();
            $this->js("Swal.fire('Success!', '{$message}', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Failed to save Price Guide item: ".addslashes($e->getMessage())."', 'error')");
        }
    }

    public function togglePriceHelperStatus($id)
    {
        try {
            $item = PriceHelper::findOrFail($id);
            $item->is_active = ! $item->is_active;
            $item->save();
            $this->loadPriceHelpers();
            $status = $item->is_active ? 'activated' : 'deactivated';
            $this->js("Swal.fire('Updated!', 'Price Guide item has been {$status}.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to update status.', 'error')");
        }
    }

    public function deletePriceHelper($id)
    {
        try {
            $item = PriceHelper::findOrFail($id);
            $item->delete();
            $this->loadPriceHelpers();
            $this->js("Swal.fire('Deleted!', 'Price Guide item deleted successfully.', 'success')");
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error!', 'Unable to delete Price Guide item.', 'error')");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SMS Management Methods
    // ──────────────────────────────────────────────────────────────────────────

    public function loadSmsSettings()
    {
        $this->sms_rate = (float) (Setting::where('key', 'sms_rate')->value('value') ?? 0.85);
        $this->sms_low_balance_threshold = (float) (Setting::where('key', 'sms_low_balance_threshold')->value('value') ?? 50.00);
        $this->sms_topup_contact = (string) (Setting::where('key', 'sms_topup_contact')->value('value') ?? '0759037101');
        $this->sms_sender_id = (string) (Setting::where('key', 'sms_sender_id')->value('value') ?? config('sms.sender_id', 'Jaffna Gold'));
        $this->sms_enabled = (bool) (Setting::where('key', 'sms_enabled')->value('value') ?? config('sms.enabled', true));
    }

    public function saveSmsSettings()
    {
        $this->js("Swal.fire('Settings Locked', 'SMS pricing, thresholds, and provider configurations are managed exclusively by service provider (0759037101). Store users cannot modify these settings.', 'warning')");
    }

    public function checkGatewayStatus()
    {
        try {
            $res = SmsService::checkAccountStatus();
            if (($res['success'] ?? false) && isset($res['data'])) {
                $this->sms_gateway_balance = $res['data']['sms_credit_balance'] ?? '0.00';
                $this->sms_gateway_status = $res['data']['status'] ?? 'active';
                $this->sms_active_plan = $res['data']['active_plan'] ?? 'N/A';
                $this->js("Swal.fire('Gateway Connected!', 'Live SMSlenz Balance: Rs. {$this->sms_gateway_balance} (Plan: {$this->sms_active_plan})', 'success')");
            } else {
                $msg = $res['message'] ?? 'Unable to connect to SMS gateway';
                $this->js("Swal.fire('Gateway Warning', '{$msg}', 'warning')");
            }
        } catch (\Exception $e) {
            $this->js("Swal.fire('Error', 'Gateway check failed: ".addslashes($e->getMessage())."', 'error')");
        }
    }

    public function openTopupModal()
    {
        $this->js("Swal.fire('Self Top-Up Disabled', 'Store users cannot self-topup. Please send a top-up request to service provider (0759037101).', 'info')");
    }

    public function closeTopupModal()
    {
        $this->showTopupModal = false;
    }

    public function saveTopup()
    {
        $this->js("Swal.fire('Self Top-Up Disabled', 'Self-service top-up is disabled. Please contact service provider (0759037101) to credit your SMS balance.', 'warning')");
    }

    public function openTestSmsModal()
    {
        $this->testSmsPhone = '';
        $this->testSmsMessage = 'Test SMS from Jaffna Gold ('.config('shop.branch', 'Vavuniya Branch').'). System test OK.';
        $this->showTestSmsModal = true;
    }

    public function closeTestSmsModal()
    {
        $this->showTestSmsModal = false;
    }

    public function requestSmsTopup()
    {
        $contact = SmsService::getTopupContact();
        $balance = SmsService::getBalance();
        $user = auth()->user()?->name ?? 'Admin';
        $smsMsg = 'Top-Up Request: Jaffna Gold (Vavuniya Branch) requested balance recharge (Current Bal: Rs. '.number_format($balance, 2).") by {$user}.";

        try {
            SmsService::sendSms($contact, $smsMsg, false, false);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[Vav] Top-up SMS request failed: '.$e->getMessage());
        }

        $this->js("Swal.fire('Request Sent via SMS!', 'Your SMS top-up request has been dispatched to helpline ({$contact}). Once credited, your balance will update automatically.', 'success')");
    }

    public function sendTestSms($forceDouble = false)
    {
        $this->validate([
            'testSmsPhone' => 'required|string|min:9',
            'testSmsMessage' => 'required|string|min:3|max:500',
        ]);

        $isLow = SmsService::isBalanceLow();
        $isFinished = SmsService::isBalanceFinished();
        $balance = SmsService::getBalance();
        $rate = SmsService::getPerSmsRate();
        $contact = SmsService::getTopupContact();

        // If balance is low or finished and user has not confirmed double charge
        if (($isLow || $isFinished) && ! $forceDouble) {
            $balFmt = number_format($balance, 2);
            $doubleRate = number_format($rate * 2, 2);
            $regRate = number_format($rate, 2);

            $stateLabel = $balance <= 0 ? 'exhausted' : 'low';
            $stateTitle = $balance <= 0 ? 'Finished' : 'Low';

            $this->js(<<<JS
Swal.fire({
    title: '⚠️ SMS Balance {$stateTitle}!',
    html: `
        <div class="text-start">
            <p class="mb-2 fw-semibold text-danger">⚠️ SMS account balance is {$stateLabel}!</p>
            <div class="alert alert-warning p-2 mb-3 small">
                <div><strong>Current Balance:</strong> Rs. {$balFmt}</div>
                <div><strong>Regular Rate:</strong> Rs. {$regRate} per SMS</div>
                <div class="text-danger fw-bold mt-1">⚠️ If you continue, double charge (Rs. {$doubleRate}) will apply.</div>
            </div>
            <p class="mb-0 small text-muted">Click <strong>Top Up Request via SMS</strong> to dispatch an SMS recharge request directly to <strong>{$contact}</strong>, or continue with double charge.</p>
        </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: '<i class="bi bi-send-exclamation me-1"></i> Continue (Double Charge)',
    denyButtonText: '<i class="bi bi-chat-text-fill me-1"></i> Top Up Request via SMS',
    cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
    confirmButtonColor: '#dc3545',
    denyButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
}).then((result) => {
    if (result.isConfirmed) {
        \$wire.sendTestSms(true);
    } else if (result.isDenied) {
        \$wire.requestSmsTopup();
    }
});
JS);

            return;
        }

        $res = SmsService::sendSms($this->testSmsPhone, $this->testSmsMessage, $forceDouble);

        if ($res['success'] ?? false) {
            $this->closeTestSmsModal();
            $chargeText = $forceDouble ? ' (Double Charge applied: Rs. '.number_format($rate * 2, 2).')' : '';
            $this->js("Swal.fire('SMS Dispatched!', 'Message sent to {$this->testSmsPhone} successfully!{$chargeText}', 'success')");
        } else {
            $msg = $res['message'] ?? 'Gateway rejected request';
            $this->js("Swal.fire('Send Failed', '{$msg}', 'error')");
        }
    }

    public function render()
    {
        $smsLogsQuery = SmsLog::query()->with('user')->orderBy('id', 'desc');
        if (! empty($this->smsSearch)) {
            $search = '%'.trim($this->smsSearch).'%';
            $smsLogsQuery->where(function ($q) use ($search) {
                $q->where('recipient', 'like', $search)
                    ->orWhere('message', 'like', $search);
            });
        }
        if (! empty($this->smsStatusFilter)) {
            $smsLogsQuery->where('status', $this->smsStatusFilter);
        }
        if (! empty($this->smsDateFilter)) {
            $smsLogsQuery->whereDate('created_at', $this->smsDateFilter);
        }
        $smsLogs = $smsLogsQuery->paginate(10, ['*'], 'smsPage');

        $smsTopups = SmsTopup::with('user')->orderBy('id', 'desc')->take(10)->get();

        $smsStats = [
            'balance' => SmsService::getBalance(),
            'totalTopup' => SmsService::getTotalTopupAmount(),
            'totalCost' => SmsService::getTotalSmsCost(),
            'sentCount' => SmsService::getTotalSmsSentCount(),
            'rate' => SmsService::getPerSmsRate(),
            'threshold' => SmsService::getLowBalanceThreshold(),
            'isLow' => SmsService::isBalanceLow(),
            'isFinished' => SmsService::isBalanceFinished(),
            'contact' => SmsService::getTopupContact(),
            'remainingEstimate' => floor(max(0, SmsService::getBalance()) / max(0.01, SmsService::getPerSmsRate())),
        ];

        return view('livewire.admin.settings', [
            'smsLogs' => $smsLogs,
            'smsTopups' => $smsTopups,
            'smsStats' => $smsStats,
        ])->layout($this->layout);
    }
}
