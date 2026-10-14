{{-- Form tambah / ubah produk. Variabel: $action, $method, $product (boleh null), $categories --}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <label>Foto menu</label>
    <div class="upl">
        <div class="thumb" id="thumb">
            @if ($product && $product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            @else
                📷
            @endif
        </div>
        <div>
            <input type="file" name="image" id="image" accept="image/*">
            @if ($product && $product->image_path)
                <label class="chk"><input type="checkbox" name="remove_image" value="1"> Hapus foto saat ini</label>
            @endif
        </div>
    </div>

    <label for="name">Nama produk</label>
    <input type="text" id="name" name="name" maxlength="60" required value="{{ old('name', $product?->name) }}">

    <div class="row">
        <div>
            <label for="price">Harga (Rp)</label>
            <input type="number" id="price" name="price" min="0" step="500" required value="{{ old('price', $product?->price) }}">
        </div>
        <div>
            <label for="stock">Stok</label>
            <input type="number" id="stock" name="stock" min="0" step="1" required value="{{ old('stock', $product?->stock) }}">
        </div>
    </div>

    <div class="row">
        <div>
            <label for="category">Kategori</label>
            <input type="text" id="category" name="category" list="catList" maxlength="40" placeholder="Kopi, Makanan…" value="{{ old('category', $product?->category) }}">
            <datalist id="catList">
                @foreach ($categories as $c)
                    <option value="{{ $c }}">
                @endforeach
            </datalist>
        </div>
        <div>
            <label for="emoji">Ikon cadangan</label>
            <input type="text" id="emoji" name="emoji" maxlength="8" placeholder="☕" value="{{ old('emoji', $product?->emoji) }}">
        </div>
    </div>

    <div class="row" style="margin-top:16px">
        <button class="btn" type="submit">{{ $product ? 'Simpan perubahan' : 'Simpan' }}</button>
        @if ($product)
            <a class="btn ghost" href="{{ route('kasir.produk.index') }}">Batal</a>
        @endif
    </div>
</form>

@push('scripts')
<script>
    // pratinjau foto sebelum disimpan
    document.getElementById('image').addEventListener('change', function () {
        var f = this.files && this.files[0];
        if (!f) return;
        var img = document.createElement('img');
        img.src = URL.createObjectURL(f);
        var t = document.getElementById('thumb');
        t.innerHTML = '';
        t.appendChild(img);
    });
</script>
@endpush
