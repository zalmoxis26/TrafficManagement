<?php

namespace App\Exports;

use App\Models\Embarque;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Importar Auth

class EmbarquesExport implements FromCollection, WithHeadings , WithEvents , WithChunkReading
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $modulado;

    public function __construct($fechaInicio, $fechaFin, $modulado)
    {
        $this->fechaInicio = $fechaInicio ? Carbon::parse($fechaInicio)->startOfDay() : Carbon::now()->subMonth()->startOfMonth(); // sino mes anterior del dia actual
        $this->fechaFin = $fechaFin ? Carbon::parse($fechaFin)->endOfDay() : Carbon::now()->endOfDay(); // sino hoy al final del dia
        $this->modulado = $modulado;
    }

    public function collection()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener los IDs de las empresas asignadas al usuario
        $empresasAsignadasIds = $user->empresas->pluck('empresa_id');

        // Filtrar los embarques y tráficos por las empresas asignadas al usuario autenticado
        $query = Embarque::with(['traficos' => function($query) use ($empresasAsignadasIds) {
            $query->select([
                'traficos.id',
                'traficos.factura',
                'traficos.clavePed',
                'traficos.aduana',
                'traficos.pedimento_id',
                'traficos.empresa_id'
            ])->whereIn('empresa_id', $empresasAsignadasIds)->with([
                'empresa' => function($query) {
                    $query->select([
                        'empresas.id',
                        'empresas.descripcion'
                    ]);
                }
            ]);
        }])->whereHas('traficos', function($query) use ($empresasAsignadasIds) {
            $query->whereIn('empresa_id', $empresasAsignadasIds);
        });

        if ($this->fechaInicio && $this->fechaFin) {
            $query->whereBetween('fechaEmbarque', [$this->fechaInicio, $this->fechaFin]);
        }

        if ($this->modulado !== 'TODOS') {
            if ($this->modulado === 'SI') {
                $query->whereNotNull('modulado');
            } else {
                $query->whereNull('modulado');
            }  
        }

        // Limitar a 5000 resultados
        $query->orderBy('fechaEmbarque', 'DESC')->limit(5000);

        return $query->get()->map(function($embarque) {
            $trafico = $embarque->traficos->first();
            return [
                'numEmbarque' => $embarque->numEmbarque,
                'numEconomico' => $embarque->numEconomico,
                'Factura' => $trafico ? $trafico->factura : null,
                'Entregado' => $embarque->entregaDocs,
                'Desaduanado' => $embarque->modulado,
                'clavePedimento' => $trafico ? $trafico->clavePed : null,
                'TipoOperacion' => $trafico && $trafico->pedimento ? "AVISO CONSOLIDAD DE " .  strtoupper($trafico->pedimento->operacion) : null,
                'ClaveAduana' => $trafico ? $trafico->aduana : null,
                'NombreEmpresa' => $trafico && $trafico->empresa ? $trafico->empresa->descripcion : null,
                'Transporte' => $embarque->Transporte
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Numero de Embarque',
            'Numero Economico',
            'Factura',
            'Entregado',
            'Desaduanado',
            'Clave de Pedimento',
            'Tipo de Operacion',
            'Clave de Aduana',
            'Nombre de la Empresa',
            'Transporte'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto tamaño para todas las columnas
                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Centrar el contenido de las columnas
                $sheet->getStyle('A1:J' . $sheet->getHighestRow())
                      ->getAlignment()
                      ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Poner en negrita el encabezado
                $sheet->getStyle('A1:J1')->getFont()->setBold(true);
            },
        ];
    }

    public function chunkSize(): int
    {
        return 500; // Tamaño del chunk (ejemplo: 500 registros por chunk)
    }

}