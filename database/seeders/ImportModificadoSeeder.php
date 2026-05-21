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
            1 => "CLAUDIO VARGAS",
            2 => "CRISTOBAL JALDIN",
            3 => "FELIPE VELIZ",
            4 => "ISABEL JALDIN",
            5 => "SOFIA QUIROZ",
            6 => "LUIS TAPIA",
            7 => "ISABEL VASQUEZ",
            8 => "SOFIA QUIROZ",
            9 => "HILARIA COLQUE",
            10 => "MIRTHA TAPIA",
            11 => "GENARO CASTRO",
            12 => "FACUNDO CASTRO",
            13 => "NATIVIDAD QUIROZ",
            14 => "EDILBERTO TAPIA",
            15 => "GIOVANA ALVAREZ",
            16 => "GUMERCINDO ALVAREZ",
            17 => "HORTENCIA CLAROS",
            18 => "GERARDO JORDAN",
            19 => "NIVARDO CALUSTRO",
            20 => "ALICIA CAMARA",
            21 => "ANDREA SOTO",
            22 => "ROBERT CASTRO",
            23 => "EMETERIO ZAPATA",
            24 => "EDMUNDO TAPIA",
            25 => "CLAUDINA CALUSTRO",
            26 => "SABINA CALUSTRO",
            27 => "ROXANA CRUZ",
            28 => "PASTOR MONTAÑO",
            29 => "FOTUNATO MONTAÑO",
            30 => "LILIANA SAUSA VEIZAGA",
            31 => "EVARISTO PLATA",
            32 => "ORLANDO JORDAN",
            33 => "MARTHA ROJAS",
            34 => "CASIANO PANIAGUA",
            35 => "JOSE BARRIENTOS",
            36 => "CELIA JORDAN",
            37 => "LEONORA VARGAS",
            38 => "WILDER CRUZ",
            39 => "EDELMIRA FERRUFINO",
            40 => "RUBEN MONTAÑO",
            41 => "CRISTINA JORDAN",
            42 => "EDELMIRA FERRUFINO",
            43 => "RENE SAGARDIA",
            44 => "FRANCISCA MONTAÑO",
            45 => "ROSA TAPIA",
            46 => "FRANCISCO SAGARDIA",
            47 => "MODESTA CALUSTRO",
            48 => "OLGA TAPIA",
            49 => "EFRAIN TAPIA",
            50 => "VILMA MONTAÑO",
            51 => "MARIA REA",
            52 => "BRYAN PANIAGUA",
            53 => "LUCIA CALUSTRO",
            54 => "MOISE TAPIA",
            55 => "VALENTIN CLAURE",
            56 => "PRIMITIVA CASTRO",
            57 => "LIMBER CASTRO",
            58 => "CONSTANTINO BARRIENTOS",
            59 => "CONSTANTINO BARRIENTOS",
            60 => "ROSMERY MONTAÑO",
            61 => "LUCIA DE SAGARDIA",
            62 => "LUISA FERRUFINO",
            63 => "JULIO CAMACHO",
            64 => "ANGELINA BARRIENTOS",
            65 => "JULIA BARRIENTOS",
            66 => "EMETERIO GUTIERREZ",
            67 => "SONIA CASTRO",
            68 => "SABINA CASTRO",
            69 => "MIGUELINA VARGAS",
            70 => "EUSEBIO VILLCA",
            71 => "APOLONIA VARGAS",
            72 => "MARIA DE VARGAS",
            73 => "PANFILO CHOQUE",
            74 => "EDWIN CASTRO",
            75 => "ROMULO FUENTES",
            76 => "ROMULO FUENTES",
            77 => "CONCEPCION CALUSTRO",
            78 => "CASIANO CALUSTRO",
            79 => "ADELAIDA CALUSTRO",
            80 => "ANDREA REA",
            81 => "MARCO MERINO",
            82 => "EMILIA BARRIENTOS",
            83 => "ESTEBAN BARRIENTOS",
            84 => "MELICIA LEDEZMA",
            85 => "VALERIO RAMIREZ"
        ];

        $nombresVR = [
            1 => "BENEDICTA FERRUFINO",
            2 => "CRISTINA FERREL",
            3 => "GERARDO ZAPATA",
            4 => "ROGELIO CASTRO",
            5 => "RUFINO VERDUGUEZ",
            6 => "MARCIAL FERRUFINO",
            7 => "FELICIDAD SAGARDIA",
            8 => "FAQUINA VILLARROEL",
            9 => "LEONARDA CALUSTRO",
            10 => "MARTA GIMENEZ",
            11 => "CONSTANTINO ZURITA",
            12 => "KARMINIA ZAPATA",
            13 => "ROLANDO TERCEROS",
            14 => "SANTIAGO REA",
            15 => "MACARIO CASTRO",
            16 => "VILMA TERCEROS",
            17 => "ISMAEL JALDIN",
            18 => "JAIME JALDIN",
            20 => "INES ORELLANA",
            21 => "INDALICIO CASTRO",
            22 => "SERAFINA QUIROS",
            24 => "PEDRO VILLARROEL",
            25 => "PEPE ZURITA"
        ];

        $nombresSI = [
            1 => "ANTONIO SAGARDIA",
            2 => "VALERIO JALDIN",
            3 => "FELICIANA CALUSTRO",
            4 => "ANICETA CASTRO",
            5 => "LUCRECIO JORDAN",
            6 => "JUAN CASTRO",
            7 => "KARINA OROSCO",
            8 => "MARCELINA CALUSTRO",
            9 => "JUSTINA CASTRO",
            10 => "LIZBETH JORDAN",
            11 => "ALBINA CALUSTRO",
            12 => "PABLO QUIROZ",
            13 => "RICHAR CLAROS",
            14 => "VIRGINIA TAPIA",
            15 => "FAUSTINO CLAROS",
            16 => "IRINE QUIROZ",
            17 => "ANTINOR QUIROZ",
            18 => "FANOR ELIAS JORDAN",
            19 => "TOMASA SOLIZ",
            20 => "ALCIRA TAPIA",
            21 => "DOROTEA TAPIA",
            22 => "EMETERIO ARICOMA",
            23 => "JOSE ALVAREZ",
            24 => "GILVER JORDAN",
            25 => "EPIFANIA CLAROS",
            26 => "GREGORIA FUENTES",
            27 => "ANA MARIA FLORES",
            28 => "ALICIA CLAROS",
            29 => "ISMAEL JALDIN",
            30 => "BERNARDINA LEDEZMA",
            31 => "FREDDY TAPIA",
            32 => "FREDDY UREÑA",
            33 => "CLAUDIO LEDEZMA",
            34 => "ARMANDO OROSCO",
            35 => "TOMASA SOLIZ",
            36 => "LITZI PONCE",
            37 => "NATIVIDAD QUIROZ",
            38 => "EDILVERTO TAPIA",
            39 => "MACARIA SAGARDIA",
            40 => "NICOLAZA LEDEZMA",
            41 => "FELIX TAPIA",
            42 => "VALERIO TAPIA",
            43 => "LUCRECIO TAPIA",
            44 => "APOLONIA JALDIN",
            45 => "GREGORIA FUENTES",
            46 => "DEMETRIO CARDOZO",
            47 => "SIMON CLAROS",
            48 => "CRISPIN CALUSTRO",
            49 => "EMMA CLAURE",
            50 => "MIRIAM MONTAÑO",
            51 => "ENCARNACION TAPIA",
            52 => "ELMER TAPIA",
            53 => "HERMINIA  LEDEZMA",
            54 => "DELICIA TAPIA",
            55 => "GUMERCINDO TERCEROS",
            56 => "EINAR TERCEROS",
            57 => "EMILIO ALMENDRAS",
            58 => "MELICIA LEDEZMA",
            59 => "LUCHA VARGAS",
            60 => "ROGER JALDIN",
            61 => "WILFREDO LEDEZMA",
            62 => "FERMIN REQUE",
            63 => "BLANCA OCHOA",
            64 => "CRISTOBAL TAPIA",
            65 => "NICACIA DE FERRUFINO",
            66 => "LEANDRO FERRUFINO",
            67 => "VIRGINIA TAPIA",
            68 => "FORTUNATA FUENTES",
            69 => "WILLIAM TERCEROS",
            70 => "LUCIO FERREL",
            71 => "JUAN TAPIA",
            72 => "MARIA TAPIA",
            73 => "SABINO ALVARES",
            74 => "EDMUNDO TAPIA",
            75 => "BLADIMIR TAPIA",
            76 => "SEVERINA RODRIGUEZ",
            77 => "LEANDRA MONTAÑO",
            78 => "FLORINDA JORDAN",
            79 => "SANDRA SAGARDIA",
            80 => "BENEDICTA ROCHA",
            81 => "MACARIA SAGARDIA",
            82 => "FELICIDAD SAGARDIA",
            83 => "CECILIO CALUSTRO",
            84 => "CARLOS CASTRO",
            85 => "VALENTIN ROCHA",
            86 => "JORGE LEDEZMA",
            87 => "MARCELINA CRUZ",
            88 => "FRANCISCA ALVAREZ",
            89 => "VALENTIN CLAURE",
            90 => "EUGENIA VEIZAGA",
            91 => "HERMOGENES FUENTES",
            92 => "ELOTERIA TERCEROS",
            93 => "ELOTERIA DE FERRUFINO",
            94 => "ORLANDO VASQUEZ",
            95 => "DARIO FERRUFINO",
            96 => "DANIEL FERRUFINO",
            97 => "PRIMITIVO MONTAÑO",
            98 => "CONSTANCIA SOTO",
            99 => "ERVER TAPIA",
            100 => "IRINEO CRUZ",
            101 => "CELIA CRUZ",
            102 => "BACILIA JORDAN",
            103 => "LEON VALLEJOS",
            104 => "ERASMO JIMENEZ",
            105 => "PEDRO MONTAÑO",
            106 => "PILAR AREVALO",
            107 => "NELIDA QUIROZ",
            108 => "ALBINA CALUSTRO",
            109 => "JUAN CASTRO",
            110 => "ROLANDO TERCEROS",
            111 => "JULIO CAMACHO",
            112 => "SUSI QUIROZ",
            113 => "RAMIRO OROSCO",
            114 => "CIPRIANA TAPIA",
            115 => "CRISPIN TAPIA",
            116 => "SONIA VASQUEZ",
            117 => "AURELIA QUIROZ",
            118 => "RIMER FUENTES",
            119 => "WILSON CASTRO",
            120 => "CLAUDIO LEDEZMA",
            121 => "NELGI TAPIA",
            122 => "PEPE CALUSTRO",
            123 => "JAIME CLAURE",
            124 => "ESPERANZA CASTRO",
            125 => "SUSI JORDAN",
            126 => "RAFAEL TAPIA",
            127 => "ADELA CRUZ",
            128 => "WILLY WALDO FUENTES",
            129 => "GUIDO FUENTES",
            130 => "ISABEL FUENTES",
            131 => "ALFREDO SAGARDIA",
            132 => "FRANCISCO SAGARDIA",
            133 => "MARIO CLAROS",
            134 => "MARTHA CRUZ",
            135 => "PALMIRA FERRUFINO",
            136 => "PEDRO CAMARA",
            137 => "MARIA REA",
            138 => "KARINA CARDOZO",
            139 => "DENIS CARDOZO",
            140 => "EUSEBIO LEDEZMA",
            141 => "JUAN JOSE TAPIA",
            142 => "PATROCINIO TAPIA",
            143 => "BORIS TAPIA",
            144 => "ALICIA CLAROS",
            145 => "PATROCINIO TAPIA",
            146 => "ELVIS QUIROZ",
            147 => "LUIS CALUSTRO",
            148 => "RENE TAPIA",
            149 => "AQUILINO VERDUGUEZ",
            150 => "JUAN CARLOS CANO",
            151 => "WILLIAM VASQUEZ",
            152 => "SANTIAGO QUIROZ",
            153 => "NOLVERTA QUIROZ",
            154 => "LUCIA LEDEZMA",
            155 => "CRISTINA TORRICO",
            156 => "ISABEL MONTANO",
            157 => "MELQUIADES CASTRO",
            158 => "JULIA FERRUFINO",
            159 => "CASIMIRO CRUZ",
            160 => "ORLANDO BARRIOS",
            161 => "EMETERIO CAMARA",
            162 => "MAXIMO CASTRO",
            163 => "PILAR CASTRO",
            164 => "AURORA CLAURE",
            165 => "GUIDO JUAREZ",
            166 => "NELIDA CASTRO",
            167 => "FORTUNATA CLAURE",
            168 => "ELMER GUTIERREZ",
            169 => "FLORENCIA CHOQUE",
            170 => "POLICARPIO FERRUFINO",
            171 => "NESTOR VARGAS",
            172 => "JUANA VILLARROEL",
            173 => "CASIANA TAPIA",
            174 => "ANDREA REA",
            175 => "ILDA ROCHA",
            176 => "SEGUNDINA TORRICO",
            177 => "AURORA TAPIA",
            178 => "HERMOGENES TORRICO",
            179 => "JESUS SALAZAR",
            180 => "AURORA TAPIA",
            181 => "DENIS TAPIA",
            182 => "VALERIO TERCEROS",
            183 => "SAN ISIDRO (TRANSP 15 MAYO)",
            184 => "VALERIO FLORES",
            185 => "EDWIN CASTRO",
            186 => "LETICIA JORDAN",
            187 => "ROSA TAPIA",
            188 => "BETHY CASTRO",
            189 => "WILFREDO FUENTES",
            190 => "LUCRECIO TAPIA",
            191 => "PRIMITIVA MIRANDA",
            192 => "WILLIAN VASQUEZ",
            193 => "HERNAN TERCEROS"
        ];

        $zonasMap = [
            'TR' => ['zona' => $zonaTR, 'nombres' => $nombresTR],
            'VR' => ['zona' => $zonaVR, 'nombres' => $nombresVR],
            'SI' => ['zona' => $zonaSI, 'nombres' => $nombresSI],
        ];

        $createdUsers = [];

        foreach ($zonasMap as $prefijo => $data) {
            foreach ($data['nombres'] as $num => $nombre) {
                $nombreClean = trim($nombre);
                if (empty($nombreClean)) continue;

                // Normalización de nombres para agrupar duplicados
                $norm = strtolower($nombreClean);
                $norm = preg_replace('/\s+/', ' ', $norm);
                $norm = str_replace(['ñ', 'Ñ'], 'n', $norm);
                $norm = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $norm);

                if (!isset($createdUsers[$norm])) {
                    $emailSlug = str_replace(' ', '-', $norm);
                    $email = $emailSlug . '@socio.com';
                    
                    $usuario = User::updateOrCreate(
                        ['email' => $email],
                        ['name' => $nombreClean, 'password' => Hash::make('password'), 'role_id' => $socioRole->id]
                    );
                    $createdUsers[$norm] = $usuario;
                } else {
                    $usuario = $createdUsers[$norm];
                }

                $codigo = $prefijo . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
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

        // Crear período de Enero 2026 abierto por defecto
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