<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SummaryController extends Controller
{
    public function __invoke(): View
    {
        $done = Order::where('status', 'selesai');

        $stats = [
            'revenue'  => (clone $done)->sum('total'),
            'done'     => (clone $done)->count(),
            'active'   => Order::whereIn('status', ['baru', 'diproses'])->count(),
            'soldout'  => Product::where('stock', '<=', 0)->count(),
        ];

        $top = OrderItem::select('product_name', DB::raw('SUM(qty) as sold'))
            ->whereHas('order', fn ($q) => $q->where('status', 'selesai'))
            ->groupBy('product_name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        return view('cashier.summary', compact('stats', 'top'));
    }
}
