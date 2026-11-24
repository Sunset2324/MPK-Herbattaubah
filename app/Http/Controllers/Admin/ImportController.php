<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CsvImporter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ImportController extends Controller
{
    public function show()
    {
        return view('admin.import');
    }

    public function store(Request $request, CsvImporter $importer)
    {
        $request->validate([
            'type' => ['required', Rule::in(['members', 'purchases'])],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], ['file.required' => 'Pilih file CSV terlebih dulu.']);

        try {
            $rows = CsvImporter::read($request->file('file')->getRealPath());
            $report = $request->input('type') === 'members'
                ? $importer->importMembers($rows)
                : $importer->importPurchases($rows);
        } catch (Throwable $e) {
            $report = ['Gagal: '.$e->getMessage()];
        }

        return redirect()->route('admin.import')->with('report', $report);
    }
}
