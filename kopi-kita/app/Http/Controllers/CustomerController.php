<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class CustomerController extends Controller
{
    /** Halaman menu + keranjang. */
    public function menu(): View
    {
        $products = Product::orderBy('category')->orderBy('name')->get();
        $categories = $products->pluck('category')->unique()->values();

        return view('customer.menu', compact('products', 'categories'));
    }

    /** Kirim pesanan ke kasir. Harga & stok SELALU dihitung ulang di server. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name'        => ['required', 'string', 'max:40'],
            'note'                 => ['nullable', 'string', 'max:120'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'          => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'items.required' => 'Pilih minimal satu produk.',
            'items.min'      => 'Pilih minimal satu produk.',
        ]);

        // gabungkan bila produk yang sama muncul dua kali
        $qtys = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['product_id'];
            $qtys[$id] = ($qtys[$id] ?? 0) + (int) $item['qty'];
        }

        try {
            $order = DB::transaction(function () use ($data, $qtys, $request) {
                // kunci baris produk supaya dua pelanggan tidak bisa mengambil stok yang sama
                $products = Product::whereIn('id', array_keys($qtys))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $problems = [];
                foreach ($qtys as $id => $qty) {
                    $product = $products[$id];
                    if ($product->stock < $qty) {
                        $problems[] = $product->name . ' (sisa ' . $product->stock . ')';
                    }
                }
                if ($problems) {
                    throw new RuntimeException('Stok tidak cukup: ' . implode(', ', $problems));
                }

                $order = Order::create([
                    'code'          => Order::generateCode(),
                    'user_id'       => $request->user()->id,
                    'customer_name' => $data['customer_name'],
                    'note'          => $data['note'] ?? null,
                    'total'         => 0,
                    'status'        => 'baru',
                ]);

                $total = 0;
                foreach ($qtys as $id => $qty) {
                    $product = $products[$id];
                    $order->items()->create([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'price'        => $product->price,
                        'qty'          => $qty,
                    ]);
                    $product->decrement('stock', $qty);
                    $total += $product->price * $qty;
                }

                $order->update(['total' => $total]);

                return $order;
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()->route('orders.mine')
            ->with('success', "Pesanan #{$order->code} terkirim ke kasir. Total bayar " . Money::rp($order->total))
            ->with('clear_cart', true);
    }

    /** Riwayat & status pesanan milik pelanggan yang login. */
    public function mine(Request $request): View
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('customer.orders', compact('orders'));
    }
}
