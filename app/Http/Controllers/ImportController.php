<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Traits\LogsActivity;
use App\Traits\SendsAzureNotificationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UtilitiesImport;
use App\Models\user;
use App\Models\imports;
use App\Models\period;
use App\Models\status;
use App\Models\residence;
use App\Models\cell;
use App\Models\Importlog_Utilities;
use App\Models\Utilities;
use App\Models\comment;
use App\Models\consumptionmetric;
use App\Models\parameters_res_utilities;
use App\Models\parameters_tc_utilities;

class ImportController extends Controller implements HasMiddleware
{

    use SendsAzureNotificationEmail;
    use LogsActivity;

    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('utilities.imports'), only: ['importexcel', 'selectimport', 'download_utilitiestmp1']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('utilities.listimports'), only: ['log_import', 'viewimport']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using(['utilities.checkmaintenance', 'utilities.cancel', 'utilities.checkfinance']), only: ['checkimport']),
        ];
    }

    #################################
    ##SUB MODULO SELECCIONAR EXCEL E IMPORTAR##
    ################################

    //***INDEX***/
    public function selectimport(Request $request)
    {
        $start_date = Carbon::parse($request->start_date);
        $end_date =  Carbon::parse($request->end_date);
        $current_date = Carbon::now();

        //sin capturar, en curso, capturado
        $status1 = Status::find(1);
        $status2 = Status::find(2);
        $status3 = Status::find(3);

        //periodo
        $period = Period::where('start', '=', $start_date->format('Y-m-d'))->with('status')
            ->where('end', '=', $end_date->format('Y-m-d'))
            ->get();

        //**CONSULTAR SI IMPORTACIÓN CON VERSION MAS RECIENTE DE UN PERIODO ESTA POR APROBAR O APROBADO**/
        $approve_import = Imports::where('period_id', $period->first()->id)
            ->whereIn('status_id', [4, 5])
            ->orderBy('version', 'desc')
            ->first();


        if ($approve_import === null) {

            //** REVISAR CONDICION PARA ENTRAR A ESTE MODULO**/
            //Primero: Verificar si fecha inicio y final que se esta comparando sean del mismo mes y año
            if ($start_date->month == $end_date->month && $start_date->year == $end_date->year) {
                //segundo: Ahora verificamos si son el primer y último día del mes
                if ($start_date->day == 1 && $end_date->day == $end_date->daysInMonth) {
                    //tercero: Verificar si el período que se está abriendo no sea mayor que la fecha actual
                    if ($current_date <=  $start_date) {
                        return view("error")->with('error', 'No se puede abrir un período que es mayor que la fecha actual.');
                    } else {
                        //cuarto: Verificar si el período esta en status 7
                        if (!$period->isEmpty()) {
                            if ($period->first()->status_id == 1 || $period->first()->status_id == 3) {
                                //se ha creado periodo en el modelo pero su estatus esta en 1 o 3
                                return view("error")->with('error', 'No se puede abrir este período porque su estado actual es: ' . $status1->name . ' o ' . $status3->name . '. Por favor, asegurece que este ' . $status2->name . '.');
                            } else {
                                //periodo no esta en status 1 o 3, entonces rederigir a vista subir importación
                                return view('selectimport', compact('start_date', 'end_date', 'period'));
                            }
                        } else {
                            //no se ha creado periodo en el modelo
                            return view("error")->with('error', 'No se puede abrir este período porque su estado actual es: ' . $status1->name . '. Por favor, cámbielo a ' . $status2->name . '.');
                        }
                    }
                } else {
                    return view("error")->with('error', 'Las fechas no son el primer y último día del mes o no estan en el mismo periodo anual y mensual.');
                }
            } else {
                return view("error")->with('error', 'Las fechas no son el primer y último día del mes o no estan en el mismo periodo anual y mensual.');
            }
        } else {

            return redirect()->back()->withErrors(['error' => 'Ya existe una importación previamente subida. Debe estar como no aprobada o cancelada para poder subir otra versión corregida.']);
        }
    }

    //***DESCARGAR PLANTILLA***/
    public function download_utilitiestmp1(Request $request)
    {
        $filePath = 'public/fulltemplate_utilities.xlsx';
        return Storage::download($filePath);
    }

    //***LEER HOJAS DE EXCEL***/
    public function readsheets(Request $request)
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($request->file('documento'));
        // Obtener los nombres de todas las hojas en el archivo Excel
        $sheetNames = $spreadsheet->getSheetNames();
        return response()->json(['sheetnames' => $sheetNames]);
    }

    //***SUBIR IMPORTACIÓN***///
    public function importexcel(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            //***0 - Generar un token***/
            $token = str::random(20);

            //***1 - Obtener periodo y actualizar tipo de importacion***/
            $period = Period::where('start', '=', $request->start)
                ->where('end', '=', $request->end)
                ->first();
            if ($period) {
                $period->templateutilities = $request->checktemplate == 1 ? 'onlyconsumption' : ($request->checktemplate == 2 ? 'fulltemplate' : $period->templateutilities);
                $period->save();
            }

            //***2 - CONSULTAR SI IMPORTACIÓN CON VERSION MAS RECIENTE DE UN PERIODO ESTA POR APROBAR O APROBADO: PERMITIR SUBIR OTRA VERSION***/
            $approve_import = Imports::where('period_id', $period->id)
                ->whereIn('status_id', [4, 5])
                ->orderBy('version', 'desc')
                ->first();

            if ($approve_import == null) {

                //***3 - SUBIR IMPORTACION A TABLA: IMPORTLOG_UTILITIES ***/
                $data = $request->excelData;
                $numRecordsBeforeImport = Importlog_Utilities::count();

                foreach ($data as $idx => $row) {
                    // Inicializamos un array con las primeras 8 columnas (token, residencia, etc.)
                    $importData = [
                        'token' => $token,
                        'residencia' => $row[0],
                        'room' => $row[1] ?? 0,
                        'owner' => $row[2],
                        'ocupacion' => $row[3],
                        'kw' => $row[4],
                        'agua' => $row[5],
                        'gas' => $row[6]
                    ];
                    if ($request->checktemplate == 2) {
                        $importData += [
                            'total_kw' => $row[7],
                            'total_kwfee' => $row[8],
                            'total_gas' => $row[9],
                            'total_gasfee' => $row[10],
                            'total_agua' => $row[11],
                            'total_sewer' => $row[12],
                            'subtotal' => $row[13],
                            'tax' => $row[14],
                            'total' => $row[15]
                        ];
                    }

                    // Crear el registro de importación en la base de datos
                    Importlog_Utilities::create($importData);
                }
                $numRecordsAfterImport = Importlog_Utilities::count();
                $numImportedRecords = $numRecordsAfterImport - $numRecordsBeforeImport;


                //***4 - DATOS GENERALES DE IMPORTACION EN TABLA: IMPORTS***/
                $highestVersion = Imports::where('period_id', $period->id)->max('version');
                // Establecer la nueva versión incrementada
                $version = ($highestVersion ? $highestVersion + 1 : 1);
                // Crear el nuevo registro con la versión incrementada
                $newImport = new Imports([
                    'period_id' => $period->id,
                    'importlog_tb' => 'importlog_utilities',
                    'import_token' => $token,
                    'version' => $version,
                    'status_id' => 4, // Por aprobar
                    'uploaded_by' => Auth::id(),
                ]);
                $newImport->save();

                // Confirmar la transacción si todo ha ido bien
                if ($numImportedRecords > 0) {
                    //***5 - Auditar registro***/
                    $dateperiod = Carbon::parse($period->start)->locale('es_ES')->isoFormat('MMMM YYYY'); //mes, año
                    $this->logActivity(
                        'create',
                        'Utilities',
                        'Periodos | Subir consumos',
                        'Subio una importación plantilla utilities [v' . $version . ']. Periodo ' .  ucfirst($dateperiod),
                        'imports',
                        null,
                        $newImport
                    );

                    //***6 - mandar notificacion***/
                    $notificacion = 'utilities.importupload';
                    $this->sendNotification($notificacion, $dateperiod, null, $version);

                    DB::commit();
                    // Si se registraron datos, retornar un mensaje de éxito
                    return response()->json(['message' => 'Importacion exitosa. Se registraron ' . $numImportedRecords . ' registros.']);
                } else {
                    // Si no se registraron datos, retornar un mensaje indicando el problema
                    return response()->json(['error' => 'No se registraron datos en la tabla. Revise el archivo importado o los datos de entrada.'], 500);
                }
            } else {
                return response()->json(['error' => 'No se pueden subir más datos en este periodo porque ya hay datos en proceso de revisión o aprobados.'], 500);
            }
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            // Captura cualquier excepción y maneja el error
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    ///***FUNCIONALIDADES: TIPOS DE VALIDACION PLANTILLA***/

    protected function validateSheetExists(Spreadsheet $spreadsheet, string $sheetName)
    {
        $sheetName = trim($sheetName);
        $sheet = $spreadsheet->getSheetByName($sheetName);
        if ($sheet === null) {
            throw new \Exception("La hoja con el nombre '$sheetName' no existe. Verifica si hay espacios al final o al inicio del nombre");
        }
    }


    //**PROCESAR VALIDACIONES EN HOJA EXCEL***/
    public function process_sheet(Request $request)
    {
        $sheetName = $request->sheetname;
        $reader = new Xlsx();
        $spreadsheet = $reader->load($request->file('documento'));

        // Definir los encabezados requeridos según el template
        $requiredHeadersTemplate1 = [
            'residencia',
            'room',
            'owner',
            'ocupacion',
            'kws',
            'aguam3',
            'gasm3'
        ];
        $requiredHeadersTemplate2 = [
            'residencia',
            'room',
            'owner',
            'ocupacion',
            'kws',
            'aguam3',
            'gasm3',
            'kw',
            'kwfee',
            'propane',
            'propanefee',
            'water',
            'sewer',
            'subtotal',
            'tax',
            'total'
        ];

        // Seleccionar los encabezados requeridos según el template
        $requiredHeaders = $request->template == 1 ? $requiredHeadersTemplate1 : $requiredHeadersTemplate2;
        //data residencias segun el periodo
        $residencesoinperiod = parameters_res_utilities::where('period_id', $request->period)->distinct()->pluck('residence_id')->toArray();
        $residences = residence::whereIn('id', $residencesoinperiod)->get();
        $numResidences = $residences->count();
        $dbResidences = $residences->pluck('name')->toArray();

        $responses = [];
        $excelData = [];

        try {
            switch ($request->validationIndex) {
                case 1:
                    $this->validateSheetExists($spreadsheet, $sheetName);
                    $responses[] = ['status' => 'success', 'message' => 'Completado'];
                    break;
                case 2:
                    $this->validateHeaders($spreadsheet, $sheetName, $requiredHeaders);
                    $responses[] = ['status' => 'success', 'message' => 'Completado'];
                    break;
                case 3:
                    $this->validateColumnOrder($spreadsheet, $sheetName, $requiredHeaders);
                    $responses[] = ['status' => 'success', 'message' => 'Completado'];
                    break;
                case 4:
                    $this->validateRowCount($spreadsheet, $sheetName, $numResidences);
                    $responses[] = ['status' => 'success', 'message' => 'Completado'];
                    break;
                case 5:
                    $this->validateResidences($spreadsheet, $sheetName, $dbResidences);
                    $responses[] = ['status' => 'success', 'message' => 'Completado'];
                    break;
                case 6:
                    $this->validateDataRows($spreadsheet, $sheetName, $numResidences, $requiredHeaders);
                    $responses[] = ['status' => 'success', 'message' => 'Completado'];

                    // Leer los datos del Excel
                    $sheetData = $spreadsheet->getActiveSheet()->toArray();
                    // Limitar a las columnas necesarias y detenerse si la primera columna está vacía
                    foreach (array_slice($sheetData, 1) as $row) {
                        $row = array_slice($row, 0, count($requiredHeaders));
                        // Detener el bucle si el primer índice de una fila está vacío
                        if (empty($row[0])) {
                            break;
                        }
                        // Limpiar y ajustar los datos, excepto la primera columna
                        foreach ($row as $index => &$cell) {
                            if ($index > 2) { // Excluir las 2 primeras columnas (residencia y room)
                                if (is_string($cell)) {
                                    // Limpiar comas y espacios
                                    $cell = str_replace(',', '', trim($cell));
                                    // Reemplazar valores que sean vacíos, 'NaN', o símbolos con 0
                                    if ($cell === '' || strtolower($cell) === 'nan') {
                                        $cell = 0;
                                    }
                                }
                                // Convertir a número si es posible
                                if (is_numeric($cell)) {
                                    $cell = (float)$cell; // o (int)$cell si prefieres enteros
                                } else {
                                    $cell = 0; // Si no es numérico, lo ponemos a 0
                                }
                            }
                        }
                        $excelData[] = $row;
                    }

                    break;
                default:
                    throw new \Exception('Índice de validación no válido.');
            }
        } catch (\Exception $e) {
            $responses[] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        return response()->json([
            'validations' => $responses,
            'excelData' =>  $excelData
        ]);
    }

    protected function validateHeaders(Spreadsheet $spreadsheet, string $sheetName, array $requiredHeaders)
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $headerRow = $sheet->rangeToArray('A1:' . chr(64 + count($requiredHeaders)) . '1')[0];
        $normalizedHeaderRow = array_map([$this, 'normalizeString'], $headerRow);
        $missingHeaders = array_diff($requiredHeaders, $normalizedHeaderRow);
        if (!empty($missingHeaders)) {
            $missingHeadersOriginal = array_intersect_key($requiredHeaders, array_flip(array_keys($missingHeaders)));
            throw new \Exception('Faltan las siguientes columnas: ' . implode(', ', $missingHeadersOriginal));
        }
    }

    protected function validateColumnOrder(Spreadsheet $spreadsheet, string $sheetName, array $requiredHeaders)
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $headerRow = $sheet->rangeToArray('A1:' . chr(64 + count($requiredHeaders)) . '1')[0];
        $normalizedHeaderRow = array_map([$this, 'normalizeString'], $headerRow);
        foreach ($requiredHeaders as $index => $header) {
            if ($normalizedHeaderRow[$index] !== $header) {
                throw new \Exception("La columna '{$requiredHeaders[$index]}' debe estar en la posición " . ($index + 1) . ".");
            }
        }
    }


    protected function validateRowCount(Spreadsheet $spreadsheet, string $sheetName, int $numResidences)
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $highestRow = $sheet->getHighestRow();
        $dataRows = 0;
        for ($row = 2; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell("A$row")->getValue();
            if (!empty($cellValue)) {
                $dataRows++;
            }
        }
        if ($dataRows !== $numResidences) {
            throw new \Exception("El archivo debe tener exactamente $numResidences filas de datos, pero tiene $dataRows.");
        }
    }

    protected function validateResidences(Spreadsheet $spreadsheet, string $sheetName, array $dbResidences)
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);
        $highestRow = $sheet->getHighestRow();
        $importedResidences = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell("A$row")->getValue();
            if (!empty($cellValue)) {
                $importedResidences[] = $cellValue;
            }
        }
        $missingInExcel = array_diff($dbResidences, $importedResidences);
        $extraInExcel = array_diff($importedResidences, $dbResidences);
        if (count($missingInExcel) > 0 || count($extraInExcel) > 0) {
            $message = '';
            if (count($missingInExcel) > 0) {
                $message .= "Residencias faltantes en el archivo Excel, con respecto al periodo: " . implode(', ', $missingInExcel) . ". ";
            }
            if (count($extraInExcel) > 0) {
                $message .= "Residencias adicionales en el archivo Excel, con respecto al periodo: " . implode(', ', $extraInExcel) . ". ";
            }
            throw new \Exception($message);
        }
    }

    protected function validateDataRows(Spreadsheet $spreadsheet, string $sheetName, int $numResidences, array $requiredHeaders)
    {
        $sheet = $spreadsheet->getSheetByName($sheetName);

        //filas leidas en el archivo, para que sea igual a $numresidences 
        $highestRow = min($sheet->getHighestRow(), $numResidences + 1); //+1 para sumarle diferencia por el encabezado

        // Comienza el recorrido de indices desde la fila 2 de datos leidos
        for ($row = 2; $row < $highestRow; $row++) {
            $rowData = [];
            for ($col = 'A'; $col <= chr(64 + count($requiredHeaders)); $col++) {
                $rowData[] = $sheet->getCell("$col$row")->getCalculatedValue();
            }
            // Validar columna 3 (índice 2)
            if ($rowData[2] === null || $rowData[2] === '') {
                throw new \Exception("El valor en la columna 3, fila " . ($row) . " no puede estar vacío. Valor encontrado: '{$rowData[2]}'.");
            }

            // Validar el resto de las columnas (índices 3 en adelante)
            for ($colIndex = 3; $colIndex < count($requiredHeaders); $colIndex++) {
                $cellValue = str_replace(',', '', $rowData[$colIndex]);
                if ($cellValue === null || $cellValue === '' || !is_numeric($cellValue)) {
                    throw new \Exception("El valor en la columna " . ($colIndex + 1) . ", fila " . ($row) . " debe ser un número y no puede estar vacío. Valor encontrado: '{$rowData[$colIndex]}'.");
                }
            }
        }
    }

    //Normaliza los nombres de las columnas eliminando acentos, convirtiendo a minúsculas y eliminando caracteres no alfabéticos.
    protected function normalizeString($string)
    {
        $unwantedArray = [
            'á' => 'a',
            'à' => 'a',
            'ä' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'é' => 'e',
            'è' => 'e',
            'ë' => 'e',
            'ê' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'ï' => 'i',
            'î' => 'i',
            'ó' => 'o',
            'ò' => 'o',
            'ö' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'ü' => 'u',
            'û' => 'u',
            'ñ' => 'n',
            'ç' => 'c'
        ];
        $string = strtolower($string);
        $string = strtr($string, $unwantedArray);
        $string = preg_replace('/[^a-z0-9 ]/', '', $string); // Permitimos letras, números y espacios
        return str_replace(' ', '', $string);
        return $string;
    }


    #################################
    ##MODULO LISTA IMPORTACIONES##
    ################################



    //**INDEX***/
    public function log_import(Request $request)
    {

        $year = $request->input('year');
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        // Verifica si el año solicitado es mayor al próximo año permitido
        if ($year >= $nextYear) {
            // Redirige con un mensaje de error
            return redirect()->back()->with('error', 'No podemos acceder a datos del año posterior al actual');
        }

        $year = $request->input('year');
        $dateNow = now();

        // Verificar si se proporcionó un año
        if (!empty($year)) {
            // Si se proporcionó un año, establecer la fecha al primer día del primer mes del año
            $dateNow = Carbon::create($year, 1, 1)->startOfMonth();
        }

        //importaciones utilities
        $imports = imports::with('period')
            ->with('status')
            ->with('uploadedBy')
            ->with('maintenanceApprovedBy')
            ->with('financeApprovedBy')
            ->whereHas('period', function ($query) use ($dateNow) {
                $query->whereYear('start', $dateNow);
            })
            ->where('importlog_tb', 'importlog_utilities')
            ->orderby('created_at', 'desc')
            ->orderby('version', 'desc')
            ->get();

        $metrics = consumptionmetric::first();
        return view('logimports', compact('imports', 'metrics', 'dateNow'));
    }

    //**LEER DATOS IMPORTACIÓN SELECCIONADA***/
    public function viewimport(Request $request)
    {

        try {
            //permisos
            $permissions = [
                'checkmaintenance' => auth()->user()->can('utilities.checkmaintenance'),
                'checkfinance' => auth()->user()->can('utilities.checkfinance'),
                'cancel' => auth()->user()->can('utilities.cancel'),
            ];
            $metrics = consumptionmetric::first();
            $token = $request->input('token');
            $import = imports::with('period')->with('status')->with('maintenanceApprovedBy')->with('financeApprovedBy')->where('import_token', $token)->get();
            $highestVersion = Imports::where('period_id', $import->first()->period_id)->max('version');
            $data = Importlog_Utilities::where('token', $token)->get();

            //datos de la tablas
            //caso: si fue plantilla completa los totales se extraen de ahi
            $template = $import->first()->period->templateutilities;
            if ($template == 'fullltemplate') {
                $data = $data->map(function ($item) {
                    $item->residencia;
                    $item->room;
                    $item->owners;
                    $item->ocupacion;
                    $item->kw = number_format($item->kw, 2, '.', ',');
                    $item->agua = number_format($item->agua, 2, '.', ',');
                    $item->gas = number_format($item->gas, 2, '.', ',');
                    $item->total_kw = '$' . number_format($item->total_kw, 2, '.', ',');
                    $item->total_kwfee = '$' . number_format($item->total_kwfee, 2, '.', ',');
                    $item->total_gas = '$' . number_format($item->total_gas, 2, '.', ',');
                    $item->total_gasfee = '$' . number_format($item->total_gasfee, 2, '.', ',');
                    $item->total_agua = '$' . number_format($item->total_agua, 2, '.', ',');
                    $item->total_sewer = '$' . number_format($item->total_sewer, 2, '.', ',');
                    $item->subtotal = '$' . number_format($item->subtotal, 2, '.', ',');
                    $item->tax = '$' . number_format($item->tax, 2, '.', ',');
                    $item->total = '$' . number_format($item->total, 2, '.', ',');
                    return $item;
                });
                //caso: si no fue plantilla completa, los totales se calculan en base a parametros del periodo
            } else {

                $period = $import->first()->period->id;
                $taxPercentage = parameters_tc_utilities::where('period_id', $period)->pluck('tax')->first();
                $tff = parameters_res_utilities::where('period_id', $period)
                    ->with('residence')
                    ->with('cost')
                    ->get();

                $data = $data->map(function ($item) use ($tff, $taxPercentage) {
                    $porcentaje = 10;
                    //recorrido busqueda costo kw
                    $param = $tff->firstWhere(function ($param) use ($item) {
                        return $param->residence->name === $item->residencia && $param->cost->service_id == 1;
                    });
                    $costkw = $param ? $param->cost->cost : 0;
                    //recorrido busqueda costo gas
                    $param = $tff->firstWhere(function ($param) use ($item) {
                        return $param->residence->name === $item->residencia && $param->cost->service_id == 2;
                    });
                    $costgas = $param ? $param->cost->cost : 0;
                    //recorrido busqueda costo agua
                    $param = $tff->firstWhere(function ($param) use ($item) {
                        return $param->residence->name === $item->residencia && $param->cost->service_id == 3;
                    });
                    $costagua = $param ? $param->cost->cost : 0;
                    //recorrido busqueda costo sewer
                    $param = $tff->firstWhere(function ($param) use ($item) {
                        return $param->residence->name === $item->residencia && $param->cost->service_id == 4;
                    });
                    $costsewer = $param ? $param->cost->cost : 0;

                    // variables de calculo
                    $kw = ($costkw > 0) ? ($item->kw * $costkw) : 0;
                    $kwfee = $kw * (1 + ($porcentaje / 100));
                    $gas = ($costgas > 0) ? ($item->gas * $costgas) : 0;
                    $gasfee = $gas * (1 + ($porcentaje / 100));
                    $agua = ($costagua > 0) ? ($item->agua * $costagua) : 0;
                    $sewer = ($costsewer > 0) ? ($item->agua * $costsewer) : 0;
                    $subtotal = ($kwfee + $gasfee + $agua + $sewer);
                    $tax = $subtotal * ($taxPercentage / 100);
                    $total = ($subtotal + $tax);

                    //generar columas informativas de calculo
                    $item->calculo_total_kw = number_format($item->kw, 2) . " * " . $costkw;
                    $item->calculo_total_kwfee = number_format($kw, 2) . " * " . (1 + ($porcentaje / 100));
                    $item->calculo_total_gas = number_format($item->gas, 2) . " * " . $costgas;
                    $item->calculo_total_gasfee = number_format($gas, 2) . " * " . (1 + ($porcentaje / 100));
                    $item->calculo_total_agua = number_format($item->agua, 2) . " * " . $costagua;
                    $item->calculo_total_sewer = number_format($item->agua, 2) . " * " . $costsewer;
                    $item->calculo_subtotal = number_format($kwfee, 2) . "(kwfee)" . " + " . number_format($gasfee, 2) . "(gasfee)" . " + " . number_format($agua, 2) . "(ttlagua)" . " + " . number_format($sewer, 2) . "(ttlsewer)";
                    $item->calculo_tax = number_format($subtotal, 2) . "(subtotal)" . " * " . ($taxPercentage / 100);
                    $item->calculo_total =  number_format($subtotal, 2) . "(subtotal)" . " + " . number_format($tax, 2) . "(tax)";
                    //generar columnas de tabla
                    $item->residencia;
                    $item->room;
                    $item->owners;
                    $item->ocupacion;
                    $item->kw = number_format($item->kw, 2, '.', ',');
                    $item->agua = number_format($item->agua, 2, '.', ',');
                    $item->gas = number_format($item->gas, 2, '.', ',');
                    $item->total_kw = '$' . number_format($kw, 2, '.', ',');
                    $item->total_kwfee = '$' . number_format($kwfee, 2, '.', ',');
                    $item->total_gas = '$' . number_format($gas, 2, '.', ',');
                    $item->total_gasfee = '$' . number_format($gasfee, 2, '.', ',');
                    $item->total_agua = '$' . number_format($agua, 2, '.', ',');
                    $item->total_sewer = '$' . number_format($sewer, 2, '.', ',');
                    $item->subtotal = '$' . number_format($subtotal, 2, '.', ',');
                    $item->tax = '$' . number_format($tax, 2, '.', ',');
                    $item->total = '$' . number_format($total, 2, '.', ',');
                    return $item;
                });
            }
            //comentarios de la tabla
            $hascomments = cell::whereIn('row', $data->pluck('id')->toArray())->select([
                DB::raw("CONCAT(row, '-', name_column) AS row") // Crea el alias 'row' combinando 'row_id' y 'name_colum'
            ])->get();

            return response()->json(array(
                "permissions" => $permissions,
                "import" => $import,
                "highestversion" => $highestVersion,
                "data" => $data,
                "hascomments" =>  $hascomments,
                "metrics" => $metrics
            ));
        } catch (\Throwable $th) {
            // Si hay una excepción, no guardar los cambios y devolver un mensaje de error.
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    ///***FUNCIONALIDAD APROBAR/RECHAZAR/CANCELAR IMPORTACIÓN ***///

    public function checkimport(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            // Validar permisos
            $permissionError = $this->validatePermission($request->department, $request->action);
            if ($permissionError) {
                return $permissionError; // Retornar error si no tiene permiso
            }
            // Continuar con el resto del código si tiene permiso

            //***variables para actualizar estado importaciones y periodos**/
            $import =  Imports::where('id', $request->idimport)->first();
            $highestVersion = Imports::where('period_id', $import->period_id)->max('version');
            $period = period::findOrFail($import->period_id);

            //variabes para registro actividad antes de posteriores cambios
            $before_import = clone $import;
            $before_period = clone $period;
            $dateperiod = Carbon::parse($period->start)->locale('es_ES')->isoFormat('MMMM YYYY'); //mes, año

            /***Actualizar los check de importacion mantenimiento y finanzas**/

            // Verificar si check fue mantenimiento
            if ($request->department === "maintenance") {


                $import->maintenance_action = $request->action;
                $import->maintenance_description = $request->description;
                $import->maintenance_aproved_by = Auth::id();
                $import->maintenance_aproved_date = now();
                $import->save();
                $import->refresh();

                //registrar actividad
                $status_import = ($request->action == "canceled") ? 'cancelo' : (($request->action == "disapproved") ? 'no aprobó' : (($request->action == "approved") ? 'aprobo' : 'Estado desconocido'));
                $this->logActivity(
                    'update',
                    'Utilities',
                    'Registro de importaciónes',
                    'Mantenimiento ' . $status_import . ' importación utilities [v' . $import->version . ']. Periodo ' .  ucfirst($dateperiod),
                    'imports',
                    $before_import,
                    $import
                );


                //mandar notificacion
                $notificacion = 'utilities.maintenancecheckimport';
                $this->sendNotification($notificacion, $dateperiod,  $status_import, $import->version);
            }

            // Verificar si check fue finanzas
            if ($request->department === "finance") {
                $import->finance_action = $request->action;
                $import->finance_description = $request->description;
                $import->finance_aproved_by = Auth::id();
                $import->finance_aproved_date = now();
                $import->save();
                $import->refresh();

                //registrar actividad
                $status_import = (($request->action == "disapproved") ? 'no aprobó' : (($request->action == "approved") ? 'aprobo' : 'Estado desconocido'));
                $this->logActivity(
                    'update',
                    'Utilities',
                    'Registro de importaciónes',
                    'Finanzas ' . $status_import . ' importación utilities [v' . $import->version . ']. Periodo ' .  ucfirst($dateperiod),
                    'imports',
                    $before_import,
                    $import
                );

                //mandar notificacion
                $notificacion = 'utilities.financecheckimport';
                $this->sendNotification($notificacion, $dateperiod, $status_import, $import->version);
            }


            /***De acuerdo a los check, actualizar estados del periodo e importación**/

            //Si mantemiento cancelo importación
            if ($import->maintenance_action == "canceled") {
                $import->status_id = 8; //importacion cancelado
                $import->finance_action = null;
                $import->finance_description = null;
                $import->finance_aproved_by = null;
                $import->finance_aproved_date = null;
                $period->status_id = 2; //periodo en progreso
                $period->token = null;  //token null en periodo
                //Borrar utilities si no se han aprobado(Si existe)
                Utilities::where('token', $import->import_token)->delete();
                $import->save();
                $period->save();
            }

            //Si mantenimiento o finanzas no aprovo importacion
            if ($import->maintenance_action == "disapproved" || $import->finance_action == "disapproved") {
                $import->status_id = 6; //importacion no aprobado
                $period->status_id = 2; //periodo en progreso
                $period->token = null;  //token null en periodo
                //Borrar utilities si no se han aprobado (Si existe)
                Utilities::where('token', $import->import_token)->delete();
                $import->save();
                $period->save();
            }



            //Si mantenimiento y finanzas aprovo importacion
            //Nta: Ya que importación quedaria aprobada modelo periodo guarda token importacion y a su vez datos importados en modelo importlog_utilities se traspasa a Utilities
            if ($import->maintenance_action == "approved" && $import->finance_action == "approved") {
                $import->status_id = 5; //importacion aprobado
                $period->status_id = 3; //periodo capturado
                $period->token = $import->import_token;
                $import->save();
                $period->save();


                // Consulta para obtener los registros de Importlog_Utilities
                $importLogUtilities = Importlog_Utilities::where('token', $import->import_token)->get();
                //plantilla solo consumos
                if ($request->typetemplate == "onlyconsumption") {
                    $taxPercentage = parameters_tc_utilities::where('period_id', $period->id)->pluck('tax')->first();
                    $tff = parameters_res_utilities::where('period_id', $period->id)
                        ->with('residence')
                        ->with('cost')
                        ->get();

                    $data = $importLogUtilities->map(function ($item) use ($tff, $taxPercentage) {
                        $porcentaje = 10;
                        //recorrido busqueda costo kw
                        $param = $tff->firstWhere(function ($param) use ($item) {
                            return $param->residence->name === $item->residencia && $param->cost->service_id == 1;
                        });
                        $costkw = $param ? $param->cost->cost : 0;
                        //recorrido busqueda costo gas
                        $param = $tff->firstWhere(function ($param) use ($item) {
                            return $param->residence->name === $item->residencia && $param->cost->service_id == 2;
                        });
                        $costgas = $param ? $param->cost->cost : 0;
                        //recorrido busqueda costo agua
                        $param = $tff->firstWhere(function ($param) use ($item) {
                            return $param->residence->name === $item->residencia && $param->cost->service_id == 3;
                        });
                        $costagua = $param ? $param->cost->cost : 0;
                        //recorrido busqueda costo sewer
                        $param = $tff->firstWhere(function ($param) use ($item) {
                            return $param->residence->name === $item->residencia && $param->cost->service_id == 4;
                        });
                        $costsewer = $param ? $param->cost->cost : 0;

                        // variables de calculo
                        $kw = ($costkw > 0) ? ($item->kw * $costkw) : 0;
                        $kwfee = $kw * (1 + ($porcentaje / 100));
                        $gas = ($costgas > 0) ? ($item->gas * $costgas) : 0;
                        $gasfee = $gas * (1 + ($porcentaje / 100));
                        $agua = ($costagua > 0) ? ($item->agua * $costagua) : 0;
                        $sewer = ($costsewer > 0) ? ($item->agua * $costsewer) : 0;
                        $subtotal = ($kwfee + $gasfee + $agua + $sewer);
                        $tax = $subtotal * ($taxPercentage / 100);
                        $total = ($subtotal + $tax);
                        //generar columnas de tabla
                        $item->residencia;
                        $item->token;
                        $item->room;
                        $item->owners;
                        $item->ocupacion;
                        $item->kw = $item->kw;
                        $item->agua = $item->agua;
                        $item->gas = $item->gas;
                        $item->total_kw = $kw;
                        $item->total_kwfee = $kwfee;
                        $item->total_gas = $gas;
                        $item->total_gasfee = $gasfee;
                        $item->total_agua = $agua;
                        $item->total_sewer = $sewer;
                        $item->subtotal = $subtotal;
                        $item->tax = $tax;
                        $item->total = $total;
                        return $item;
                    });


                    // Iterar sobre los registros y guardarlos en el modelo Utilities
                    $utilities = $data->map(function ($importLogUtility) {
                        return [
                            'residencia' => $importLogUtility->residencia,
                            'token' => $importLogUtility->token,
                            'room' => $importLogUtility->room,
                            'owner' => $importLogUtility->owner,
                            'ocupacion' => $importLogUtility->ocupacion,
                            'kw' => $importLogUtility->kw,
                            'agua' => $importLogUtility->agua,
                            'gas' => $importLogUtility->gas,
                            'total_kw' => $importLogUtility->total_kw,
                            'total_kwfee' => $importLogUtility->total_kwfee,
                            'total_gas' => $importLogUtility->total_gas,
                            'total_gasfee' => $importLogUtility->total_gasfee,
                            'total_agua' => $importLogUtility->total_agua,
                            'total_sewer' => $importLogUtility->total_sewer,
                            'subtotal' => $importLogUtility->subtotal,
                            'tax' => $importLogUtility->tax,
                            'total' => $importLogUtility->total,
                        ];
                    });
                    Utilities::insert($utilities->toArray());
                } else
                //plantilla completa
                {
                    $utilities = $importLogUtilities->map(function ($importLogUtility) {
                        return [
                            'residencia' => $importLogUtility->residencia,
                            'token' => $importLogUtility->token,
                            'room' => $importLogUtility->room,
                            'owner' => $importLogUtility->owner,
                            'ocupacion' => $importLogUtility->ocupacion,
                            'kw' => $importLogUtility->kw,
                            'agua' => $importLogUtility->agua,
                            'gas' => $importLogUtility->gas,
                            'total_kw' => $importLogUtility->total_kw,
                            'total_kwfee' => $importLogUtility->total_kwfee,
                            'total_gas' => $importLogUtility->total_gas,
                            'total_gasfee' => $importLogUtility->total_gasfee,
                            'total_agua' => $importLogUtility->total_agua,
                            'total_sewer' => $importLogUtility->total_sewer,
                            'subtotal' => $importLogUtility->subtotal,
                            'tax' => $importLogUtility->tax,
                            'total' => $importLogUtility->total,
                        ];
                    });
                    Utilities::insert($utilities->toArray());
                }

                //mandar notificacion
                $notificacion = 'utilities.close';
                $this->sendNotification($notificacion, $dateperiod,  $status_import, $import->version);
            }

            //refrescar datos y relaciones 
            $import->refresh();
            $period->refresh();


            /***Registrar el resultado final de los cambios de estado de periodo antes y despues (si es que hubo)**/
            if ($before_period->status_id != $period->status_id) {
                $dateperiod = Carbon::parse($period->start)->locale('es_ES')->isoFormat('MMMM YYYY'); //mes, año
                $this->logActivity(
                    'update',
                    'Utilities',
                    'Registro de importaciónes',
                    'Se actualizo el periodo ' .  ucfirst($dateperiod),
                    'periods',
                    $before_period,
                    $period
                );
            }


            /***Devolver respuesta JSON del resultado**/
            $importData = [
                'id' => $import->id,
                'version' => $import->version,
                'highestversion' => $highestVersion,
                'maintenance_action' => $import->maintenance_action,
                'finance_action' => $import->finance_action,
                'created_at' => $import->created_at,
                'updated_at' => $import->updated_at,
                'status' => [
                    'name' => $import->status->name,
                    'class' => $import->status->class,
                ],
                'uploadedBy' => [
                    'name' => $import->uploadedBy ? $import->uploadedBy->name : null,
                    'last_name' => $import->uploadedBy ? $import->uploadedBy->last_name : null,
                ],
                'maintenanceApprovedBy' => [
                    'name' => $import->maintenanceApprovedBy ? $import->maintenanceApprovedBy->name : null,
                    'last_name' => $import->maintenanceApprovedBy ? $import->maintenanceApprovedBy->last_name : null,
                ],
                'financeApprovedBy' => [
                    'name' => $import->financeApprovedBy ? $import->financeApprovedBy->name : null,
                    'last_name' => $import->financeApprovedBy ? $import->financeApprovedBy->last_name : null,
                ]
            ];
            $periodData = [
                'start' => $period->start
            ];

            $permissions = [
                'checkmaintenance' => auth()->user()->can('utilities.checkmaintenance'),
                'checkfinance' => auth()->user()->can('utilities.checkfinance'),
                'cancel' => auth()->user()->can('utilities.cancel'),
            ];


            // Confirmar la transacción si todo ha ido bien
            DB::commit();
            return response()->json(array(
                'message' => 'Datos actualizados correctamente',
                "import" =>  $importData,
                "period" =>  $periodData,
                "permissions" => $permissions
            ));
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            // Si hay una excepción, no guardar los cambios y devolver un mensaje de error.
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function validatePermission($department, $action)
    {
        $user = Auth::user();

        if ($department === "maintenance") {
            if ($action == "canceled" && !$user->can('utilities.cancel')) {
                return response()->json(['error' => 'No tienes permiso para cancelar como mantenimiento.'], 403);
            }

            if (($action == "disapproved" || $action == "approved") && !$user->can('utilities.checkmaintenance')) {
                return response()->json(['error' => 'No tienes permiso para aprobar o desaprobar como mantenimiento.'], 403);
            }
        }

        if ($department === "finance") {
            if (($action == "disapproved" || $action == "approved") && !$user->can('utilities.checkfinance')) {
                return response()->json(['error' => 'No tienes permiso para aprobar o desaprobar como finanzas.'], 403);
            }
        }

        return null; // No hay errores de permiso
    }

    ///***FUNCIONALIDADES COMENTARIOS EN CELDAS***/


    public function readcommentsIntable(Request $request)
    {
        try {
            $data = cell::with('comments.user')->where('row', $request->row)->where('name_column', $request->column)->get();
            $transformedData = $data->map(function ($cell) {
                return [
                    'id' => $cell->id,
                    'row' => $cell->row,
                    'name_column' => $cell->name_column,
                    'name_table' => $cell->name_table,
                    'created_at' => $cell->created_at,
                    'updated_at' => $cell->updated_at,
                    'comments' => $cell->comments->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'user_id' => $comment->user_id,
                            'comment' => $comment->comment,
                            'created_at' => $comment->created_at,
                            'updated_at' => $comment->updated_at,
                            'user' => [
                                'name' => $comment->user->name,
                                'last_name' => $comment->user->last_name,
                                'email' => $comment->user->email, // If you need to include email
                                'username' => $comment->user->username,
                                'avatar_class' => $comment->user->avatar_class,
                                'alias' =>  substr($comment->user->name, 0, 1) . '' . substr($comment->user->last_name, 0, 1),
                            ]
                        ];
                    }),
                ];
            });
            return response()->json(array("data" => $transformedData));
        } catch (\Throwable $th) {
            // Si hay una excepción, no guardar los cambios y devolver un mensaje de error.
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function savecommentIntable(Request $request)
    {
        try {

            $comment = comment::find($request->id);
            $before = clone $comment;  //capturar modelo antes de actualizar
            if ($comment->user_id != Auth::user()->id) {
                return response()->json(['error' => 'No actualizado. No puedes editar comentarios hechos por otros usuarios.']);
            } else {
                $comment->comment = $request->comment;
                $comment->save();
                $comment->refresh();

                // Registrar la actividad
                $this->logActivity(
                    'update',
                    'Utilities',
                    'Comentarios',
                    'Actualizó un comentario',
                    'comments',
                    $before,
                    $comment
                );

                return response()->json(['message' => 'Comentario guardado']);
            }
        } catch (\Throwable $th) {
            // If an exception occurs, do not save changes and return an error message.
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function newcommentIntable(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos

        try {
            // Buscar o crear la celda
            $cell = Cell::where([
                'row' => $request->row,
                'name_column' => $request->column,
                'name_table' => 'importlog_utilities',
            ])->first();

            if (!$cell) {
                $cell = Cell::create([
                    'row' => $request->row,
                    'name_column' => $request->column,
                    'name_table' => 'importlog_utilities',
                ]);
            }

            // Crear un nuevo comentario
            $comment = new Comment([
                'user_id' => Auth::id(),
                'comment' => $request->comment
            ]);
            $comment->save();

            // Asociar el comentario con la celda
            $comment->cell()->attach($cell->id);

            // Preparar los datos después de la creación
            $after = [
                'cells' => [
                    'id' => $cell->id,
                    'row' => $cell->row,
                    'name_column' => $cell->name_column,
                    'name_table' => $cell->name_table,
                    'created_at' => $cell->created_at,
                    'updated_at' => $cell->updated_at,
                ],
                'comment' => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                ],
                'user' => [
                    'name' => Auth::user()->name,
                    'last_name' => Auth::user()->last_name,
                    'email' => Auth::user()->email,
                    'username' => Auth::user()->username,
                    'avatar_class' => Auth::user()->avatar_class,
                    'alias' => substr(Auth::user()->name, 0, 1) . substr(Auth::user()->last_name, 0, 1),
                ]
            ];

            // Registrar la actividad
            $this->logActivity(
                'create',
                'Utilities',
                'Registro de importaciones',
                'Creó un nuevo comentario en una celda',
                'comments',
                null,
                $after
            );

            DB::commit();
            return response()->json(['message' => 'Nuevo comentario agregado en la celda.', 'data' => $after]);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            // Captura cualquier excepción y maneja el error
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function destroycommentIntable(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos

        try {
            // Encuentra el comentario y carga la celda asociada en una sola consulta
            $comment = Comment::with('cell')->find($request->id);

            if (!$comment) {
                return response()->json(['error' => 'Comentario no encontrado.'], 404);
            }

            $cell = $comment->cell->first();

            // Guardar información antes de la eliminación
            $before = [
                'comments' => [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                ],
                'cells' => $cell ? [
                    'id' => $cell->id,
                    'row' => $cell->row,
                    'name_column' => $cell->name_column,
                    'name_table' => $cell->name_table,
                ] : null,
            ];
            // Eliminar el comentario
            $comment->delete();

            // Registrar la actividad
            $this->logActivity(
                'delete',
                'Utilities',
                'Registro de importaciones',
                'Eliminó un comentario en una celda',
                'comments',
                $before,
                null
            );

            // Verifica si la celda tiene más comentarios
            if ($cell && $cell->comments()->count() === 0) {
                $beforeCell = [
                    'cells' => [
                        'id' => $cell->id,
                        'row' => $cell->row,
                        'name_column' => $cell->name_column,
                        'name_table' => $cell->name_table,
                        'created_at' => $cell->created_at,
                        'updated_at' => $cell->updated_at,
                    ],
                ];

                // Registrar la actividad para la celda
                $this->logActivity(
                    'delete',
                    'Utilities',
                    'Registro de importaciones',
                    'Se ha eliminado la celda contenedora de comentarios al no tener ninguno asociado.',
                    'cells',
                    $beforeCell,
                    null
                );

                // Eliminar la celda si no quedan comentarios
                $cell->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Comentario eliminado correctamente de la celda.']);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            // Captura cualquier excepción y maneja el error
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    ##########################
    ##FUNCION GENERAL
    ############################

    ///****FUNCIONALIDAD NOTIFICACION POR CORREO ***///
    private function sendNotification($notificacion, $date = null, $status = null, $version = null)
    {
        try {
            // Definir variables predeterminadas
            $subject = '';
            $message = '';
            $users = User::whereNotNull('email')
                ->with(['roles', 'notifications'])
                ->get();
            $filteredUsers = $users->filter(function ($user) use ($notificacion){
                // Verificar que el usuario no tenga el rol "Suspendido"
                $hasNoSuspendedRole = !$user->hasRole('Suspendido');
                //filtrar si tiene asignado notificación
                $hasUtilitiesOpenNotification = $user->notifications->contains('name', $notificacion);
                // Combinar ambas condiciones
                return $hasNoSuspendedRole && $hasUtilitiesOpenNotification;
            });

            //contenido email según el tipo de notificación
            switch ($notificacion) {
                case 'utilities.importupload':
                    $subject = 'Utilities | Importación subida';
                    $message = sprintf(
                        'El usuario %s %s ha subido un archivo de datos correspondientes al periodo: %s. En espera de su revisión (v%s).',
                        Auth::user()->name,
                        Auth::user()->last_name,
                        ucfirst($date),
                        $version
                    );
                    break;

                case 'utilities.maintenancecheckimport':
                    $info = ($status === 'aprobo') ? '' : 'Se habilita opción para subir datos nuevamente.';
                    $subject = 'Utilities | Mantenimiento realizó una acción en tabla importaciones';
                    $message = sprintf(
                        'El usuario %s %s %s importación (v%s). Periodo %s. %s',
                        Auth::user()->name,
                        Auth::user()->last_name,
                        $status,
                        $version,
                        ucfirst($date),
                        $info
                    );
                    break;

                case 'utilities.financecheckimport':
                    $info = ($status === 'aprobo') ? '' : 'Se habilita opción para subir datos nuevamente.';
                    $subject = 'Utilities | Finanzas realizó una acción en tabla importaciones';
                    $message = sprintf(
                        'El usuario %s %s %s importación (v%s). Periodo %s. %s',
                        Auth::user()->name,
                        Auth::user()->last_name,
                        $status,
                        $version,
                        ucfirst($date),
                        $info
                    );
                    break;

                case 'utilities.close':
                    $subject = 'Utilities | Periodo cerrado';
                    $message = sprintf(
                        'Se han aprobado entre departamentos datos del periodo %s. Se cierra el mes y se habilita la descarga de recibos.',
                        ucfirst($date)
                    );
                    break;

                default:
                    // Notificación no válida
                    Log::warning('Tipo de notificación no válido: ' . $notificacion);
                    return;
            }

            // Enviar el correo
            $this->sendNotificationEmail($subject, $message, $filteredUsers);
        } catch (\Exception $e) {
            // Registrar un error en los logs si falla el envío de correos
            Log::error('Error al enviar notificación por correo: ' . $e->getMessage() . ' en archivo ' . $e->getFile() . ' en línea ' . $e->getLine());
            // Continuar con el proceso sin interrumpir la ejecución
        }
    }
}
