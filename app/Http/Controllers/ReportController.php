<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cluster;
use App\Models\Material;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    private const PDF_MAX_ROWS = 1500;
    private const PREVIEW_ROWS = 200;

    public function __construct(private ReportService $reports)
    {
    }

    public function index(Request $request)
    {
        $report = null;
        if ($request->has('type')) {
            $report = $this->reports->build($this->params($request), self::PREVIEW_ROWS);
        }

        return view('reports.index', [
            'report' => $report,
            'clusters' => Cluster::orderBy('cluster_name')->get(),
            'categories' => Category::orderBy('id')->get(),
            'materials' => Material::with('category')->orderBy('category_id')->orderBy('material_name')
                ->get()->groupBy(fn ($m) => $m->category->category_name),
            'defaults' => [
                'date' => now()->format('Y-m-d'),
                'week' => now()->format('o-\WW'),
                'month' => now()->format('Y-m'),
                'year' => now()->format('Y'),
                'date_from' => now()->startOfMonth()->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ],
        ]);
    }

    public function printable(Request $request)
    {
        $report = $this->reports->build($this->params($request));

        return view('reports.print', compact('report'));
    }

    public function excel(Request $request)
    {
        if (! class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return redirect()->route('reports.index')->with('error', 'Ekspor Excel belum tersedia: paket maatwebsite/excel belum terpasang.');
        }

        $report = $this->reports->build($this->params($request));

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ReportExport($report), $this->filename('xlsx'));
    }

    public function pdf(Request $request)
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->route('reports.index')->with('error', 'Ekspor PDF belum tersedia: paket barryvdh/laravel-dompdf belum terpasang.');
        }

        $report = $this->reports->build($this->params($request), self::PDF_MAX_ROWS);

        if ($report['truncated']) {
            return redirect()->route('reports.index', $request->query())->with(
                'error',
                'Laporan memiliki lebih dari '.number_format(self::PDF_MAX_ROWS, 0, ',', '.').' baris. Gunakan ekspor Excel atau persempit periode untuk PDF.'
            );
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', compact('report'))
            ->setPaper('a4', 'landscape')
            ->download($this->filename('pdf'));
    }

    private function params(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(array_keys(ReportService::TYPES))],
            'date' => ['required_if:type,daily', 'nullable', 'date_format:Y-m-d'],
            'week' => ['required_if:type,weekly', 'nullable', 'regex:/^\d{4}-W(0[1-9]|[1-4]\d|5[0-3])$/'],
            'month' => ['required_if:type,monthly', 'nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'year' => ['required_if:type,yearly', 'nullable', 'integer', 'between:2000,2100'],
            'date_from' => ['required_if:type,range', 'nullable', 'date'],
            'date_to' => ['required_if:type,range', 'nullable', 'date', 'after_or_equal:date_from'],
            'layout' => ['nullable', Rule::in(array_keys(ReportService::LAYOUTS))],
            'cluster_id' => ['nullable', 'integer', 'exists:clusters,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'material_id' => ['nullable', 'integer', 'exists:materials,id'],
        ], [
            'required_if' => 'Isian periode wajib diisi sesuai jenis laporan.',
            'regex' => 'Format periode tidak valid.',
            'date_to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);
    }

    private function filename(string $ext): string
    {
        return 'laporan-material-masuk-'.now()->format('Ymd-His').'.'.$ext;
    }
}
