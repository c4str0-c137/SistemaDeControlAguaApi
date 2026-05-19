<?php

namespace App\Services;

use App\Models\Ajuste;
use App\Models\Lectura;
use App\Models\Pago;
use App\Models\Periodo;
use App\Models\Vivienda;
use Illuminate\Support\Facades\Cache;

class BillingService
{
    public function calculateDebt(Vivienda $vivienda, Periodo $periodo, Lectura $lectura)
    {
        $tarifa = $vivienda->tarifa;
        $consumo = (float)$lectura->consumo;
        $costoConsumo = 0;
        $montoFijo = 0;
        $desgloceRangos = [];

        $isAnual = ($vivienda->tipo_lectura === 'anual');

        if ($isAnual) {
            $montoFijoDefault = (float) $this->getAjuste('monto_fijo_anual', 150);
            $montoFijo = ($tarifa && $tarifa->monto_fijo > 0) ? (float)$tarifa->monto_fijo : $montoFijoDefault;
            if ($tarifa) {
                $rangos = $tarifa->rangos->sortBy('desde');
                $remaining = $consumo;
                foreach ($rangos as $rango) {
                    if ($remaining <= 0) break;
                    $hasta = $rango->hasta ?? PHP_INT_MAX;
                    $desde = (float)$rango->desde;
                    $tamañoRango = ($desde == 0) ? (float)$hasta : ($hasta - $desde);
                    $consumoEnRango = min($remaining, $tamañoRango);
                    $costoParcial = $consumoEnRango * (float)$rango->precio_metro;
                    $costoConsumo += $costoParcial;
                    if ($consumoEnRango > 0 && $costoParcial > 0) {
                        $desgloceRangos[] = [
                            'desde' => $desde,
                            'hasta' => $rango->hasta,
                            'precio_metro' => $rango->precio_metro,
                            'metros' => round($consumoEnRango, 2),
                            'subtotal' => round($costoParcial, 2),
                        ];
                    }
                    $remaining = max(0, $remaining - $consumoEnRango);
                }
            }
        } else {
            $montoFijo = $tarifa ? (float)$tarifa->monto_fijo : 0;
            if ($tarifa) {
                $rangos = $tarifa->rangos->sortBy('desde');
                $remaining = $consumo;
                foreach ($rangos as $rango) {
                    if ($remaining <= 0) break;
                    $hasta = $rango->hasta ?? PHP_INT_MAX;
                    $desde = (float)$rango->desde;
                    $tamañoRango = ($desde == 0) ? (float)$hasta : ($hasta - $desde + 1);
                    $consumoEnRango = min($remaining, $tamañoRango);
                    $costoParcial = $consumoEnRango * (float)$rango->precio_metro;
                    $costoConsumo += $costoParcial;
                    if ($consumoEnRango > 0) {
                        $desgloceRangos[] = [
                            'desde' => $desde,
                            'hasta' => $rango->hasta,
                            'precio_metro' => $rango->precio_metro,
                            'metros' => round($consumoEnRango, 2),
                            'subtotal' => round($costoParcial, 2),
                        ];
                    }
                    $remaining = max(0, $remaining - $consumoEnRango);
                }
            }
            $costoConsumo = max($montoFijo, $costoConsumo);
            $montoFijo = 0;
        }

        $multaMonto = (float) $this->getAjuste('multa_mora', 0);
        $mesesMora = (int) $this->getAjuste('meses_mora_deudor', 3);

        if ($multaMonto === 0.0) {
            \Illuminate\Support\Facades\Log::warning('Ajuste multa_mora no configurado');
        }
        if ($mesesMora === 0) {
            \Illuminate\Support\Facades\Log::warning('Ajuste meses_mora_deudor no configurado');
        }

        $periodosConLectura = Periodo::whereHas('lecturas', function ($q) use ($vivienda) {
                $q->where('vivienda_id', $vivienda->id);
            })
            ->where('fecha_inicio', '<=', $periodo->fecha_inicio)
            ->orderBy('fecha_inicio')
            ->pluck('id')
            ->toArray();

        $periodosSinPago = 0;
        foreach ($periodosConLectura as $pid) {
            $tienePago = Pago::where('vivienda_id', $vivienda->id)
                ->where('periodo_id', $pid)
                ->exists();
            if (!$tienePago) {
                $periodosSinPago++;
            }
        }

        $hayMora = $periodosSinPago >= $mesesMora;
        $multa = $hayMora ? $multaMonto : 0;

        $costoAlcantarillado = match($vivienda->alcantarillado) {
            'activo'   => 8,
            'inactivo' => 5,
            default    => 0,
        };

        $montoTotal = $montoFijo + $costoConsumo + $multa + $costoAlcantarillado;

        return [
            'vivienda_id'          => $vivienda->id,
            'codigo'               => $vivienda->codigo,
            'periodo_id'           => $periodo->id,
            'lectura_anterior'     => $lectura->lectura_anterior,
            'lectura_actual'       => $lectura->lectura_actual,
            'consumo'              => $lectura->consumo,
            'monto_fijo'           => $montoFijo,
            'costo_consumo'        => round($costoConsumo, 2),
            'monto_alcantarillado' => $costoAlcantarillado,
            'multa'                => $multa,
            'monto_total'          => round($montoTotal, 2),
            'hay_mora'             => $hayMora,
            'is_anual'             => $isAnual,
            'desgloce_rangos'      => $desgloceRangos,
            'tarifa_nombre'        => $tarifa?->nombre ?? 'Sin tarifa',
        ];
    }

    protected function getAjuste(string $clave, $default = null)
    {
        return Cache::remember('ajuste_'.$clave, now()->addDay(), function () use ($clave, $default) {
            return Ajuste::where('clave', $clave)->value('valor') ?? $default;
        });
    }
}
