<?php

namespace App\Http\Controllers;

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
        $requests = RcvRequest::latest()->paginate(10);

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
            'period' => $validated['period'],
            'type' => $validated['type'],
            'company_ids' => $validated['company_ids'],
            'status' => 'pending',
        ]);

        // Process the request immediately
        $this->processRcvRequest($rcvRequest);

        return redirect()->route('rcv.index')
            ->with('success', 'Solicitud de RCV creada exitosamente.');
    }

    protected function processRcvRequest(RcvRequest $rcvRequest)
    {
        $rcvRequest->update(['status' => 'processing']);

        $companies = Company::whereIn('id', $rcvRequest->company_ids)->get();
        $results = [];

        foreach ($companies as $company) {
            try {
                $response = Http::timeout(30)->post("http://localhost:3000/api/consulta/{$rcvRequest->period}/{$rcvRequest->type}", [
                    'rut' => $company->rut,
                    'contrasena' => $company->sii_password,
                ]);

                if ($response->successful()) {
                    $results[] = [
                        'company_id' => $company->id,
                        'company_name' => $company->name,
                        'rut' => $company->rut,
                        'status' => 'success',
                        'data' => $response->json(),
                    ];
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

    public function show(RcvRequest $rcvRequest)
    {
        return Inertia::render('Rcv/Show', [
            'request' => $rcvRequest
        ]);
    }

    public function export(RcvRequest $rcvRequest, $companyId)
    {
        $result = collect($rcvRequest->response_data)->firstWhere('company_id', (int)$companyId);

        if (!$result || $result['status'] !== 'success') {
            abort(404, 'No se encontraron datos para exportar');
        }

        $data = $result['data'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header information
        $sheet->setCellValue('A1', 'Empresa: ' . $result['company_name']);
        $sheet->setCellValue('A2', 'RUT: ' . $result['rut']);
        $sheet->setCellValue('A3', 'Período: ' . $rcvRequest->period);
        $sheet->setCellValue('A4', 'Tipo: ' . ucfirst($rcvRequest->type));
        $sheet->setCellValue('A5', 'Total Registros: ' . ($data['total_registros'] ?? 0));

        // Headers for the data table
        $row = 7;
        $headers = [
            'Nro', 'Tipo Doc', 'Tipo Compra', 'RUT Proveedor', 'Razón Social',
            'Folio', 'Fecha Docto', 'Fecha Recepción', 'Monto Exento',
            'Monto Neto', 'Monto IVA Recuperable', 'Monto Total'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }

        // Data rows
        if (isset($data['datos']) && is_array($data['datos'])) {
            $row = 8;
            foreach ($data['datos'] as $item) {
                $sheet->setCellValue('A' . $row, $item['nro'] ?? '');
                $sheet->setCellValue('B' . $row, $item['tipo_doc'] ?? '');
                $sheet->setCellValue('C' . $row, $item['tipo_compra'] ?? '');
                $sheet->setCellValue('D' . $row, $item['rut_proveedor'] ?? '');
                $sheet->setCellValue('E' . $row, $item['razon_social'] ?? '');
                $sheet->setCellValue('F' . $row, $item['folio'] ?? '');
                $sheet->setCellValue('G' . $row, $item['fecha_docto'] ?? '');
                $sheet->setCellValue('H' . $row, $item['fecha_recepcion'] ?? '');
                $sheet->setCellValue('I' . $row, $item['monto_exento'] ?? 0);
                $sheet->setCellValue('J' . $row, $item['monto_neto'] ?? 0);
                $sheet->setCellValue('K' . $row, $item['monto_iva_recuperable'] ?? 0);
                $sheet->setCellValue('L' . $row, $item['monto_total'] ?? 0);
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'RCV_' . $result['company_name'] . '_' . $rcvRequest->period . '_' . $rcvRequest->type . '.xlsx';
        $fileName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $fileName);

        $tempFile = tempnam(sys_get_temp_dir(), 'rcv_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
