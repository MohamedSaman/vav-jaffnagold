<?php

namespace App\Livewire\Staff;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\StaffProduct;
use App\Models\StaffSale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Title("Staff Dashboard")]
#[Layout("components.layouts.staff")]
class StaffDashboard extends Component
{
    public $totalSalesCount = 0;
    public $todaySalesCount = 0;
    public $todaySalesAmount = 0;
    public $pendingSalesCount = 0;
    public $confirmedSalesCount = 0;

    public $totalRevenue = 0;
    public $totalDueAmount = 0;
    public $revenuePercentage = 0;
    public $duePercentage = 0;

    public $totalInventory = 0;
    public $soldInventory = 0;
    public $soldPercentage = 0;

    public $totalCustomers = 0;
    public $customerTypes = [];

    public $fullPaidCount = 0;
    public $fullPaidAmount = 0;
    public $partialPaidCount = 0;

    public $recentSales = [];
    public $salesReport = [];
    public $productInventory = [];
    public $dailySales = [];
    public $customerPaymentStats = [];

    public $availableStockValue = 0;

    public function mount()
    {
        // Check if user has permission to view dashboard
        if (!Auth::user()->hasPermission('menu_dashboard')) {
            return redirect()->route('staff.billing')->with('error', 'You do not have permission to access the dashboard.');
        }

        // Calculate available stock value for this staff
        $userId = Auth::id();

        $this->totalSalesCount = Sale::where('user_id', $userId)->count();
        $this->todaySalesCount = Sale::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $this->todaySalesAmount = Sale::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->sum('total_amount');
        $this->pendingSalesCount = Sale::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();
        $this->confirmedSalesCount = Sale::where('user_id', $userId)
            ->where('status', 'confirm')
            ->count();
        $availableStockValue = StaffProduct::where('staff_id', $userId)
            ->select(DB::raw('SUM((quantity - sold_quantity) * unit_price) as available_value'))
            ->value('available_value');
        $this->availableStockValue = $availableStockValue ?? 0;
        $userId = Auth::id();

        // Get sales statistics for this staff member
        $salesStats = Sale::where('user_id', $userId)
            ->select(
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(due_amount) as total_due'),
                DB::raw('COUNT(*) as sales_count')
            )->first();

        // Calculate total revenue (total_amount - due_amount)
        $totalSales = $salesStats->total_sales ?? 0;
        $this->totalDueAmount = $salesStats->total_due ?? 0;
        $this->totalRevenue = $totalSales - $this->totalDueAmount;

        // Calculate percentages
        if ($totalSales > 0) {
            $this->revenuePercentage = round(($this->totalRevenue / $totalSales) * 100, 1);
            $this->duePercentage = round(($this->totalDueAmount / $totalSales) * 100, 1);
        }

        // Get fully paid invoices data
        $fullPaidData = Sale::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as amount')
            )->first();

        $this->fullPaidCount = $fullPaidData->count ?? 0;
        $this->fullPaidAmount = $fullPaidData->amount ?? 0;

        // Get partially paid invoices data
        $partialPaidData = Sale::where('user_id', $userId)
            ->where('payment_status', 'partial')
            ->select(
                DB::raw('COUNT(*) as count')
            )->first();

        $this->partialPaidCount = $partialPaidData->count ?? 0;

        // Get inventory data for this staff
        $inventoryStats = StaffProduct::where('staff_id', $userId)
            ->select(
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(sold_quantity) as sold_quantity')
            )->first();

        $this->totalInventory = $inventoryStats->total_quantity ?? 0;
        $this->soldInventory = $inventoryStats->sold_quantity ?? 0;

        if ($this->totalInventory > 0) {
            $this->soldPercentage = round(($this->soldInventory / $this->totalInventory) * 100, 1);
        }

        // Fix customer statistics to count unique customers correctly
        $this->totalCustomers = Sale::where('user_id', $userId)
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        // Get customer types breakdown - fixed to count distinct customers
        $customerTypes = Sale::where('user_id', $userId)
            ->select('customer_type', DB::raw('COUNT(DISTINCT customer_id) as count'))
            ->groupBy('customer_type')
            ->get();

        // Format customer types for display
        $this->customerTypes = $customerTypes->mapWithKeys(function ($item) {
            return [$item->customer_type => $item->count];
        })->toArray();

        // Load recent sales
        $this->loadRecentSales($userId);

        // Load sales report list
        $this->loadSalesReport($userId);

        // Load product inventory
        $this->loadProductInventory($userId);

        // Load daily sales data for last 7 days
        $this->loadDailySales($userId);

        // Load customer payment statistics
        $this->loadCustomerPaymentStats($userId);
    }

    protected function loadRecentSales($userId)
    {
        $this->recentSales = Sale::where('sales.user_id', $userId)
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.id',
                'sales.invoice_number',
                'sales.total_amount',
                'sales.payment_status',
                'sales.created_at',
                DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as name'),
                'customers.email',
                'sales.due_amount'
            )
            ->orderBy('sales.created_at', 'desc')
            ->limit(5)
            ->get();
    }

    protected function loadProductInventory($userId)
    {
        $this->productInventory = DB::table('staff_products')
            ->join('product_details', 'staff_products.product_id', '=', 'product_details.id')
            ->leftJoin('brand_lists', 'brand_lists.id', '=', 'product_details.brand_id')
            ->where('staff_products.staff_id', $userId)
            ->select(
                'staff_products.product_id',
                'product_details.name',
                'product_details.model',
                'product_details.code',
                'brand_lists.brand_name as brand_name',
                'product_details.image',
                DB::raw('SUM(staff_products.quantity) as total_quantity'),
                DB::raw('SUM(staff_products.sold_quantity) as sold_quantity'),
                DB::raw('SUM(staff_products.quantity) - SUM(staff_products.sold_quantity) as available_quantity'),
                DB::raw('SUM(staff_products.unit_price * (staff_products.quantity - staff_products.sold_quantity)) as available_value')
            )
            ->groupBy(
                'staff_products.product_id',
                'product_details.name',
                'product_details.model',
                'product_details.code',
                'brand_lists.brand_name',
                'product_details.image'
            )
            ->get();
    }

    protected function loadSalesReport($userId)
    {
        $this->salesReport = Sale::where('sales.user_id', $userId)
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.invoice_number',
                'sales.total_amount',
                'sales.due_amount',
                'sales.payment_status',
                'sales.status',
                'sales.created_at',
                DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as customer_name')
            )
            ->orderBy('sales.created_at', 'desc')
            ->limit(15)
            ->get();
    }

    protected function loadDailySales($userId)
    {
        // Get daily sales for the last 7 days
        $dailySales = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $sales = Sale::where('user_id', $userId)
                ->whereDate('created_at', $date->toDateString())
                ->sum('total_amount');

            $dailySales[] = [
                'date' => $date->format('M d'),
                'total_sales' => (float) $sales
            ];
        }

        $this->dailySales = $dailySales;
    }

    protected function loadCustomerPaymentStats($userId)
    {
        // Get customer-wise payment statistics
        $this->customerPaymentStats = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.user_id', $userId)
            ->select(
                DB::raw('COALESCE(customers.id, 0) as id'),
                DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer") as name'),
                'customers.email',
                'customers.phone',
                'customers.type',
                DB::raw('SUM(sales.total_amount) as total_sales'),
                DB::raw('SUM(sales.total_amount - sales.due_amount) as collected_amount'),
                DB::raw('SUM(sales.due_amount) as due_amount'),
                DB::raw('COUNT(sales.id) as transaction_count')
            )
            ->groupBy(DB::raw('COALESCE(customers.id, 0)'), DB::raw('COALESCE(customers.name, sales.walking_customer_name, "Walk-in Customer")'), 'customers.email', 'customers.phone', 'customers.type')
            ->get();
    }

    public function render()
    {
        return view('livewire.staff.staff-dashboard');
    }
}
