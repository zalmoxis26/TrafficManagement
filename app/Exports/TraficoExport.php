<?php

namespace App\Exports;

use App\Models\Trafico;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;

class TraficoExport implements FromCollection, WithHeadings, WithEvents ,WithChunkReading
{

    protected $exportType;
    protected $status;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($exportType, $status,$fechaInicio,$fechaFin)
    {
        $this->exportType = $exportType;
        $this->status = $status;
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : Carbon::now()->subMonth()->startOfMonth(); // sino mes anterior del dia actual
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : Carbon::now()->endOfDay(); // sino hoy al final del d
        
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
         // Obtener el usuario autenticado
         $user = Auth::user();

        // Obtener los IDs de las empresas asignadas al usuario
        $empresasAsignadasIds = $user->empresas->pluck('empresa_id');
        // Build the query
        $query = Trafico::with('empresa', 'pedimento', 'revision')->whereIn('empresa_id', $empresasAsignadasIds);

        // Apply the filter based on export type
        if ($this->exportType != 'TODOS') {
            $query->whereHas('pedimento', function ($q) {
                $q->where('operacion', $this->exportType);
            });
        }

        if($this->status != 'TODOS'){
            $query->whereHas('pedimento', function($p){
                $p->where('statusTrafico', $this->status);
            });
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fechaReg', [$this->fechaInicio, $this->fechaFin]);
        }

        // Limitar a 5000 resultados
        $query->orderBy('fechaReg', 'DESC')->limit(5000);

        // Obtener los resultados
        $traficos = $query->get();


    

        // Map the results to include related fields
            $traficosCollection = $traficos->map(function($trafico) {
                return [
                    'id' => $trafico->id,
                    'cliente' => $trafico->empresa->descripcion,
                    'factura' => $trafico->factura,
                    'pedimento' => $trafico->pedimento ? $trafico->pedimento->numPedimento : '',
                    'clave' => $trafico->pedimento ? $trafico->pedimento->clavePed : '',
                    'tipo_operacion' => $trafico->pedimento ? $trafico->pedimento->operacion : '',
                    'recepcion_factura' => $trafico->fechaReg,
                    'inicia_revision' => optional($trafico->revision)->inicioRevision,
                    'fin_revision' => optional($trafico->revision)->finRevision,
                    'factura_correcta' =>  optional($trafico->revision)->facturaCorrecta,
                    'fechaDodaPita' => $trafico->pedimento ? $trafico->pedimento->fechaDodaPita : '',
                    'estatus' => $trafico->statusTrafico,
                ];
            });


        // Return the collection
        return collect($traficosCollection);
    }

    public function headings(): array
    {
        return [
            '#TAFICO',
            'CLIENTE',
            'FACTURA',
            'PEDIMENTO',
            'CLAVE',
            'TIPO OPERACION',
            'RECEPCION FACTURA',
            'INICIA REVISION',
            'FIN REVISION',
            'FACTURA CORRECTA',
            'DODA/ PITA EN TRAFICO',
            'STATUS',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto tamaño para todas las columnas
                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Centrar el contenido de las columnas
                $sheet->getStyle('A1:L' . $sheet->getHighestRow())
                      ->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Poner en negrita el encabezado
                $sheet->getStyle('A1:L1')->getFont()->setBold(true);
            },
        ];
    }
    public function chunkSize(): int
    {
        return 500; // Tamaño del chunk (ejemplo: 500 registros por chunk)
    }
}
