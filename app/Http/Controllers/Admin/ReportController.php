<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Living\Services\ReportService;
use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $type = $request->string('type')->toString() ?: 'occupancy';
        $filters = $request->only(['from', 'to', 'status', 'days']);

        $report = $this->reportService->generate($type, $filters);

        return Inertia::render('Dashboard/Admin/Living/Reports/Index', [
            'types' => ReportService::TYPES,
            'type' => $type,
            'filters' => $filters,
            'headings' => $report['headings'],
            'rows' => $report['rows'],
        ]);
    }

    public function export(Request $request): HttpResponse|BinaryFileResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(ReportService::TYPES))],
            'format' => ['required', 'in:pdf,excel,csv'],
        ]);

        $filters = $request->only(['from', 'to', 'status', 'days']);
        $report = $this->reportService->generate($validated['type'], $filters);
        $title = ReportService::TYPES[$validated['type']];
        $filename = 'laporan-'.$validated['type'].'-'.now()->format('Ymd_His');

        return match ($validated['format']) {
            'pdf' => Pdf::loadView('pdf.report', ['title' => $title, 'headings' => $report['headings'], 'rows' => $report['rows']])
                ->download("{$filename}.pdf"),
            'excel' => Excel::download(new ReportExport($report['headings'], $report['rows']), "{$filename}.xlsx"),
            'csv' => Excel::download(new ReportExport($report['headings'], $report['rows']), "{$filename}.csv", ExcelFormat::CSV),
        };
    }
}
