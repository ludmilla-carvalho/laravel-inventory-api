<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesReportRequest;
use App\Http\Resources\SalesReportResource;
use App\Http\Traits\ApiResponse;
use App\Services\ReportService;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private ReportService $reportService) {}

    public function sales(SalesReportRequest $request)
    {
        $report = $this->reportService->salesReport(
            $request->start_date,
            $request->end_date,
            $request->sku
        );

        return $this->success(
            SalesReportResource::collection($report),
            'Relatório de vendas gerado',
            Response::HTTP_OK
        );
    }
}
