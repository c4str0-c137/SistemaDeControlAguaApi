<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use App\Models\Role;
use App\Models\Tarifa;
use App\Models\TarifaRango;
use App\Models\User;
use App\Models\Vivienda;
use App\Models\Zone;
use App\Models\Periodo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportModificadoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $lectorRole = Role::firstOrCreate(['name' => 'Lector']);
        $socioRole = Role::firstOrCreate(['name' => 'Socio']);

        // 1.5 Métodos de Pago
        \App\Models\PaymentMethod::firstOrCreate(['name' => 'Efectivo'], ['description' => 'Pago en efectivo']);
        \App\Models\PaymentMethod::firstOrCreate(['name' => 'Transferencia'], ['description' => 'Pago por transferencia bancaria']);

        // 2. Administrador Principal
        User::updateOrCreate(
            ['email' => 'admin@agua.com'],
            ['name' => 'ADMINISTRADOR', 'password' => Hash::make('password'), 'role_id' => $adminRole->id]
        );

        // 2.5 Lector
        User::updateOrCreate(
            ['email' => 'lector@agua.com'],
            ['name' => 'Lector de Agua', 'password' => Hash::make('password'), 'role_id' => $lectorRole->id]
        );

        // 3. Zonas
        $zonaTR = Zone::updateOrCreate(['name' => 'Tojlu Rancho'], ['description' => 'Sector Tojlu Rancho']);
        $zonaVR = Zone::updateOrCreate(['name' => 'Villa Rosario'], ['description' => 'Sector Villa Rosario']);
        $zonaSI = Zone::updateOrCreate(['name' => 'San Isidro'], ['description' => 'Sector San Isidro']);

        // 4. Tarifa General
        $tarifa = Tarifa::updateOrCreate(['nombre' => 'Tarifa General'], ['monto_fijo' => 8.00]);
        $rangos = [['desde' => 0, 'hasta' => 20, 'precio_metro' => 1.50]];
        $precio = 1.50;
        for ($i = 21; $i <= 500; $i += 10) {
            $precio += 1.50;
            $rangos[] = ['desde' => $i, 'hasta' => $i + 9, 'precio_metro' => $precio];
        }
        $rangos[count($rangos)-1]['hasta'] = null;
        foreach ($rangos as $rango) {
            TarifaRango::updateOrCreate(['tarifa_id' => $tarifa->id, 'desde' => $rango['desde']], $rango);
        }

        // 5. Ajustes
        Ajuste::updateOrCreate(['clave' => 'multa_mora'], ['valor' => '5.00', 'descripcion' => 'Monto de multa por mora']);
        Ajuste::updateOrCreate(['clave' => 'meses_mora_deudor'], ['valor' => '3', 'descripcion' => 'Meses para ser considerado deudor']);
        Ajuste::updateOrCreate(['clave' => 'dia_vencimiento'], ['valor' => '15', 'descripcion' => 'Día del mes de vencimiento']);
        Ajuste::updateOrCreate(['clave' => 'monto_fijo_anual'], ['valor' => '150.00', 'descripcion' => 'Monto fijo pago anual (hasta 60m3/año)']);
        Ajuste::updateOrCreate(['clave' => 'monto_conexion'], ['valor' => '1500.00', 'descripcion' => 'Monto predeterminado por nueva conexión']);

        // ======================== SOCIOS POR ZONAS ========================

        $nombresTR = [
            "ADELAIDA CALUSTRO",
        "ALICIA CAMARA",
        "ANDREA REA",
        "ANDREA SOTO",
        "ANGELINA BARRIENTOS",
        "APOLONIA VARGAS",
        "BRYAN PANIAGUA",
        "CASIANO CALUSTRO",
        "CASIANO PANIAGUA",
        "CELIA JORDAN",
        "CLAUDINA CALUSTRO",
        "CLAUDIO VARGAS",
        "CONCEPCION CALUSTRO",
        "CONSTANTINO BARRIENTOS",
        "CRISTINA JORDAN",
        "CRISTOBAL JALDIN",
        "EDELMIRA FERRUFINO",
        "EDILBERTO TAPIA",
        "EDILVERTO TAPIA",
        "EDMUNDO TAPIA",
        "EDWIN CASTRO",
        "EFRAIN TAPIA",
        "EMETERIO GUTIERREZ",
        "EMETERIO ZAPATA",
        "EMILIA BARRIENTOS",
        "ESTEBAN BARRIENTOS",
        "EUSEBIO VILLCA",
        "EVARISTO PLATA",
        "FACUNDO CASTRO",
        "FELIPE VELIZ",
        "FOTUNATO MONTANO",
        "FRANCISCA MONTANO",
        "FRANCISCO SAGARDIA",
        "GENARO CASTRO",
        "GERARDO JORDAN",
        "GIOVANA ALVAREZ",
        "GUMERCINDO ALVAREZ",
        "HILARIA COLQUE",
        "HORTENCIA CLAROS",
        "ISABEL JALDIN",
        "ISABEL VASQUEZ",
        "JOSE BARRIENTOS",
        "JULIA BARRIENTOS",
        "JULIO CAMACHO",
        "LEONORA VARGAS",
        "LILIANA SAUSA VEIZAGA",
        "LIMBER CASTRO",
        "LUCIA CALUSTRO",
        "LUCIA DE SAGARDIA",
        "LUIS TAPIA",
        "LUISA FERRUFINO",
        "MARCO MERINO",
        "MARIA DE VARGAS",
        "MARIA REA",
        "MARTHA ROJAS",
        "MELICIA LEDEZMA",
        "MIGUELINA VARGAS",
        "MIRTHA TAPIA",
        "MODESTA CALUSTRO",
        "MOISE TAPIA",
        "NATIVIDAD QUIROZ",
        "NIVARDO CALUSTRO",
        "OLGA TAPIA",
        "ORLANDO JORDAN",
        "PANFILO CHOQUE",
        "PASTOR MONTANO",
        "PRIMITIVA CASTRO",
        "RENE SAGARDIA",
        "ROBERT CASTRO",
        "ROMULO FUENTES",
        "ROSA TAPIA",
        "ROSMERY MONTANO",
        "ROXANA CRUZ",
        "RUBEN MONTANO",
        "SABINA CALUSTRO",
        "SABINA CASTRO",
        "SOFIA QUIROZ",
        "SONIA CASTRO",
        "VALENTIN CLAURE",
        "VALERIO RAMIREZ",
        "VILMA MONTANO",
        "WILDER CRUZ"
        ];

        $nombresVR = [
            "BENEDICTA FERRUFINO",
        "CONSTANTINO ZURITA",
        "CRISTINA FERREL",
        "FAQUINA VILLARROEL",
        "FELICIDAD SAGARDIA",
        "GERARDO ZAPATA",
        "INDALICIO CASTRO",
        "INES ORELLANA",
        "ISMAEL JALDIN",
        "JAIME JALDIN",
        "KARMINIA ZAPATA",
        "LEONARDA CALUSTRO",
        "MACARIO CASTRO",
        "MARCIAL FERRUFINO",
        "MARTA GIMENEZ",
        "PEDRO VILLARROEL",
        "PEPE ZURITA",
        "ROGELIO CASTRO",
        "ROLANDO TERCEROS",
        "RUFINO VERDUGUEZ",
        "SANTIAGO REA",
        "SERAFINA QUIROS",
        "VILMA TERCEROS"
        ];

        $nombresSI = [
            "ADELA CRUZ",
        "ALBINA CALUSTRO",
        "ALCIRA TAPIA",
        "ALFREDO SAGARDIA",
        "ALICIA CLAROS",
        "ANA MARIA FLORES",
        "ANICETA CASTRO",
        "ANTINOR QUIROZ",
        "ANTONIO SAGARDIA",
        "APOLONIA JALDIN",
        "AQUILINO VERDUGUEZ",
        "ARMANDO OROSCO",
        "AURELIA QUIROZ",
        "AURORA CLAURE",
        "AURORA TAPIA",
        "BACILIA JORDAN",
        "BENEDICTA ROCHA",
        "BERNARDINA LEDEZMA",
        "BETHY CASTRO",
        "BLADIMIR TAPIA",
        "BLANCA OCHOA",
        "BORIS TAPIA",
        "CARLOS CASTRO",
        "CASIANA TAPIA",
        "CASIMIRO CRUZ",
        "CECILIO CALUSTRO",
        "CELIA CRUZ",
        "CIPRIANA TAPIA",
        "CLAUDIO LEDEZMA",
        "CONSTANCIA SOTO",
        "CRISPIN CALUSTRO",
        "CRISPIN TAPIA",
        "CRISTINA TORRICO",
        "CRISTOBAL TAPIA",
        "DANIEL FERRUFINO",
        "DARIO FERRUFINO",
        "DELICIA TAPIA",
        "DEMETRIO CARDOZO",
        "DENIS CARDOZO",
        "DENIS TAPIA",
        "DOROTEA TAPIA",
        "EINAR TERCEROS",
        "ELMER GUTIERREZ",
        "ELMER TAPIA",
        "ELOTERIA DE FERRUFINO",
        "ELOTERIA TERCEROS",
        "ELVIS QUIROZ",
        "EMETERIO ARICOMA",
        "EMETERIO CAMARA",
        "EMILIO ALMENDRAS",
        "EMMA CLAURE",
        "ENCARNACION TAPIA",
        "EPIFANIA CLAROS",
        "ERASMO JIMENEZ",
        "ERVER TAPIA",
        "ESPERANZA CASTRO",
        "EUGENIA VEIZAGA",
        "EUSEBIO LEDEZMA",
        "FANOR ELIAS JORDAN",
        "FAUSTINO CLAROS",
        "FELICIANA CALUSTRO",
        "FELIX TAPIA",
        "FERMIN REQUE",
        "FLORENCIA CHOQUE",
        "FLORINDA JORDAN",
        "FORTUNATA CLAURE",
        "FORTUNATA FUENTES",
        "FRANCISCA ALVAREZ",
        "FREDDY TAPIA",
        "FREDDY URENA",
        "GILVER JORDAN",
        "GREGORIA FUENTES",
        "GUIDO FUENTES",
        "GUIDO JUAREZ",
        "GUMERCINDO TERCEROS",
        "HERMINIA LEDEZMA",
        "HERMOGENES FUENTES",
        "HERMOGENES TORRICO",
        "HERNAN TERCEROS",
        "ILDA ROCHA",
        "IRINE QUIROZ",
        "IRINEO CRUZ",
        "ISABEL FUENTES",
        "ISABEL MONTANO",
        "JAIME CLAURE",
        "JESUS SALAZAR",
        "JORGE LEDEZMA",
        "JOSE ALVAREZ",
        "JUAN CARLOS CANO",
        "JUAN CASTRO",
        "JUAN JOSE TAPIA",
        "JUAN TAPIA",
        "JUANA VILLARROEL",
        "JULIA FERRUFINO",
        "JUSTINA CASTRO",
        "KARINA CARDOZO",
        "KARINA OROSCO",
        "LEANDRA MONTANO",
        "LEANDRO FERRUFINO",
        "LEON VALLEJOS",
        "LETICIA JORDAN",
        "LITZI PONCE",
        "LIZBETH JORDAN",
        "LUCHA VARGAS",
        "LUCIA LEDEZMA",
        "LUCIO FERREL",
        "LUCRECIO JORDAN",
        "LUCRECIO TAPIA",
        "LUIS CALUSTRO",
        "MACARIA SAGARDIA",
        "MARCELINA CALUSTRO",
        "MARCELINA CRUZ",
        "MARIA TAPIA",
        "MARIO CLAROS",
        "MARTHA CRUZ",
        "MAXIMO CASTRO",
        "MELQUIADES CASTRO",
        "MIRIAM MONTANO",
        "NELGI TAPIA",
        "NELIDA CASTRO",
        "NELIDA QUIROZ",
        "NESTOR VARGAS",
        "NICACIA DE FERRUFINO",
        "NICOLAZA LEDEZMA",
        "NOLVERTA QUIROZ",
        "ORLANDO BARRIOS",
        "ORLANDO VASQUEZ",
        "PABLO QUIROZ",
        "PALMIRA FERRUFINO",
        "PATROCINIO TAPIA",
        "PEDRO CAMARA",
        "PEDRO MONTANO",
        "PEPE CALUSTRO",
        "PILAR AREVALO",
        "PILAR CASTRO",
        "POLICARPIO FERRUFINO",
        "PRIMITIVA MIRANDA",
        "PRIMITIVO MONTANO",
        "RAFAEL TAPIA",
        "RAMIRO OROSCO",
        "RENE TAPIA",
        "RICHAR CLAROS",
        "RIMER FUENTES",
        "ROGER JALDIN",
        "SABINO ALVARES",
        "SAN ISIDRO (TRANSP 15 MAYO)",
        "SANDRA SAGARDIA",
        "SANTIAGO QUIROZ",
        "SEGUNDINA TORRICO",
        "SEVERINA RODRIGUEZ",
        "SIMON CLAROS",
        "SONIA VASQUEZ",
        "SUSI JORDAN",
        "SUSI QUIROZ",
        "TOMASA SOLIZ",
        "VALENTIN ROCHA",
        "VALERIO FLORES",
        "VALERIO JALDIN",
        "VALERIO TAPIA",
        "VALERIO TERCEROS",
        "VIRGINIA TAPIA",
        "WILFREDO FUENTES",
        "WILFREDO LEDEZMA",
        "WILLIAM TERCEROS",
        "WILLIAM VASQUEZ",
        "WILLIAN VASQUEZ",
        "WILLY WALDO FUENTES",
        "WILSON CASTRO"
        ];

        $zonasMap = [
            'TR' => ['zona' => $zonaTR, 'nombres' => $nombresTR],
            'VR' => ['zona' => $zonaVR, 'nombres' => $nombresVR],
            'SI' => ['zona' => $zonaSI, 'nombres' => $nombresSI],
        ];

        foreach ($zonasMap as $prefijo => $data) {
            foreach ($data['nombres'] as $index => $nombre) {
                $email = Str::slug($nombre) . '-' . $prefijo . '-' . ($index+1) . '@socio.com';
                $usuario = User::updateOrCreate(
                    ['email' => $email],
                    ['name' => $nombre, 'password' => Hash::make('password'), 'role_id' => $socioRole->id]
                );

                $codigo = $prefijo . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                Vivienda::updateOrCreate(
                    ['codigo' => $codigo],
                    [
                        'user_id' => $usuario->id,
                        'zone_id' => $data['zona']->id,
                        'tarifa_id' => $tarifa->id,
                        'direccion' => "Sector {$data['zona']->name}, Conexión {$codigo}",
                    ]
                );
            }
        }

        // Crear período de Mayo 2026 abierto por defecto
        Periodo::updateOrCreate(
            ['nombre' => 'Enero 2026'],
            [
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-01-31',
                'estado' => 'abierto',
                'gestion' => '2026'
            ]
        );
    }
}