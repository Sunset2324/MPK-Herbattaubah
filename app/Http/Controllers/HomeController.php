<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $cat = trim((string) $request->query('kategori', ''));
        $search = trim((string) $request->query('cari', ''));

        $products = Product::active()
            ->when($cat !== '', fn ($q) => $q->where('category', $cat))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->ordered()
            ->get();

        $categories = Product::active()
            ->selectRaw('category, COUNT(*) AS n')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        $wa = setting('whatsapp');
        $waText = fn (string $t) => $wa ? wa_link($wa, $t) : '#kontak';

        return view('home', [
            'cat' => $cat,
            'search' => $search,
            'products' => $products,
            'heroProducts' => Product::active()->ordered()->limit(3)->get(),
            'categories' => $categories,
            'totalProducts' => Product::active()->count(),
            'memberCount' => Member::where('status', 'aktif')->count(),
            'siteName' => setting('site_name'),
            'wa' => $wa,
            'waOrder' => $waText('Assalamualaikum MPK Herbattaubah, saya tertarik dan ingin order produk. Mohon infonya.'),
            'waJoin' => $waText('Assalamualaikum MPK Herbattaubah, saya ingin bergabung menjadi mitra MPK. Mohon infonya.'),
            'waTraining' => $waText('Assalamualaikum MPK Herbattaubah, saya ingin info jadwal pelatihan penyembuhan ala sehat islami.'),
        ]);
    }
}
