<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vivienda;
use App\Models\Periodo;
use App\Models\Lectura;
use App\Models\Tarifa;
use App\Models\TarifaRango;

class PagoFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcular_deuda_devuelve_valores_correctos()
    {
        // 1. Setup
        $admin = User::factory()->create();
        
        $tarifa = Tarifa::create(['nombre' => 'Residencial', 'monto_fijo' => 50]);
        TarifaRango::create(['tarifa_id' => $tarifa->id, 'desde' => 0, 'hasta' => 15, 'precio_metro' => 2]);
        TarifaRango::create(['tarifa_id' => $tarifa->id, 'desde' => 16, 'hasta' => 30, 'precio_metro' => 5]);

        $vivienda = Vivienda::create([
            'codigo' => 'VIV-001',
            'tarifa_id' => $tarifa->id,
            'alcantarillado' => 'inactivo',
            'tipo_lectura' => 'mensual'
        ]);

        $periodo = Periodo::create([
            'nombre' => 'Enero 2026',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-31',
            'estado' => 'abierto'
        ]);

        Lectura::create([
            'vivienda_id' => $vivienda->id,
            'periodo_id' => $periodo->id,
            'lectura_anterior' => 100,
            'lectura_actual' => 120, // Consumo: 20
            'consumo' => 20
        ]);

        // 2. Ejecutar
        // Los primeros 15 m3 cuestan 2 = 30
        // Los restantes 5 m3 cuestan 5 = 25
        // Costo consumo = 55.
        // Monto fijo = 50. Como 55 > 50, costo_consumo será 55 y monto_fijo 0.
        // Alcantarillado inactivo = 5.
        // Total esperado = 55 + 5 = 60.

        $response = $this->actingAs($admin)->postJson('/api/pagos/calcular', [
            'vivienda_id' => $vivienda->id,
            'periodo_id' => $periodo->id
        ]);

        // 3. Verificar
        $response->assertStatus(200)
                 ->assertJsonPath('consumo', 20)
                 ->assertJsonPath('costo_consumo', 55)
                 ->assertJsonPath('monto_alcantarillado', 5)
                 ->assertJsonPath('monto_total', 60);
    }
}
