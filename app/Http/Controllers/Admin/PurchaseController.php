<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $month = valid_month($request->query('bulan'));
        $start = Carbon::parse($month.'-01');
        $search = trim((string) $request->query('cari', ''));

        $query = Purchase::query()
            ->whereBetween('purchase_date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->when($search !== '', fn ($q) => $q->whereHas('member', fn ($m) => $m
                ->where('name', 'like', "%$search%")->orWhere('member_code', 'like', "%$search%")));

        return view('admin.purchases.index', [
            'month' => $month,
            'search' => $search,
            'sum' => (int) (clone $query)->sum('amount'),
            'rows' => $query->with('member')->orderByDesc('purchase_date')->orderByDesc('id')->paginate(50)->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['amount' => money_input($request->input('amount'))]);
        $data = $request->validate([
            'member_code' => ['required', 'string'],
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], ['member_code' => 'kode member', 'purchase_date' => 'tanggal', 'amount' => 'nominal']);

        $member = Member::where('member_code', strtoupper(trim($data['member_code'])))->first();
        if (! $member) {
            return back()->withInput()->withErrors(['member_code' => 'Kode member tidak ditemukan.']);
        }
        $member->purchases()->create($data);

        return redirect()->route('admin.purchases.index', ['bulan' => substr($data['purchase_date'], 0, 7)])
            ->with('ok', 'Belanja '.$member->name.' sebesar '.rupiah($data['amount']).' dicatat.');
    }

    public function destroy(Purchase $purchase)
    {
        $month = $purchase->purchase_date->format('Y-m');
        $purchase->delete();

        return redirect()->route('admin.purchases.index', ['bulan' => $month])->with('ok', 'Transaksi dihapus.');
    }
}
