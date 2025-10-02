<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\RcvRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class ProcessRcvRequest implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes total timeout

    /**
     * Create a new job instance.
     */
    public function __construct(
        public RcvRequest $rcvRequest
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->rcvRequest->update(['status' => 'processing']);

        $companies = Company::whereIn('id', $this->rcvRequest->company_ids)->get();
        $results = [];

        // Process all companies in parallel using HTTP pool
        $companyMap = [];
        $responses = Http::pool(function ($pool) use ($companies, &$companyMap) {
            $requests = [];
            $index = 0;
            foreach ($companies as $company) {
                $companyMap[$index] = $company;
                $requests[] = $pool
                    ->timeout(60)
                    ->withHeaders([
                        'x-api-key' => '123456789',
                    ])
                    ->post("https://api-rcv.hostingsistemas.cl/api/consulta/{$this->rcvRequest->period}/{$this->rcvRequest->type}", [
                        'rut' => $company->rut,
                        'contrasena' => $company->sii_password,
                    ]);
                $index++;
            }
            return $requests;
        });

        // Process all responses using index
        foreach ($responses as $index => $response) {
            $company = $companyMap[$index];

            try {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
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
                } elseif ($response instanceof \Illuminate\Http\Client\Response) {
                    // Try to parse error response
                    $errorMessage = $response->body();
                    try {
                        $errorData = $response->json();
                        if (isset($errorData['error']['message'])) {
                            $errorMessage = $errorData['error']['message'];
                        } elseif (isset($errorData['message'])) {
                            $errorMessage = $errorData['message'];
                        }
                    } catch (\Exception $e) {
                        // Keep original body if not JSON
                    }

                    $results[] = [
                        'company_id' => $company->id,
                        'company_name' => $company->name,
                        'rut' => $company->rut,
                        'status' => 'error',
                        'error' => $errorMessage,
                    ];
                } else {
                    // Exception occurred
                    $results[] = [
                        'company_id' => $company->id,
                        'company_name' => $company->name,
                        'rut' => $company->rut,
                        'status' => 'error',
                        'error' => $response instanceof \Exception ? $response->getMessage() : 'Error desconocido',
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

            // Update progress incrementally
            $this->rcvRequest->increment('processed_companies');
        }

        // Update all results at once
        $this->rcvRequest->update(['response_data' => $results]);

        $resultCollection = collect($results);
        $errorCount = $resultCollection->where('status', 'error')->count();
        $successCount = $resultCollection->whereIn('status', ['success', 'warning'])->count();
        $totalCount = $resultCollection->count();

        // Determine final status
        if ($errorCount === 0) {
            $status = 'completed';
            $errorMessage = null;
        } elseif ($errorCount === $totalCount) {
            $status = 'failed';
            $errorMessage = 'Todas las solicitudes fallaron';
        } else {
            $status = 'completed';
            $errorMessage = "{$errorCount} de {$totalCount} solicitudes fallaron";
        }

        $this->rcvRequest->update([
            'status' => $status,
            'response_data' => $results,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }
}
