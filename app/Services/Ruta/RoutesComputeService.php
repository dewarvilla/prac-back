<?php

namespace App\Services\Ruta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoutesComputeService
{
    public function computeMetrics(array $payload): ?object
    {
        $key = config('services.google_routes.key');

        if (!$key) {
            return (object) [
                'distance_m' => null,
                'duration_s' => null,
                'polyline'   => null,
                'warning'    => 'GOOGLE_ROUTES_API_KEY no configurada',
            ];
        }

        $origin = $payload['origin'] ?? null;
        $dest   = $payload['dest'] ?? null;
        $mode   = $payload['mode'] ?? 'DRIVE';

        if (!$origin || !$dest) return null;

        $reqPayload = [
            'origin' => [
                'location' => [
                    'latLng' => [
                        'latitude'  => (float) ($origin['lat'] ?? 0),
                        'longitude' => (float) ($origin['lng'] ?? 0),
                    ]
                ]
            ],
            'destination' => [
                'location' => [
                    'latLng' => [
                        'latitude'  => (float) ($dest['lat'] ?? 0),
                        'longitude' => (float) ($dest['lng'] ?? 0),
                    ]
                ]
            ],
            'travelMode'               => $mode,
            'computeAlternativeRoutes' => false,
            'routingPreference'        => config('services.google_routes.traffic_aware') ? 'TRAFFIC_AWARE' : 'TRAFFIC_UNAWARE',
            'polylineEncoding'         => 'ENCODED_POLYLINE',
            'units'                    => 'METRIC',
        ];

        try {
            $res = Http::withHeaders([
                    'X-Goog-Api-Key'   => $key,
                    'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration,routes.polyline.encodedPolyline',
                ])
                ->timeout(15)
                ->post('https://routes.googleapis.com/directions/v2:computeRoutes', $reqPayload);

            if ($res->failed()) {
                Log::warning('Routes API failed', ['status'=>$res->status(),'body'=>$res->json()]);
                return (object) [
                    'distance_m' => null,
                    'duration_s' => null,
                    'polyline'   => null,
                    'error'      => $res->json(),
                ];
            }

            $route    = $res->json('routes.0');
            $distance = (int) ($route['distanceMeters'] ?? 0);

            // duration viene tipo "123s" o similar -> extraer dígitos
            $duration = isset($route['duration'])
                ? (int) preg_replace('/\D/', '', (string) $route['duration'])
                : null;

            $polyline = $route['polyline']['encodedPolyline'] ?? null;

            return (object) [
                'distance_m' => $distance ?: null,
                'duration_s' => $duration,
                'polyline'   => $polyline,
            ];
        } catch (\Throwable $e) {
            Log::error('Routes API exception', ['msg' => $e->getMessage()]);
            return (object) [
                'distance_m' => null,
                'duration_s' => null,
                'polyline'   => null,
                'exception'  => $e->getMessage(),
            ];
        }
    }
}
