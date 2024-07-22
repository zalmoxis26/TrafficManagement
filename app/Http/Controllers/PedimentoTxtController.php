<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedimentoTxt;
use App\Models\Contribucion;
use App\Models\FechasPedimento;
use App\Models\Partida;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Facades\Excel;

class PedimentoTxtController extends Controller
{
    
    // Mostrar el formulario para cargar el archivo .txt
    public function cargarUnTxt(){

        return view('pedimentoTxt.createAntiguo');
   }

  // Mostrar el contenido del archivo .txt
  public function visualizarUnTxt(Request $request)
  {
   
       $request->validate([
           'file' => 'required|mimes:txt|max:2048',
       ]);

       $file = $request->file('file');
       $contentExtract = file($file->getRealPath());
       $content = file_get_contents($file->getRealPath());

       // Definir los medios de transporte
       $mediosTransporte = [
           '1' => 'MARÍTIMO',
           '2' => 'FERROVIARIO DE DOBLE ESTIBA',
           '3' => 'CARRETERO-FERROVIARIO',
           '4' => 'AÉREO',
           '5' => 'POSTAL',
           '6' => 'FERROVIARIO',
           '7' => 'CARRETERO',
           '8' => 'TUBERÍA',
           '10' => 'CABLES',
           '11' => 'DUCTOS',
           '12' => 'PEATONAL',
           '98' => 'NO SE DECLARA MEDIO DE TRANSPORTE POR NO HABER PRESENTACIÓN FÍSICA DE MERCANCÍAS ANTE LA ADUANA',
           '99' => 'OTROS'
       ];



         // Definir las abreviaciones para la columna 3 del índice 510
         $contribuciones = [
           '1' => 'DTA',
           '2' => 'C.C.',
           '3' => 'IVA',
           '4' => 'ISAN',
           '6' => 'IGI/IGE',
           '7' => 'REC.',
           '9' => 'OTROS',
           '11' => 'MULT.',
           '12' => '2.5',
           '13' => 'RT',
           '15' => 'PRV',
           '16' => 'EUR',
           '17' => 'REU',
           '20' => 'MT',
           '22' => 'IEPS',
           '23' => 'IVA/PRV',
           '24' => '2IB',
           '25' => '2IA2',
           '26' => '2IA1',
           '27' => '2IC',
           '28' => '2IF',
           '29' => '2IG',
           '30' => '2IJ',
           '31' => '2II',
           '32' => 'ICF',
           '33' => 'IEPSDIE',
           '34' => 'ICNF',
           '35' => 'LIEPS',
           '50' => 'DFC'
       ];

          // Definir las descripciones para la columna 4 del índice 510
          $descripciones = [
           '0' => 'EFECTIVO',
           '2' => 'FIANZA',
           '4' => 'DEPÓSITO EN CUENTA ADUANERA',
           '5' => 'TEMPORAL NO SUJETA A IMPUESTOS',
           '6' => 'PENDIENTE DE PAGO',
           '7' => 'CARGO A PARTIDA PRESUPUESTAL GOBIERNO FEDERAL',
           '8' => 'FRANQUICIA',
           '9' => 'EXENTO DE PAGO',
           '12' => 'COMPENSACIÓN',
           '13' => 'PAGO YA EFECTUADO',
           '14' => 'CONDONACIONES',
           '15' => 'CUENTAS ADUANERAS DE GARANTÍA POR PRECIOS ESTIMADOS',
           '16' => 'ACREDITAMIENTO',
           '18' => 'ESTÍMULO FISCAL',
           '19' => 'OTROS MEDIOS DE GARANTÍA',
           '21' => 'CRÉDITO EN IVA E IEPS',
           '22' => 'GARANTÍA EN IVA E IEPS'
       ];

        // Tipos de fecha
        $tiposFecha = [
           '1' => 'ENTRADA',
           '2' => 'PAGO',
           '3' => 'EXTRACCIÓN',
           '5' => 'PRESENTACIÓN',
           '6' => 'IMP. EUA/CAN',
           '7' => 'ORIGINAL'
       ];



       // Filtrar y organizar la información LOS INDICES
       $data501 = [];
       $data506 = [];
       $data510 = [];
       $data551 = [];
       $countPartidas = 0;

       foreach ($contentExtract as $line) {
           $parts = explode('|', $line);

 //TOMAR 501 DATOS GENERALES PEDIMENTO TXT      

           if ($parts[0] == '501') {

                // Transformar Tipo de Operación
                $tipoOperacion = $parts[4] ?? '';
                if ($tipoOperacion == '1') {
                    $tipoOperacion = 'Importación';
                } elseif ($tipoOperacion == '2') {
                    $tipoOperacion = 'Exportación';
                }

               // Transformar Medio de Transporte de E/S
               $medioTransporte = $mediosTransporte[$parts[19]] ?? $parts[19];

               $data501[] = [
                   'Patente' => $parts[1] ?? '',
                   '#Pedimento' => $parts[2] ?? '',
                   'Aduana' => $parts[3] ?? '',
                   'Tipo Operación' =>  $tipoOperacion,
                   'Clave de Ped' => $parts[5] ?? '',
               //  'Aduana-Sección de Entrada o Salida' => $parts[6] ?? '',
               //   'CURP Importador o Exportador' => $parts[7] ?? '',
                   'RFC Import/Export' => $parts[8] ?? '',
                   'CURP del Agente aduanal' => $parts[9] ?? '',
                   'Tipo de Cambio' => $parts[10] ?? '',
               //    'Fletes' => $parts[11] ?? '',
               //   'Seguros' => $parts[12] ?? '',
               //   'Embalajes' => $parts[13] ?? '',
               //    'Otros Incrementables' => $parts[14] ?? '',
                  // 'Uso Futuro' => $parts[15] ?? '',
                   'Peso Bruto' => $parts[16] . ' Kg' ?? '',
               //  'Medio de Transporte de Salida' => $parts[17] ?? '',
               // 'Medio de Transporte de Arribo' => $parts[18] ?? '',
                   'Medio de Transporte de E/S' => $medioTransporte  ,
                   'Origen o Destino' => $parts[20] ?? '',
                   'Nombre Importador/Exportador' => $parts[21] ?? '',
                  'Dirección' => 
                       ($parts[22] ?? '') . ', ' . 
                       ('Int: ' . ($parts[23] ?? '')) . ', ' . 
                       ('Ext: ' . ($parts[24] ?? '')) . ', ' . 
                       ('CP: ' . $parts[25] ?? '') . ', ' . 
                       ($parts[26] ?? '') . ', ' . 
                       ($parts[27] ?? '') . ', ' . 
                       ($parts[28] ?? ''),
               //    'RFC de Facturación' => $parts[29] ?? '',
               ];

//TOMAR 510 CONTRIBUCIONES

           } elseif ($parts[0] == '510') {
               $columna3 = $contribuciones[$parts[2]] ?? $parts[2];
               $columna4 = $descripciones[$parts[3]] ?? $parts[3];
               $data510[] = [
                   'Contribucion' => $columna3,
                   'Forma de Pago' => $columna4,
                   'Importe' => $parts[4] ?? '',
               ];

//TOMAR 506 FECHAS

           }elseif ($parts[0] == '506') {
                  // Formatear la fecha
                  $fecha = isset($parts[3]) ? \DateTime::createFromFormat('dmY', $parts[3])->format('Y-m-d') : '';

               $tipoFecha = $tiposFecha[$parts[2]] ?? $parts[2];
               $data506[] = [
                   'Tipo de Fecha' => $tipoFecha,
                   'Fecha' => $fecha,
               ];   


// TOMAR 551 PARTIDAS
       } elseif ($parts[0] == '551') {
               $countPartidas++;
               $data551[] = [
                   'Número de Pedimento' => $parts[1] ?? '',
                   'Fracción Arancelaria' => $parts[2] ?? '',
                   'Número de Partida' => $parts[3] ?? '',
                   'Subdivision de la Fracción' => $parts[4] ?? '',
                   'Descripcion de la Mercancía' => $parts[5] ?? '',
                   'Precio Unitario' => $parts[6] ?? '',
                   'Valor en Aduana' => $parts[7] ?? '',
                   'Importe del precio pagado o Valor Comercial' => $parts[8] ?? '',
                   'Valor en Dólares (USD)' => $parts[9] ?? '',
                   'Cantidad de mercancía en Unidades de Medida de Comercialización' => $parts[10] ?? '',
                   'Unidad de Medida de Comercialización' => $parts[11] ?? '',
                   'Cantidad de mercancía en Unidades de la LIGIE' => $parts[12] ?? '',
                   'Unidad de Medida de la LIGIE' => $parts[13] ?? '',
                   'Valor Agregado' => $parts[14] ?? '',
                   'Vinculación' => $parts[15] ?? '',
                   'Método de Valoración' => $parts[16] ?? '',
                   'Código del producto' => $parts[17] ?? '',
                   'Marca de la mercancía' => $parts[18] ?? '',
                   'Modelo o Lote de la mercancía' => $parts[19] ?? '',
                   'País de Origen o Destino de la mercancía' => $parts[20] ?? '',
                   'País Vendedor o Comprador' => $parts[21] ?? '',
               ];
           }
       }

       return view('pedimentoTxt.show', compact('content', 'data501','data510','data551','countPartidas','data506'));
   }
    
    // Método para mostrar el índice
    public function index()
    {
       
        $pedimentos = PedimentoTxt::with(['contribuciones', 'fechasPedimento', 'partidas'])->get();

    foreach ($pedimentos as $pedimento) {
        foreach ($pedimento->contribuciones as $contribucion) {
            $contribucion->contribucion_descripcion = $contribucion->contribucion_descripcion;
            $contribucion->forma_pago_descripcion = $contribucion->forma_pago_descripcion;
            $contribucion->importe = $contribucion->importe ? floatval($contribucion->importe) : 0.00; // Asegurarse de que el importe es numérico y no nulo
        }

        foreach ($pedimento->partidas as $partida) {
            $partida->unidad_medida_comercializacion_descripcion = $partida->unidad_medida_comercializacion_descripcion;
            $partida->metodo_valoracion_descripcion = $partida->metodo_valoracion_descripcion;
            $partida->unidad_medida_ligie_descripcion = $partida->unidad_medida_ligie_descripcion;
        }

        $pedimento->origen_destino_descripcion = $pedimento->origen_destino_descripcion;
        $pedimento->medio_transporte_descripcion = $pedimento->medio_transporte_descripcion; 
        
    }

        return view('pedimentoTxt.index', compact('pedimentos'));
    }
    
    
    
    public function create(){

         return view('pedimentoTxt.create');
    }


   // Mostrar el contenido del archivo .txt
   public function show(Request $request)
   {
     

        return view('pedimentoTxt.show', compact('content', 'data501','data510','data551','countPartidas','data506'));
    }

    public function store(Request $request)
    { 
            $request->validate([
                'file.*' => 'required|mimes:txt|max:2048',
            ]);
    
            $files = $request->file('file');
    
            // Definir los medios de transporte
       /*     $mediosTransporte = [
                '1' => 'MARÍTIMO',
                '2' => 'FERROVIARIO DE DOBLE ESTIBA',
                '3' => 'CARRETERO-FERROVIARIO',
                '4' => 'AÉREO',
                '5' => 'POSTAL',
                '6' => 'FERROVIARIO',
                '7' => 'CARRETERO',
                '8' => 'TUBERÍA',
                '10' => 'CABLES',
                '11' => 'DUCTOS',
                '12' => 'PEATONAL',
                '98' => 'NO SE DECLARA MEDIO DE TRANSPORTE POR NO HABER PRESENTACIÓN FÍSICA DE MERCANCÍAS ANTE LA ADUANA',
                '99' => 'OTROS'
            ];  */
    
            foreach ($files as $file) {
                $contentExtract = file($file->getRealPath());
    
                foreach ($contentExtract as $line) {
                    $parts = explode('|', $line);
    
                    if ($parts[0] == '501') {
                        // Transformar Tipo de Operación
                        $tipoOperacion = $parts[4] ?? '';
                        if ($tipoOperacion == '1') {
                            $tipoOperacion = 'Importación';
                        } elseif ($tipoOperacion == '2') {
                            $tipoOperacion = 'Exportación';
                        }
    
                        // Transformar Medio de Transporte de E/S
                      // $medioTransporte = $mediosTransporte[$parts[19]] ?? $parts[19];
    
                        $data = [
                            'patente' => $parts[1] ?? '',
                            'pedimento' => $parts[2] ?? '',
                            'aduana' => $parts[3] ?? '',
                            'tipo_operacion' => $tipoOperacion,
                            'clave_ped' => $parts[5] ?? '',
                            'rfc_import_export' => $parts[8] ?? '',
                            'curp_agente_aduanal' => $parts[9] ?? '',
                            'tipo_cambio' => $parts[10] ?? '',
                            'peso_bruto' => $parts[16] . ' Kg' ?? '',
                            'medio_transporte_es' =>  $parts[19] ?? '',
                            'origen_destino' => $parts[20] ?? '',
                            'nombre_importador_exportador' => $parts[21] ?? '',
                            'direccion' => ($parts[22] ?? '') . ', ' . 
                                          ('Int: ' . ($parts[23] ?? '')) . ', ' . 
                                          ('Ext: ' . ($parts[24] ?? '')) . ', ' . 
                                          ('CP: ' . $parts[25] ?? '') . ', ' . 
                                          ($parts[26] ?? '') . ', ' . 
                                          ($parts[27] ?? '') . ', ' . 
                                          ($parts[28] ?? ''),
                        ];
    
                        // Verificar si el registro ya existe
                    $pedimentoTxt = PedimentoTxt::updateOrCreate(
                        [
                            'patente' => $data['patente'],
                            'pedimento' => $data['pedimento'],
                            'aduana' => $data['aduana']
                        ],
                        $data
                    );

                } elseif ($parts[0] == '510') {

                    Contribucion::updateOrCreate([
                        'pedimento_txt_id' => $pedimentoTxt->id,
                        'contribucion' =>$parts[2] ?? '',
                        'forma_pago' => $parts[3] ?? '',
                        'importe' => $parts[4] ?? 0,
                    ]);

            
                }elseif ($parts[0] == '506') {
                      //  $tipoFecha = $tiposFecha[$parts[2]] ?? $parts[2];
                        $fecha = isset($parts[3]) ? \DateTime::createFromFormat('dmY', $parts[3])->format('Y-m-d') : '';
    
                        FechasPedimento::updateOrCreate([
                            'pedimento_txt_id' => $pedimentoTxt->id,
                            'tipo_fecha' =>$parts[2] ?? '',
                            'fecha' => $fecha,
                        ]);

                    } elseif ($parts[0] == '551') {
                    Partida::updateOrCreate([
                        'pedimento_txt_id' => $pedimentoTxt->id,
                        'numero_pedimento' => $parts[1] ?? '',
                        'fraccion_arancelaria' => $parts[2] ?? '',
                        'numero_partida' => $parts[3] ?? '',
                        'subdivision_fraccion' => $parts[4] ?? '',
                        'descripcion_mercancia' => $parts[5] ?? '',
                        'precio_unitario' => $parts[6] !== '' ? $parts[6] : 0,
                        'valor_aduana' => $parts[7] !== '' ? $parts[7] : 0,
                        'importe_precio_pagado' => $parts[8] !== '' ? $parts[8] : 0,
                        'valor_dolares' => $parts[9] !== '' ? $parts[9] : 0,
                        'cantidad_mercancia_en_UMC' => $parts[10] !== '' ? $parts[10] : 0,
                        'unidad_medida_comercializacion' => $parts[11] !== '' ? $parts[11] : 0,
                        'cantidad_mercancia_unidad_ligie' => $parts[12] !== '' ? $parts[12] : 0,
                        'unidad_medida_ligie' => $parts[13] !== '' ? $parts[13] : null,
                        'valor_agregado' => $parts[14] !== '' ? $parts[14] : null,
                        'vinculacion' => $parts[15] ?? '',
                        'metodo_valoracion' => $parts[16] !== '' ? $parts[16] : 0,
                        'codigo_producto' => $parts[17] ?? '',
                        'marca_mercancia' => $parts[18] ?? '',
                        'modelo_lote_mercancia' => $parts[19] ?? '',
                        'pais_origen_destino_mercancia' => $parts[20] ?? '',
                        'pais_vendedor_comprador' => $parts[21] ?? '',
                    ]);
                }
            }  
        }      

        return Redirect::Route('pedimentoTxt.index');
    }

    public function OpcionesExportPedimentosTxt()
    {
       // return view('export');
    }


}
