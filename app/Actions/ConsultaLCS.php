<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class ConsultaLCS
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.reniec.base_url', 'http://reniec.devsysve.com/api/ws_person');
        $this->apiKey  = config('services.reniec.api_key');
    }

    /**
     * Cliente HTTP centralizado
     */
    protected function client()
    {
        return Http::timeout(15)
            ->retry(3, 1000, function ($exception, $request) {
                // Reintentar solo en errores de conexión, no en 429
                return $exception instanceof ConnectionException;
            })
            ->withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept'    => 'application/json',
            ]);
    }

    /**
     * Validar API Key
     */
    protected function validateApiKey(): bool
    {
        if (empty($this->apiKey)) {
            Log::error("RENIEC API Key no configurada.");
            return false;
        }

        return true;
    }

    /**
     * Consulta por DNI
     */
    public function consultaDni(string $dni): ?array
    {
        if (!preg_match('/^\d{8}$/', $dni)) {
            Log::warning("DNI inválido: {$dni}");
            return null;
        }

        if (!$this->validateApiKey()) {
            return null;
        }

        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/dni", [
                    'dni' => $dni
                ]);

            if ($response->status() === 429) {
                $retryAfter = $response->json('retry_after') ?? 60;
                Log::warning("Rate limit RENIEC DNI {$dni}, esperando {$retryAfter}s");
                sleep($retryAfter);

                // Reintentar una vez después de esperar
                $response = $this->client()->post("{$this->baseUrl}/dni", ['dni' => $dni]);

                if (!$response->successful()) {
                    Log::error("Error HTTP consultando DNI {$dni} tras rate limit", [
                        'status' => $response->status()
                    ]);
                    return null;
                }
            }

            if (!$response->successful()) {
                Log::error("Error HTTP consultando DNI {$dni}", [
                    'status' => $response->status()
                ]);
                return null;
            }

            $json = $response->json();

            if (($json['code'] ?? null) !== 200) {
                Log::warning("Respuesta inválida RENIEC DNI {$dni}", [
                    'response' => $json
                ]);
                return null;
            }

            $person = $json['data']['person'] ?? null;

            if (!$person) {
                return null;
            }

            return $this->mapPersonData($person, $dni);
        } catch (ConnectionException $e) {
            Log::error("Error conexión RENIEC DNI {$dni}", [
                'error' => $e->getMessage()
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error("Error inesperado RENIEC DNI {$dni}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Normalizar datos de persona
     */
    protected function mapPersonData(array $data, ?string $dni = null): array
    {
        // Mapear estado civil a números según tu lógica:
        $civilStatus = null;
        $estado = mb_strtolower($data['est_civil'] ?? '');

        switch ($estado) {
            case 'soltero':
            case 'soltera':
                $civilStatus = 1;
                break;
            case 'casado':
            case 'casada':
                $civilStatus = 2;
                break;
            case 'viudo':
            case 'viuda':
                $civilStatus = 3;
                break;
            case 'divorciado':
            case 'divorciada':
                $civilStatus = 4;
                break;
            default:
                $civilStatus = null;
                break;
        }

        return [
            'firstname'       => $this->format($data['nombres'] ?? ''),
            'lastname_father' => $this->format($data['ap_pat'] ?? ''),
            'lastname_mom'    => $this->format($data['ap_mat'] ?? ''),
            'address'         => $this->format($data['direccion'] ?? ''),
            'birthdate'       => $data['fecha_nac'] ?? null,
            'gender'          => isset($data['sexo']) ? intval($data['sexo']) : null,
            'civil_status'    => $civilStatus,
            'identify'        => $data['dni'] ?? $dni,
            'ubigeo_nac'      => $data['ubigeo_nac'] ?? null,
            'ubigeo_dir'      => $data['ubigeo_dir'] ?? null,
        ];
    }

    /**
     * Buscar por nombre / apellidos
     */
    public function BuscarPorNombre(array $criteria): ?array
    {
        if (!$this->validateApiKey()) {
            return null;
        }

        try {
            $response = $this->client()
                ->post("{$this->baseUrl}/search", $criteria);

            if (!$response->successful()) {
                Log::error("Error HTTP búsqueda RENIEC", [
                    'status' => $response->status(),
                    'criteria' => $criteria
                ]);
                return null;
            }

            $json = $response->json();

            if (($json['code'] ?? null) !== 200) {
                return null;
            }

            return collect($json['data']['persons'] ?? [])
                ->map(fn($person) => $this->mapPersonData($person))
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::error("Error búsqueda RENIEC", [
                'error' => $e->getMessage(),
                'criteria' => $criteria
            ]);
            return null;
        }
    }
    /**
     * Formatear texto consistente
     */
    protected function format(?string $value): ?string
    {
        return $value
            ? ucwords(mb_strtolower(trim($value)))
            : null;
    }
}
