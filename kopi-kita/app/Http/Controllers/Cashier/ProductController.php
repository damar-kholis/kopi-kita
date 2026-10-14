<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    private function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:60'],
            'category' => ['nullable', 'string', 'max:40'],
            'price'    => ['required', 'integer', 'min:0'],
            'stock'    => ['required', 'integer', 'min:0'],
            'emoji'    => ['nullable', 'string', 'max:8'],
            'image'    => ['nullable', 'image', 'max:2048'],   // maks 2 MB
        ];
    }

    public function index(): View
    {
        $products = Product::orderBy('category')->orderBy('name')->get();
        $categories = $products->pluck('category')->unique()->values();

        return view('cashier.products.index', compact('products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $product = [
            'name'     => $data['name'],
            'category' => $data['category'] ?: 'Lainnya',
            'price'    => $data['price'],
            'stock'    => $data['stock'],
            'emoji'    => $data['emoji'] ?: '☕',
        ];

        if ($request->hasFile('image')) {
            $product['image_path'] = $request->file('image')->store('products', 'uploads');
        }

        Product::create($product);

        return redirect()->route('kasir.produk.index')->with('success', 'Produk ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = Product::query()->pluck('category')->unique()->values();

        return view('cashier.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $attrs = [
            'name'     => $data['name'],
            'category' => $data['category'] ?: 'Lainnya',
            'price'    => $data['price'],
            'stock'    => $data['stock'],
            'emoji'    => $data['emoji'] ?: '☕',
        ];

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('uploads')->delete($product->image_path);
            }
            $attrs['image_path'] = $request->file('image')->store('products', 'uploads');
        } elseif ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('uploads')->delete($product->image_path);
            $attrs['image_path'] = null;
        }

        $product->update($attrs);

        return redirect()->route('kasir.produk.index')->with('success', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('uploads')->delete($product->image_path);
        }
        $product->delete();   // riwayat pesanan tetap aman (order_items.product_id → NULL)

        return redirect()->route('kasir.produk.index')->with('success', 'Produk dihapus.');
    }
}
