<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'aktif');

        $query = Order::with('items');

        if ($filter === 'selesai') {
            $query->where('status', 'selesai')->latest();
        } elseif ($filter === 'dibatalkan') {
            $query->where('status', 'dibatalkan')->latest();
        } elseif ($filter === 'semua') {
            $query->latest();
        } else {
            // "aktif": pesanan terlama tampil dulu (antrian)
            $filter = 'aktif';
            $query->whereIn('status', ['baru', 'diproses'])->oldest();
        }

        $orders = $query->get();
        $newCount = Order::where('status', 'baru')->count();

        return view('cashier.orders', compact('orders', 'filter', 'newCount'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUS_LABELS))],
        ]);
        $new = $request->input('status');

        try {
            DB::transaction(function () use ($order, $new) {
                $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);
                $old = $order->status;

                if ($old === $new) {
                    return;
                }

                if ($old === 'dibatalkan') {
                    $order->deductStock();      // dibuka lagi → stok dipotong lagi
                } elseif ($new === 'dibatalkan' && $order->isActive()) {
                    $order->restoreStock();     // dibatalkan → stok dikembalikan
                }

                $order->update(['status' => $new]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back();
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            if ($order->isActive()) {
                $order->restoreStock();
            }
            $order->delete();
        });

        return back()->with('success', 'Pesanan dihapus.');
    }
}
