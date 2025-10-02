<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\RcvRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RcvController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('user_id', $request->user()->id)->get();
        $requests = RcvRequest::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return Inertia::render('Rcv/Index', [
            'companies' => $companies,
            'requests' => $requests
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string',
            'type' => 'required|in:compra,venta',
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
        ]);

        $rcvRequest = RcvRequest::create([
            'user_id' => $request->user()->id,
            'period' => $validated['period'],
            'type' => $validated['type'],
            'company_ids' => $validated['company_ids'],
            'status' => 'pending',
            'total_companies' => count($validated['company_ids']),
            'processed_companies' => 0,
        ]);

        ActivityLog::log(
            'create',
            'RcvRequest',
            $rcvRequest->id,
            "Solicitud de RCV creada para período {$rcvRequest->period} tipo {$rcvRequest->type}"
        );

        // Dispatch job to process in background
        \App\Jobs\ProcessRcvRequest::dispatch($rcvRequest);

        return redirect()->route('rcv.index')
            ->with('success', 'Solicitud de RCV creada. Se está procesando en segundo plano.');
    }

    protected function processRcvRequest(RcvRequest $rcvRequest)
    {
        $rcvRequest->update(['status' => 'processing']);

        $companies = Company::whereIn('id', $rcvRequest->company_ids)->get();
        $results = [];

        foreach ($companies as $company) {
            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'x-api-key' => '123456789',
                    ])
                    ->post("https://api-rcv.hostingsistemas.cl/api/consulta/{$rcvRequest->period}/{$rcvRequest->type}", [
                        'rut' => $company->rut,
                        'contrasena' => $company->sii_password,
                    ]);

                if ($response->successful()) {
                    $responseData = $response->json();

                    // Check if there's no data available
                    if (isset($responseData['total_registros']) && $responseData['total_registros'] == 0) {
                        $results[] = [
                            'company_id' => $company->id,
                            'company_name' => $company->name,
                            'rut' => $company->rut,
                            'status' => 'warning',
                            'warning' => $responseData['message'] ?? 'No se encontraron datos para el período consultado',
                        ];
                    } else {
                        $results[] = [
                            'company_id' => $company->id,
                            'company_name' => $company->name,
                            'rut' => $company->rut,
                            'status' => 'success',
                            'data' => $responseData,
                        ];
                    }
                } else {
                    $results[] = [
                        'company_id' => $company->id,
                        'company_name' => $company->name,
                        'rut' => $company->rut,
                        'status' => 'error',
                        'error' => $response->body(),
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'rut' => $company->rut,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $hasErrors = collect($results)->contains('status', 'error');

        $rcvRequest->update([
            'status' => $hasErrors ? 'failed' : 'completed',
            'response_data' => $results,
            'error_message' => $hasErrors ? 'Algunas solicitudes fallaron' : null,
            'completed_at' => now(),
        ]);
    }

    public function show(Request $request, RcvRequest $rcvRequest)
    {
        // Verify that the user owns this request
        if ($rcvRequest->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        return Inertia::render('Rcv/Show', [
            'request' => $rcvRequest
        ]);
    }

    public function export(Request $request, RcvRequest $rcvRequest, $companyId)
    {
        // Verify that the user owns this request
        if ($rcvRequest->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para exportar esta solicitud.');
        }

        $result = collect($rcvRequest->response_data)->firstWhere('company_id', (int)$companyId);

        if (!$result || $result['status'] !== 'success') {
            abort(404, 'No se encontraron datos para exportar');
        }

        ActivityLog::log(
            'export',
            'RcvRequest',
            $rcvRequest->id,
            "Exportó RCV de {$result['company_name']} para período {$rcvRequest->period}"
        );

        $data = $result['data'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header information
        $sheet->setCellValue('A1', 'Empresa: ' . $result['company_name']);
        $sheet->setCellValue('A2', 'RUT: ' . $result['rut']);
        $sheet->setCellValue('A3', 'Período: ' . $rcvRequest->period);
        $sheet->setCellValue('A4', 'Tipo: ' . ucfirst($rcvRequest->type));
        $sheet->setCellValue('A5', 'Total Registros: ' . ($data['total_registros'] ?? 0));

        // Headers for the data table - dynamically from API response
        $row = 7;
        $headers = [];
        $fieldKeys = [];

        // Get all field keys from the first record
        if (isset($data['datos']) && is_array($data['datos']) && count($data['datos']) > 0) {
            $firstRecord = $data['datos'][0];
            foreach ($firstRecord as $key => $value) {
                // Skip __parsed_extra if present
                if ($key === '__parsed_extra') {
                    continue;
                }
                $fieldKeys[] = $key;
                // Convert snake_case to Title Case for headers
                $headers[] = ucwords(str_replace('_', ' ', $key));
            }
        }

        // Write headers
        $colIndex = 0;
        foreach ($headers as $header) {
            $colLetter = $this->getColumnLetter($colIndex);
            $sheet->setCellValue($colLetter . $row, $header);
            $sheet->getStyle($colLetter . $row)->getFont()->setBold(true);
            $colIndex++;
        }

        // Data rows
        if (isset($data['datos']) && is_array($data['datos'])) {
            $row = 8;
            foreach ($data['datos'] as $item) {
                $colIndex = 0;
                foreach ($fieldKeys as $key) {
                    $colLetter = $this->getColumnLetter($colIndex);
                    $sheet->setCellValue($colLetter . $row, $item[$key] ?? '');
                    $colIndex++;
                }
                $row++;
            }
        }

        // Auto-size all columns
        for ($i = 0; $i < count($fieldKeys); $i++) {
            $colLetter = $this->getColumnLetter($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'RCV_' . $result['company_name'] . '_' . $rcvRequest->period . '_' . $rcvRequest->type . '.xlsx';
        $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $fileName);

        $tempFile = tempnam(sys_get_temp_dir(), 'rcv_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Convert column index to Excel column letter (0 = A, 1 = B, ..., 26 = AA, etc.)
     */
    protected function getColumnLetter($index)
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }

    public function destroy(Request $request, RcvRequest $rcvRequest)
    {
        // Verify that the user owns this request
        if ($rcvRequest->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para eliminar esta solicitud.');
        }

        ActivityLog::log(
            'delete',
            'RcvRequest',
            $rcvRequest->id,
            "Eliminó solicitud RCV del período {$rcvRequest->period} tipo {$rcvRequest->type}"
        );

        $rcvRequest->delete();

        return redirect()->route('rcv.index')
            ->with('success', 'Solicitud eliminada exitosamente.');
    }
}
