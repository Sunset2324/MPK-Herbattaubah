<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BonusCalculator;
use App\Support\CsvDownload;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function __invoke(Request $request, BonusCalculator $calc)
    {
        $month = valid_month($request->query('bulan'));
        $res = $calc->compute($month);

        if ($request->query('export') === 'csv') {
            $rows = array_map(fn ($x) => [
                $x['member_code'], $x['name'], $x['downlines'], $x['personal'], $x['group'],
                $x['bonus'], $x['voucher'], $x['royalty'], $x['total'],
            ], $res['rows']);

            return CsvDownload::make('bonus-mpk-'.$month.'.csv',
                ['kode', 'nama', 'downline_langsung', 'personal', 'group', 'bonus', 'voucher', 'royalty', 'total'], $rows);
        }

        return view('admin.bonus', ['month' => $month, 'rows' => $res['rows'], 't' => $res['totals'], 'r' => $res['rules']]);
    }
}
