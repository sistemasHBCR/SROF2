<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Models\Importlog_Utilities;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets; //hace que trabaje funcion sheets para especificar hoja importar
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas; //celdas con formulas solo tomara valor
use Maatwebsite\Excel\Concerns\WithHeadingRow; //ignora encabezados

class UtilitiesImport implements ToModel, WithHeadingRow, WithMultipleSheets, WithCalculatedFormulas
{

    //creamos una variable igual a variable recibida desde un metodo.
    protected $sheetName;
    protected $token;


    public function __construct($sheetName, $token)
    {
        $this->sheetName = $sheetName;
        $this->token = $token;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

     //especificar que hoja importar
    public function sheets(): array
    {
        return [
            $this->sheetName => new UtilitiesImport($this->sheetName, $this->token), 
        ];
    }

    public function model(array $row)
    {
        // Convertir $row a un array indexado
        $row = array_values($row);
        // Obtener valores de celdas específicas (basado en la fila actual)
        $token = $this->token;
        $residencia = $row[0];
        $room = 0;
        $owner = $row[2];
        $ocupacion = $row[3];
        $kw = $row[4];
        $agua = $row[5];
        $gas = $row[6];
        $total_kw = $row[7];
        $total_kwfee = $row[8];
        $total_gas = $row[9];
        $total_gasfee = $row[10];
        $total_agua = $row[11];
        $total_sewer = $row[12];
        $subtotal = $row[13];
        $tax = $row[14];
        $total = $row[15];


        // Verificar si la fila actual no es un encabezado
        if (isset($token, $residencia, $room, $owner, $ocupacion, $kw, $agua, $gas, $total_kw, $total_kwfee, $total_gas, $total_gasfee, $total_agua, $total_sewer, $subtotal, $tax, $total)) {
            return new Importlog_Utilities([
                'token' => $token,
                'residencia' => $residencia,
                'room' => $room,
                'owner' => $owner,
                'ocupacion' => $ocupacion,
                'kw' => $kw,
                'agua' => $agua,
                'gas' => $gas,
                'total_kw' => $total_kw,
                'total_kwfee' => $total_kwfee,
                'total_gas' => $total_gas,
                'total_gasfee' => $total_gasfee,
                'total_agua' => $total_agua,
                'total_sewer' => $total_sewer,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]);
        }
        // Retornar null para omitir la inserción de la fila de encabezado
        return null;
    }
}
