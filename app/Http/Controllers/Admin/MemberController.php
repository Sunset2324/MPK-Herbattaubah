<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Purchase;
use App\Support\CsvDownload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('cari', ''));
        $status = (string) $request->query('status', '');

        $query = Member::query()
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('name', 'like', "%$search%")
                ->orWhere('member_code', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%")))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($q) => $q->where('status', $status));

        if ($request->query('export') === 'csv') {
            $rows = (clone $query)->with('sponsor')->orderBy('member_code')->get()->map(fn (Member $m) => [
                $m->member_code, $m->name, $m->phone, $m->email, $m->address,
                $m->sponsor?->member_code, $m->status, $m->joined_at?->toDateString(),
            ]);

            return CsvDownload::make('member-mpk-'.now()->format('Ymd').'.csv',
                ['kode', 'nama', 'hp', 'email', 'alamat', 'kode_sponsor', 'status', 'tgl_gabung'], $rows);
        }

        $members = $query->with('sponsor')->withCount('downlines')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.members.index', compact('members', 'search', 'status'));
    }

    public function create()
    {
        return view('admin.members.form', ['member' => new Member(['status' => 'aktif', 'joined_at' => now()])]);
    }

    public function store(Request $request)
    {
        $member = Member::create($this->validated($request));

        return redirect()->route('admin.members.edit', $member)
            ->with('ok', 'Member baru ditambahkan dengan kode '.$member->member_code.'.');
    }

    public function edit(Member $member)
    {
        $member->load('sponsor');

        return view('admin.members.form', [
            'member' => $member,
            'tree' => $this->tree($member->id),
            'directCount' => $member->downlines()->count(),
            'purchases' => $member->purchases()->orderByDesc('purchase_date')->orderByDesc('id')->limit(50)->get(),
            'monthTotal' => (int) $member->purchases()
                ->whereBetween('purchase_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount'),
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $member->update($this->validated($request, $member));

        return redirect()->route('admin.members.edit', $member)->with('ok', 'Data member disimpan.');
    }

    public function destroy(Member $member)
    {
        DB::transaction(function () use ($member) {
            Member::where('sponsor_id', $member->id)->update(['sponsor_id' => $member->sponsor_id]);
            $member->purchases()->delete();
            $member->delete();
        });

        return redirect()->route('admin.members.index')
            ->with('ok', 'Member '.$member->name.' dihapus. Downline-nya dipindah ke sponsor di atasnya.');
    }

    public function storePurchase(Request $request, Member $member)
    {
        $request->merge(['amount' => money_input($request->input('amount'))]);
        $data = $request->validate([
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], ['purchase_date' => 'tanggal', 'amount' => 'nominal']);
        $member->purchases()->create($data);

        return redirect()->to(route('admin.members.edit', $member).'#belanja')
            ->with('ok', 'Belanja '.rupiah($data['amount']).' dicatat.');
    }

    public function destroyPurchase(Member $member, Purchase $purchase)
    {
        abort_unless($purchase->member_id === $member->id, 404);
        $purchase->delete();

        return redirect()->to(route('admin.members.edit', $member).'#belanja')->with('ok', 'Transaksi dihapus.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request, ?Member $member = null): array
    {
        $request->merge([
            'member_code' => strtoupper(trim((string) $request->input('member_code'))) ?: Member::nextCode(),
            'sponsor_code' => strtoupper(trim((string) $request->input('sponsor_code'))),
        ]);

        $data = $request->validate([
            'member_code' => ['required', 'string', 'max:30', Rule::unique('members')->ignore($member?->id)],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'joined_at' => ['nullable', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string'],
            'sponsor_code' => ['nullable', 'string', function ($attr, $value, $fail) use ($member) {
                if ($value === '') {
                    return;
                }
                $sp = Member::where('member_code', $value)->first();
                if (! $sp) {
                    $fail("Kode sponsor $value tidak ditemukan.");
                } elseif ($member && Member::wouldCreateCycle($member->id, $sp->id)) {
                    $fail('Sponsor tidak boleh member itu sendiri atau downline-nya.');
                }
            }],
        ], [], [
            'member_code' => 'kode member', 'name' => 'nama', 'phone' => 'no. HP', 'address' => 'alamat',
            'joined_at' => 'tanggal gabung', 'notes' => 'catatan', 'sponsor_code' => 'kode sponsor',
        ]);

        $data['sponsor_id'] = $data['sponsor_code'] ? Member::where('member_code', $data['sponsor_code'])->value('id') : null;
        unset($data['sponsor_code']);

        return $data;
    }

    /** Pohon downline sampai 4 tingkat, dimuat sekali lalu disusun di memori. */
    private function tree(int $rootId, int $max = 4): array
    {
        $all = Member::query()->get(['id', 'name', 'member_code', 'status', 'sponsor_id'])->groupBy('sponsor_id');
        $build = function (int $id, int $depth) use (&$build, $all, $max): array {
            return ($all[$id] ?? collect())->sortBy('name')->map(fn (Member $k) => [
                'member' => $k,
                'children' => $depth < $max ? $build($k->id, $depth + 1) : [],
                'more' => $depth >= $max ? count($all[$k->id] ?? []) : 0,
            ])->values()->all();
        };

        return $build($rootId, 1);
    }
}
