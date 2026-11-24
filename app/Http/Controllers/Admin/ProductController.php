<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.products.index', ['products' => Product::ordered()->get()]);
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product(['category' => 'Herbal', 'is_active' => true]), 'cats' => $this->cats()]);
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validated($request));

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Produk disimpan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'cats' => $this->cats()]);
    }

    public function update(Request $request, Product $product)
    {
        $old = $product->image;
        $product->update($this->validated($request, $product));
        if ($old !== $product->image) {
            $this->deleteLocalImage($old);
        }

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Produk disimpan.');
    }

    public function destroy(Product $product)
    {
        $this->deleteLocalImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('ok', 'Produk '.$product->name.' dihapus.');
    }

    private function cats(): array
    {
        return Product::query()->distinct()->orderBy('category')->pluck('category')->all();
    }

    /** @return array<string,mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        $request->merge([
            'price_member' => money_input($request->input('price_member')),
            'price_retail' => money_input($request->input('price_retail')),
            'price_outside' => money_input($request->input('price_outside')),
        ]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price_member' => ['nullable', 'integer', 'min:0'],
            'price_retail' => ['nullable', 'integer', 'min:0'],
            'price_outside' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'image.max' => 'Ukuran gambar maksimal 3 MB.',
            'image.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
        ], ['name' => 'nama produk']);

        $data['category'] = $data['category'] ?: 'Herbal';
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = Product::uniqueSlug($data['name'], $product?->id);

        unset($data['image']);
        if ($file = $request->file('image')) {
            // Disimpan langsung di public/uploads agar jalan di hosting tanpa symlink storage.
            $name = now()->format('Ymd').'-'.Str::lower(Str::random(12)).'.'.$file->extension();
            $file->move(public_path('uploads'), $name);
            $data['image'] = 'uploads/'.$name;
        }

        return $data;
    }

    private function deleteLocalImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
