<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'todaySales' => Sale::whereDate('created_at', today())->sum('total'),
            'monthSales' => Sale::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'productsCount' => Product::count(),
            'lowStockCount' => Product::whereColumn('stock', '<=', 'min_stock')->count(),
            'usersCount' => User::count(),
            'recentSales' => Sale::with(['user', 'customer'])->latest()->take(8)->get(),
            'topProducts' => DB::table('sale_items')
                ->select('product_name', DB::raw('SUM(quantity) as sold'))
                ->groupBy('product_name')
                ->orderByDesc('sold')
                ->take(5)
                ->get(),
        ]);
    }
}
