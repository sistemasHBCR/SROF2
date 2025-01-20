<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\rate;
use App\Models\residence;
use App\Models\service;
use App\Models\status;
use App\Models\period;
use App\Models\parameters_costs_utilities;
use App\Models\parameters_res_utilities;
use App\Models\parameters_tc_utilities;

class ParametersController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $year = $request->input('year');
        $period = $request->input('period');
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        // Verifica si el año solicitado es mayor al próximo año permitido
        if ($year >= $nextYear) {
            // Redirige con un mensaje de error
            return redirect()->back()->with('error', 'No podemos acceder a datos del año posterior al actual');
        }

        $dateNow = now();
        // Verificar si se proporcionó un año
        if (!empty($year)) {
            // Si se proporcionó un año, establecer la fecha al primer día del primer mes del año
            $dateNow = Carbon::create($year, 1, 1)->startOfMonth();
        }

        Carbon::setLocale('es'); // Configurar Carbon para usar español
        $months = [];
        $periods = [];
        // fechas diciembre año anterior, y meses con respecto al año actual/ $dateNow
        for ($i = 0; $i <= 12; $i++) {
            $monthStart = Carbon::createFromDate($dateNow->year, $i, 1);
            $monthEnd = $monthStart->endOfMonth();

            // Inicializamos el mes en el array $months
            $monthData = [
                'target' => strtolower($monthStart->translatedFormat('F')),
                'number' => $i,
                'title' => ucfirst(strtolower($monthStart->translatedFormat('F'))),
                'start_date' => $monthStart->copy()->startOfMonth()->format('d/m/Y'),
                'end_date' => $monthEnd->format('d/m/Y'),
            ];
            $months[] = $monthData;

            // Obtener los periodos registrados para este mes
            $period = Period::where('start', '=', $monthStart->copy()->startOfMonth()->format('Y-m-d'))
                ->where('end', '=', $monthEnd->format('Y-m-d'))
                ->with('status') // Cargar la relación status
                ->first();  // Usamos `first()` para obtener un solo periodo por mes

            // Si no existe un periodo para este mes, agregar un array vacío
            if (!$period) {
                $periods[] = [];
            } else {
                $periods[] = $period->toArray();
            }
        }

        //Filtrar los periodos con el status de la relación igual a 3 y el status de parametersutilities igual a 1
        $monthsWithParameters = collect($periods)->filter(function ($period) {
            // Verificar que el 'status_id' sea igual a 3
            $statusId = $period['status_id'] ?? null;
            // Verificar que 'dataparameters["status"]' sea igual a 1
            $parametersutilities = $period['parametersutilities'] ?? null;
            // Filtramos si ambos valores cumplen con las condiciones
            return $statusId == 3 && $parametersutilities == 1;
        });
        $monthsWithParametersGrouped = $monthsWithParameters->groupBy(function ($period) {
            return \Carbon\Carbon::parse($period['start'])->year;  // Agrupar por año
        });

        //sin capturar, en curso, capturado.
        $status1 = Status::select('id', 'name', 'class')->find(1);
        $status2 = Status::select('id', 'name', 'class')->find(2);
        $status3 = Status::select('id', 'name', 'class')->find(3);

        $residences = Residence::with('owner')->orderby('name')->get();
        $services = Service::all();
        $rates = rate::all();


        return view('parameters', compact('residences', 'services', 'rates', 'months', 'dateNow', 'periods', 'monthsWithParametersGrouped', 'status1',  'status2', 'status3'));
    }


    public function load_costs_inperiod(Request $request)
    {
        $period = $request->period;

        //++++++++++datos de parametros tc++++++++++++++
        $qry1 = parameters_tc_utilities::where('period_id', $period)->select('tc', 'tax')->get();

        //++++++++datos de parametros costos++++++++++
        $qry2 = parameters_costs_utilities::where('parameters_costs_utilities.period_id', $period)
            ->select(
                'services.name as servicename',
                'services.id as serviceid',
                'services.volume as servicevolume',
                'services.code as servicecode',
                'services.icon as serviceicon',
                'services.class as serviceclass',
                'parameters_costs_utilities.period_id as period',
                'parameters_costs_utilities.volume_code as volume_code',
                'parameters_costs_utilities.volume as volume',
                'parameters_costs_utilities.cost_formula as cost_formula',
                'parameters_costs_utilities.cost as cost'
            )
            ->join('parameters_tc_utilities', 'parameters_costs_utilities.period_id', '=', 'parameters_tc_utilities.period_id')
            ->join('services', 'parameters_costs_utilities.service_id', '=', 'services.id')
            ->orderby('services.id')
            ->get();

        // Inicializa un array para almacenar los resultados agrupados
        $groupedData = [];
        // Agrupa los datos por servicecode
        foreach ($qry2 as $item) {
            $code = $item['servicecode'];

            // Si el código no existe en el array, inicializa un array vacío
            if (!isset($groupedData[$code])) {
                $groupedData[$code] = [];
            }

            // Agrega el item al grupo correspondiente
            $groupedData[$code][] = $item;
        }

        // Ordena cada grupo por volume_code
        foreach ($groupedData as $code => &$items) {
            usort($items, function ($a, $b) {
                return strcmp($a['volume_code'], $b['volume_code']);
            });
        }

        //++++++++datos de tarifa por residencia+++++++++++
        $residences = Residence::select(
            'parameters_res_utilities.id as id',
            'parameters_res_utilities.id as pruid',
            'parameters_res_utilities.rate_id as rateid',
            'parameters_res_utilities.fixedrate_value as fixedrate',
            'parameters_res_utilities.parameters_cost_id as costid',
            'parameters_res_utilities.fixedrate_isconditional as isconditional',
            'parameters_res_utilities.consumptionlower_is as ifislower',
            'parameters_res_utilities.flaterate_consumption_islower as ratevaluenew',
            'parameters_costs_utilities.volume_code as codevolume',
            'rates.name as ratename',
            'services.name as servicename',
            'services.volume as servicevolume',
            'residences.name as residencename',
            'residences.id as residenceid',
            'services.volume as volume'
        )
            ->where('parameters_res_utilities.period_id', $period)
            ->leftjoin('parameters_res_utilities', 'residences.id', '=', 'parameters_res_utilities.residence_id')
            ->leftjoin('parameters_costs_utilities', 'parameters_res_utilities.parameters_cost_id', '=', 'parameters_costs_utilities.id')
            ->leftjoin('services', 'parameters_res_utilities.service_id', '=', 'services.id')
            ->leftjoin('rates', 'parameters_res_utilities.rate_id', '=', 'rates.id')
            ->orderBy('residences.name')
            ->get();

        //generar columas extras de la coleccion $residences
        $residences = $residences->map(function ($residence) {
            //*Creamos nueva columna status:
            // 1 es tarifa registrada
            // 0 es tarifa sin registrar || o puede falten elementos por completar
            $status = 0;
            if ($residence->rateid === null) {
                // Si rateid es null, mantenemos el estatus predeterminado
            } elseif ($residence->rateid == 1) {
                // Si rateid es 1, verificamos si 'costid' no es null
                // Si 'isconditional' es 1, verificamos que 'ifislower' y 'ratevaluenew' no sean null
                if ($residence->costid !== null) {
                    if ($residence->isconditional == 1) {
                        // Si 'isconditional' es 1, verificamos que 'ifislower' y 'ratevaluenew' no sean null
                        if (!is_null($residence->ifislower) && !is_null($residence->ratevaluenew)) {
                            $status = 1;
                        }
                    } else {
                        // Si 'isconditional' es 0, no verificamos 'ifislower' ni 'ratevaluenew', solo 'costid'
                        $status = 1;
                    }
                }
            } elseif ($residence->rateid == 2) {
                // Si rateid es 2, verificamos si 'fixedrate' no es null
                if ($residence->fixedrate !== null) {
                    $status = 1;
                }
            } else {
                // Si rateid no es 1 ni 2, mantenemos el estatus predeterminado
            }
            // Asignamos el status calculado como un parámetro adicional en la colección
            $residence->status = $status;
            // Retornamos el objeto modificado
            return $residence;
        });


        $messages = $this->generateTariffMessages($residences);

        return response()->json(['datatc' => $qry1, 'data' => $groupedData, 'residences' => $residences, 'infos' => $messages], 201);
    }


    public function load_costs(Request $request)
    {
        $period = $request->period;
        $service = $request->service;
        $code = $request->code;
        $qry1 = parameters_costs_utilities::where('parameters_costs_utilities.period_id', $period)
            ->where('service_id', $service)
            ->where('volume_code', $code)
            ->select(
                'parameters_costs_utilities.volume_code as volume_code',
                'parameters_costs_utilities.volume as volume',
                'parameters_costs_utilities.cost_formula as cost_formula',
                'parameters_costs_utilities.cost as cost'
            )
            ->get();


        return response()->json(['data' => $qry1], 201);
    }

    //cargar solo costos disponibles en servicio 
    public function load_costs_inservice(Request $request)
    {

        $avaiblecosts = parameters_costs_utilities::select('id', 'volume_code', 'volume', 'cost', 'cost_formula')
            ->where('service_id', $request->serviceid)
            ->where('period_id', $request->period)
            ->get();


        return response()->json(['avaiblecosts' => $avaiblecosts], 200);
    }

    //cargar datos parametros de residencia
    public function load_databyresidence(Request $request)
    {

        $avaiblecosts = parameters_costs_utilities::select('id', 'volume_code', 'volume', 'cost', 'cost_formula')
            ->where('service_id', $request->serviceid)
            ->where('period_id', $request->period)
            ->get();

        $datares = parameters_res_utilities::where('residence_id', $request->residenceid)
            ->where('period_id', $request->period)
            ->where('service_id', $request->serviceid)
            ->get();

        return response()->json(['datares' => $datares, 'avaiblecosts' => $avaiblecosts], 200);
    }

    public function load_resultcosts(Request $request)
    {
        // Obtener el periodo y los datos de los servicios desde la solicitud
        $period = $request->period;
        $servicesData = $request->services; // Enviamos los servicios como un array

        // Arreglos para almacenar los serviceIds y serviceCodes
        $serviceIds = [];
        $serviceCodes = [];

        // Recorrer los datos de servicios y extraer los serviceId y serviceCode
        foreach ($servicesData as $service) {
            $serviceIds[] = $service['serviceId']; // Agregar los serviceId
            $serviceCodes[] = $service['serviceCode']; // Agregar los serviceCode
        }

        // Realizar la consulta con whereIn para los serviceIds y serviceCodes
        $data = parameters_costs_utilities::where('period_id', $period)
            ->whereIn('service_id', $serviceIds) // Filtrar por serviceId
            ->whereIn('volume_code', $serviceCodes) // Filtrar por serviceCode
            ->select('service_id', 'volume_code as code', 'cost')
            ->get();

        // Asegurémonos de devolver los datos como un arreglo de objetos
        return response()->json($data->isEmpty() ? [] : $data, 200); // Si la colección está vacía, devolver un arreglo vacío
    }


    public function save_parameters_tc(Request $request)
    {
        $period = $request->period;

        if (isset($request->tc)) {
            DB::beginTransaction(); // Iniciar una transacción de base de datos
            try {
                $value_tc = $request->tc;
                $qry = parameters_tc_utilities::updateOrCreate(
                    ['period_id' => $period], // Condiciones para buscar el registro
                    ['tc' => $value_tc, 'period_id' => $period] // Valores para actualizar o crear
                );

                // Actualizar registros en parameters_costs_utilities que contienen tc en la columna cost_formula
                $affectedRecords = parameters_costs_utilities::where('period_id', $period)
                    ->where('cost_formula', 'LIKE', "%(tc)%")
                    ->get();

                foreach ($affectedRecords as $record) {
                    $formula = $record->cost_formula;
                    preg_match_all('/\(([^)]+)\)/', $formula, $matches);
                    $codes = $matches[1];

                    $values = [];
                    foreach ($codes as $code) {
                        if ($code === 'tc' || $code === 'tax') {
                            $value = DB::table('parameters_tc_utilities')
                                ->where('period_id', $period)
                                ->value($code);
                        } else {
                            $value = DB::table('parameters_costs_utilities')
                                ->where('volume_code', $code)
                                ->where('period_id', $period)
                                ->value('volume');
                        }

                        if ($value === null) {
                            throw new \Exception("El código $code no existe o no tiene un valor asignado.");
                        }

                        $values[$code] = $value;
                    }

                    foreach ($values as $code => $value) {
                        $formula = str_replace("($code)", $value, $formula);
                    }

                    $newCost = eval("return $formula;");
                    $record->cost = $newCost;
                    $record->save();
                }

                //obtener estado parametros del periodo
                $statusparameter = $this->statusparameter_inperiod($period);
                DB::commit();
                return response()->json(['message' => 'Tipo de cambio actualizado', 'statusparameter' => $statusparameter], 201);
            } catch (\Throwable $th) {
                DB::rollback(); // Revertir la transacción si hay algún error
                return response()->json(['error' => 'Problemas para actualizar tipo de cambio en el periodo. ' . $th->getMessage()], 500);
            }
        } else if (isset($request->tax)) {
            DB::beginTransaction(); // Iniciar una transacción de base de datos
            try {
                $value_tax = $request->tax;
                $qry = parameters_tc_utilities::updateOrCreate(
                    ['period_id' => $period], // Condiciones para buscar el registro
                    ['tax' => $value_tax, 'period_id' => $period] // Valores para actualizar o crear
                );

                // Actualizar registros en parameters_costs_utilities que contienen tax en la columna cost_formula
                $affectedRecords = parameters_costs_utilities::where('period_id', $period)
                    ->where('cost_formula', 'LIKE', "%(tax)%")
                    ->get();

                foreach ($affectedRecords as $record) {
                    $formula = $record->cost_formula;
                    preg_match_all('/\(([^)]+)\)/', $formula, $matches);
                    $codes = $matches[1];

                    $values = [];
                    foreach ($codes as $code) {
                        if ($code === 'tc' || $code === 'tax') {
                            $value = DB::table('parameters_tc_utilities')
                                ->where('period_id', $period)
                                ->value($code);
                        } else {
                            $value = DB::table('parameters_costs_utilities')
                                ->where('volume_code', $code)
                                ->where('period_id', $period)
                                ->value('volume');
                        }

                        if ($value === null) {
                            throw new \Exception("El código $code no existe o no tiene un valor asignado.");
                        }

                        $values[$code] = $value;
                    }

                    foreach ($values as $code => $value) {
                        $formula = str_replace("($code)", $value, $formula);
                    }

                    $newCost = eval("return $formula;");
                    $record->cost = $newCost;
                    $record->save();
                }

                //obtener estado parametros del periodo
                $statusparameter = $this->statusparameter_inperiod($period);
                DB::commit();
                return response()->json(['message' => 'Tax actualizado', 'statusparameter' => $statusparameter], 201);
            } catch (\Throwable $th) {
                DB::rollback(); // Revertir la transacción si hay algún error
                return response()->json(['error' => 'Problemas para actualizar tax en este periodo. ' . $th->getMessage()], 500);
            }
        }
    }

    public function save_parameters_costs(Request $request)
    {
        $service = $request->service;
        $period = $request->period;
        $parameter = $request->parameter;
        $value = $request->value;
        $code = $request->code;
        $resultcost = $request->result;

        if ($parameter == 'volume') {
            DB::beginTransaction(); // Iniciar una transacción de base de datos
            try {
                $volume = parameters_costs_utilities::where('period_id', $period)
                    ->where('service_id', $service)
                    ->where('volume_code', $code)
                    ->first();

                $volume->volume = $value;
                $volume->cost = $resultcost;
                $volume->save();

                // Actualizar otros registros que contienen el mismo código en la columna cost_formula
                $affectedRecords = parameters_costs_utilities::where('period_id', $period)
                    ->where('cost_formula', 'LIKE', "%($code)%")
                    ->get();

                foreach ($affectedRecords as $record) {
                    $formula = $record->cost_formula;
                    preg_match_all('/\(([^)]+)\)/', $formula, $matches);
                    $codes = $matches[1];

                    $values = [];
                    foreach ($codes as $code) {
                        //los valores tc y tax..los tomara de la tabla tc. para poder calcular quienes tengan este codigo adicional en la formula
                        //aparte del que se va a actualizar
                        if ($code === 'tc' || $code === 'tax') {
                            $value = DB::table('parameters_tc_utilities')
                                ->where('period_id', $period)
                                ->value($code);
                        } else {
                            $value = DB::table('parameters_costs_utilities')
                                ->where('volume_code', $code)
                                ->where('period_id', $period)
                                ->value('volume');
                        }

                        if ($value === null) {
                            throw new \Exception("El código $code no existe o no tiene un valor asignado.");
                        }

                        $values[$code] = $value;
                    }

                    foreach ($values as $code => $value) {
                        $formula = str_replace("($code)", $value, $formula);
                    }

                    $newCost = eval("return $formula;");
                    $record->cost = $newCost;
                    $record->save();
                }

                DB::commit();
                return response()->json(['message' => 'Datos del volumen guardado en el servicio.'], 201);
            } catch (\Throwable $th) {
                DB::rollback(); // Revertir la transacción si hay algún error
                return response()->json(['error' => 'Problemas para actualizar este dato. ' . $th->getMessage()], 500);
            }
        } else if ($parameter == 'cost') {
            DB::beginTransaction(); // Iniciar una transacción de base de datos
            try {
                $cost = parameters_costs_utilities::where('period_id', $period)
                    ->where('service_id', $service)
                    ->where('volume_code', $code)
                    ->first();

                $cost->cost_formula = $value;
                $cost->cost = $resultcost;
                $cost->save();

                DB::commit();
                return response()->json(['message' => 'Datos de costo guardado en el servicio.'], 201);
            } catch (\Throwable $th) {
                DB::rollback(); // Revertir la transacción si hay algún error
                return response()->json(['error' => 'Problemas para actualizar este dato. ' . $th->getMessage()], 500);
            }
        }
    }

    public function save_parameters_res(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            $periodid = $request->periodid;
            $residenceid = $request->residenceid;
            $serviceid = $request->serviceid;
            $flaterateid = $request->flaterateid;
            $flaterate_value = $request->flaterate_value;
            $flaterate_conditional = $request->fixedRate_conditional;
            $consumptionlower_is = $request->consumptionlower_is;
            $flaterate_consumption_islower = $request->flaterate_consumption_islower;
            $fixedRateValue = $request->fixedRateValue;


            //+++++++++Actualizando datos de parametro en residencia+++++++++
            $qry = parameters_res_utilities::where([
                'period_id' => $periodid,
                'residence_id' => $residenceid,
                'service_id' => $serviceid
            ])
                ->update([
                    'rate_id' => $flaterateid,
                    'fixedrate_value' => ($flaterateid == 2) ? $fixedRateValue : null,
                    'parameters_cost_id' => $flaterate_value,
                    'fixedrate_isconditional' => $flaterate_conditional,
                    'consumptionlower_is' => $consumptionlower_is,
                    'flaterate_consumption_islower' => $flaterate_consumption_islower
                ]);

            //++++++++Actualizar info datos de tarifa por residencia+++++++++++
            $residences = Residence::select(
                'parameters_res_utilities.id as id',
                'parameters_res_utilities.id as pruid',
                'parameters_res_utilities.rate_id as rateid',
                'parameters_res_utilities.fixedrate_value as fixedrate',
                'parameters_res_utilities.parameters_cost_id as costid',
                'parameters_res_utilities.fixedrate_isconditional as isconditional',
                'parameters_res_utilities.consumptionlower_is as ifislower',
                'parameters_res_utilities.flaterate_consumption_islower as ratevaluenew',
                'parameters_costs_utilities.volume_code as codevolume',
                'rates.name as ratename',
                'services.id as serviceid',
                'services.name as servicename',
                'services.volume as servicevolume',
                'residences.name as residencename',
                'residences.id as residenceid',
                'services.volume as volume'
            )

                ->where('parameters_res_utilities.period_id', $periodid)
                ->leftjoin('parameters_res_utilities', 'residences.id', '=', 'parameters_res_utilities.residence_id')
                ->leftjoin('parameters_costs_utilities', 'parameters_res_utilities.parameters_cost_id', '=', 'parameters_costs_utilities.id')
                ->leftjoin('services', 'parameters_res_utilities.service_id', '=', 'services.id')
                ->leftjoin('rates', 'parameters_res_utilities.rate_id', '=', 'rates.id')
                ->orderBy('residences.name')
                ->get();

            //de la consulta anterior, filtrar solo datos quienes tenga ids, igual a $request->residenceid
            $residence = $residences->Where('residenceid', $request->residenceid)->where('serviceid', $request->serviceid)->first();


            // Generar columnas extras de la colección $residences
            //status 1: parametros registrados, status 2: parametros no registrado
            $residences = $residences->map(function ($residence) {
                $status = 0;
                if ($residence->rateid === null) {
                    // Si rateid es null, mantenemos el estatus predeterminado
                } elseif ($residence->rateid == 1) {
                    if ($residence->costid !== null) {
                        if ($residence->isconditional == 1) {
                            if (!is_null($residence->ifislower) && !is_null($residence->ratevaluenew)) {
                                $status = 1;
                            }
                        } else {
                            $status = 1;
                        }
                    }
                } elseif ($residence->rateid == 2) {
                    if ($residence->fixedrate !== null) {
                        $status = 1;
                    }
                }
                $residence->status = $status;
                return $residence;
            });


            //+++++++++Generando mensajes/infos para la tabla residencias+++++++++
            //generar mensajes info tarifas
            $messages = $this->generateTariffMessages($residences);
            //++++++obtener estado parametros del periodo+++++++
            $statusparameter = $this->statusparameter_inperiod($periodid);


            DB::commit();
            return response()->json(['message' => 'Datos tarifa-residencia actualizado.', 'infos' => $messages, 'residence' => $residence, 'statusparameter' => $statusparameter], 201);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'No fue posible gestionar esta residencia. No se aplicaran los cambios. ' . $th->getMessage()], 500);
        }
    }

    public function save_parameters_someres(Request $request)
    {

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            $periodid = $request->periodid;
            $residencesids = is_array($request->selectedResidences) ? $request->selectedResidences : [];
            $serviceid = $request->serviceid;
            $service = $request->service;
            $flaterateid = $request->flaterateid;
            $flaterate_value = $request->flaterate_value;
            $flaterate_conditional = $request->fixedRate_conditional;
            $consumptionlower_is = $request->consumptionlower_is;
            $flaterate_consumption_islower = $request->flaterate_consumption_islower;
            $fixedRateValue = $request->fixedRateValue;

            //+++++++++Actualizando datos de parametro en residencia+++++++++
            foreach ($residencesids as $residenceid) {
                $qry = parameters_res_utilities::where([
                    'period_id' => $periodid,
                    'residence_id' => $residenceid,
                    'service_id' => $serviceid
                ])
                    ->update([
                        'rate_id' => $flaterateid,
                        'fixedrate_value' => ($flaterateid == 2) ? $fixedRateValue : null,
                        'parameters_cost_id' => $flaterate_value,
                        'fixedrate_isconditional' => $flaterate_conditional,
                        'consumptionlower_is' => $consumptionlower_is,
                        'flaterate_consumption_islower' => $flaterate_consumption_islower
                    ]);
            }

            //++++++++Actualizar info datos de tarifa por residencia+++++++++++
            $residences = Residence::select(
                'parameters_res_utilities.id as id',
                'parameters_res_utilities.id as pruid',
                'parameters_res_utilities.rate_id as rateid',
                'parameters_res_utilities.fixedrate_value as fixedrate',
                'parameters_res_utilities.parameters_cost_id as costid',
                'parameters_res_utilities.fixedrate_isconditional as isconditional',
                'parameters_res_utilities.consumptionlower_is as ifislower',
                'parameters_res_utilities.flaterate_consumption_islower as ratevaluenew',
                'parameters_costs_utilities.volume_code as codevolume',
                'rates.name as ratename',
                'services.name as servicename',
                'services.volume as servicevolume',
                'residences.name as residencename',
                'residences.id as residenceid',
                'services.volume as volume'
            )
                ->where('parameters_res_utilities.period_id', $periodid)
                ->leftjoin('parameters_res_utilities', 'residences.id', '=', 'parameters_res_utilities.residence_id')
                ->leftjoin('parameters_costs_utilities', 'parameters_res_utilities.parameters_cost_id', '=', 'parameters_costs_utilities.id')
                ->leftjoin('services', 'parameters_res_utilities.service_id', '=', 'services.id')
                ->leftjoin('rates', 'parameters_res_utilities.rate_id', '=', 'rates.id')
                ->orderBy('residences.name')
                ->get();


            //de la consulta anterior, filtrar solo datos quienes tenga ids, igual a $residencesids
            $dataresidences = $residences->filter(function ($residence) use ($residencesids, $service) {
                $isResidenceValid = in_array($residence->residenceid, $residencesids);
                if ($service) {
                    $isServiceValid = stripos($residence->servicename, $service) !== false;
                    return $isResidenceValid && $isServiceValid;
                }
                return $isResidenceValid;
            });


            //+++++++++Generando mensajes/infos para la tabla residencias+++++++++
            //status 1: parametros registrados, status 2: parametros no registrado
            $residences = $residences->map(function ($residence) {
                $status = 0;
                if ($residence->rateid === null) {
                    // Si rateid es null, mantenemos el estatus predeterminado
                } elseif ($residence->rateid == 1) {
                    if ($residence->costid !== null) {
                        if ($residence->isconditional == 1) {
                            if (!is_null($residence->ifislower) && !is_null($residence->ratevaluenew)) {
                                $status = 1;
                            }
                        } else {
                            $status = 1;
                        }
                    }
                } elseif ($residence->rateid == 2) {
                    if ($residence->fixedrate !== null) {
                        $status = 1;
                    }
                }
                $residence->status = $status;
                return $residence;
            });

            //generar mensajes info tarifas
            $messages = $this->generateTariffMessages($residences);


            //obtener estado parametros del periodo
            $statusparameter = $this->statusparameter_inperiod($periodid);


            DB::commit();
            return response()->json(['message' => 'Datos tarifa-residencia actualizado.', 'infos' => $messages, 'dataresidences' => $dataresidences, 'statusparameter' => $statusparameter], 201);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'No fue posible gestionar esta residencia. No se aplicaran los cambios. ' . $th->getMessage()], 500);
        }
    }

    public function new_parameters_costs(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {

            parameters_costs_utilities::create([
                'volume_code' => $request->code,
                'volume' => $request->volume,
                'cost_formula' => $request->cost_formula,
                'cost' => $request->cost,
                'service_id' => $request->serviceId,
                'period_id' => $request->period
            ]);

            DB::commit();
            return response()->json(['message' => 'Nuevo costo registrado para este servicio.'], 201);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para registrar el nuevo costo. ' . $th->getMessage()], 500);
        }
    }

    public function find_costbycode(Request $request)
    {
        $expression = $request->input('expression');
        $period = $request->input('period');

        // Extraer los códigos de la expresión
        preg_match_all('/\(([^)]+)\)/', $expression, $matches);
        $codes = $matches[1];

        $values = [];

        if (!empty($codes)) {
            foreach ($codes as $code) {
                if ($code === 'tc' || $code === 'tax') {
                    // Buscar en la tabla parameters_tc_utilities
                    $value = DB::table('parameters_tc_utilities')
                        ->where('period_id', $period)
                        ->value($code); // Buscar en la columna 'tc' o 'tax'
                } else {
                    // Buscar en la tabla parameters_costs_utilities
                    $value = DB::table('parameters_costs_utilities')
                        ->where('volume_code', $code)
                        ->where('period_id', $period)
                        ->value('volume');
                }

                if ($value === null) {
                    return response()->json(['valid' => false, 'error' => "El código $code no existe o no tiene un valor asignado."]);
                }

                $values[$code] = $value;
            }

            // Reemplazar los códigos en la expresión con sus valores
            foreach ($values as $code => $value) {
                $expression = str_replace("($code)", $value, $expression);
            }
        }

        // Evaluar la expresión
        try {
            $result = eval("return $expression;");
            return response()->json(['valid' => true, 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['valid' => false, 'error' => 'Error al evaluar la expresión.']);
        }
    }

    public function  find_cost_residences(Request $request)
    {
        try {

            $residenciasAsociadas = 0;
            return response()->json(['residenciasAsociadas' => $residenciasAsociadas], 201);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Problemas o interrupción al consultar registros. ' . $th->getMessage()], 500);
        }
    }

    public function delete_parameters_costs(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'period' => 'required|integer',
            'serviceId' => 'required|integer',
            'serviceCode' => 'required|string',
        ]);

        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            //++++1: Verificar si existen más registros con las mismas coincidencias de period_id y service_id+++
            $existingRecordsCount = parameters_costs_utilities::where('period_id', $request->period)
                ->where('service_id', $request->serviceId)
                ->count();

            // Si solo hay un registro, no se puede eliminar porque dejaría nulo el periodo y servicio
            if ($existingRecordsCount <= 1) {
                return response()->json([
                    'error' => 'No se puede eliminar el único registro en este servicio, quedaría vacío.'
                ], 400);
            }

            //++++2: Verificar si el código está siendo utilizado en alguna fórmula o residencia+++
            // Revisar en fórmulas
            $formulaUsage = parameters_costs_utilities::where('period_id', $request->period)
                ->where('service_id', $request->serviceId)
                ->where('cost_formula', 'LIKE', '%' . $request->serviceCode . '%')
                ->where('volume_code', '!=', $request->serviceCode) // Excluir el registro actual
                ->join('services', 'parameters_costs_utilities.service_id', '=', 'services.id')
                ->get(['services.name as service_name', 'volume_code', 'cost_formula']);

            if ($formulaUsage->isNotEmpty()) {
                $usageDetails = $formulaUsage->map(function ($item) {
                    return $item->service_name . ' | Costo:  ' . $item->volume_code . ' | Formula: ' . $item->cost_formula;
                });
                return response()->json([
                    'error' => 'No se puede eliminar el registro porque está siendo utilizado en las siguientes fórmulas:',
                    'items' => $usageDetails
                ], 400);
            }

            //revisar en residencia
            $CostId = parameters_costs_utilities::where('period_id', $request->period)
            ->where('volume_code', 'LIKE', '%' . $request->serviceCode . '%')
            ->pluck('id')->first();

    
            $formulaUsage = parameters_res_utilities::where('period_id', $request->period)
                ->where('parameters_cost_id', $CostId)
                ->join('residences', 'parameters_res_utilities.residence_id', '=', 'residences.id')
                ->get(['residences.name as name']);

 
            if ($formulaUsage->isNotEmpty()) {
                $usageDetails = $formulaUsage->map(function ($item) {
                    return 'Residencia: '.$item->name;
                });
                return response()->json([
                    'error' => 'No se puede eliminar el registro porque está siendo utilizado en las siguientes residencias:',
                    'items' => $usageDetails
                ], 400);
            }



            //++++3: Proceso de eliminación+++
            // Si no hay fórmulas que utilicen el código o residencia, proceder a eliminar
            $cost = parameters_costs_utilities::where('period_id', $request->period)
                ->where('service_id', $request->serviceId)
                ->where('volume_code', $request->serviceCode)
                ->delete();

            // Reordenar los códigos de los registros restantes
            $remainingRecords = parameters_costs_utilities::where('period_id', $request->period)
                ->where('service_id', $request->serviceId)
                ->orderBy('volume_code')
                ->get();

            $codeMap = [];
            foreach ($remainingRecords as $index => $record) {
                $oldCode = $record->volume_code;
                // Extraer el prefijo y el número del código
                preg_match('/^([a-zA-Z]+)(\d+)$/', $oldCode, $matches);
                $prefix = $matches[1];
                $newCode = $index + 1;
                $newCodeStr = $prefix . $newCode;
                $codeMap[$oldCode] = $newCodeStr;
                $record->volume_code = $newCodeStr;
                $record->save();
            }

            // Actualizar los códigos en la columna cost_formula
            foreach ($codeMap as $oldCode => $newCode) {
                parameters_costs_utilities::where('period_id', $request->period)
                    ->where('service_id', $request->serviceId)
                    ->where('cost_formula', 'LIKE', '%' . $oldCode . '%')
                    ->update(['cost_formula' => DB::raw("REPLACE(cost_formula, '$oldCode', '$newCode')")]);
            }


            //traer el primer registro/costo del servicio basado en su volume_code con menor valor
            $firstdata = parameters_costs_utilities::where('period_id', $request->period)
                ->where('service_id', $request->serviceId)
                ->orderByRaw('CAST(REGEXP_REPLACE(volume_code, "[^0-9]", "") AS UNSIGNED)') // Extrae y ordena por la parte numérica
                ->first(['volume_code', 'volume', 'cost_formula', 'cost']);

            DB::commit();
            return response()->json(['message' => 'Costo eliminado y códigos reordenados correctamente.', 'firstdata' => $firstdata], 201);
        } catch (\Exception $e) {
            DB::rollback(); // Revertir la transacción si hay algún error
            // Log the error
            Log::error('Error deleting cost: ' . $e->getMessage());
            return response()->json(['error' => 'Problemas para eliminar el registro. ' . $e->getMessage()], 500);
        }
    }

    public function statusparameter_inperiod($periodId)
    {
        // Inicializamos las variables que se van a devolver
        $dataPeriods = [];

        // Obtenemos la información relacionada con el periodo
        $tcUtil = parameters_tc_utilities::where('period_id', $periodId)->first();

        // Si no están registrados ciertos parámetros en tc_utilities, establecemos el estado como pendiente
        if ($tcUtil && (is_null($tcUtil->tc) || $tcUtil->tc == 0.00 || is_null($tcUtil->tax) || $tcUtil->tax == 0.00)) {
            $status = 0;
            $message = "Parametros pendientes";
        } else {
            // Obtenemos las residencias relacionadas con el periodo
            $residences = Residence::select(
                'parameters_res_utilities.rate_id as rateid',
                'parameters_res_utilities.fixedrate_value as fixedrate',
                'parameters_res_utilities.parameters_cost_id as costid',
                'parameters_res_utilities.fixedrate_isconditional as isconditional',
                'parameters_res_utilities.consumptionlower_is as ifislower',
                'parameters_res_utilities.flaterate_consumption_islower as ratevaluenew'
            )
                ->where('parameters_res_utilities.period_id', $periodId)
                ->leftJoin('parameters_res_utilities', 'residences.id', '=', 'parameters_res_utilities.residence_id')
                ->get();

            // Inicializamos el estado como pendiente por defecto
            $status = 0;
            $message = "Parametros pendientes";

            // Verificamos si las residencias tienen los parámetros requeridos
            if ($residences->isEmpty()) {
                $status = null;
                $message = "null";
            } else {
                $allParametersRegistered = $residences->every(function ($residence) {
                    if ($residence->rateid === null) {
                        return false;
                    }
                    if ($residence->rateid == 1) {
                        if ($residence->costid === null) {
                            return false;
                        }
                        if ($residence->isconditional == 1 && (is_null($residence->ifislower) || is_null($residence->ratevaluenew))) {
                            return false;
                        }
                    } elseif ($residence->rateid == 2) {
                        if ($residence->fixedrate === null) {
                            return false;
                        }
                    }
                    return true;
                });

                // Si todos los parámetros están registrados, se cambia el estado a 1
                if ($allParametersRegistered) {
                    $status = 1;
                    $message = "Parametros registrados";
                }
            }
        }

        // Actualizamos la columna 'parametersutilities' en la tabla 'periods' según el estado
        $period = period::find($periodId);
        if ($period) {
            $period->parametersutilities = $status;
            $period->save();
        }

        // Devolvemos el estado y mensaje
        $dataPeriods = [
            'status' => $status,
            'message' => $message
        ];

        return $dataPeriods;
    }

    public function generateTariffMessages($residences)
    {
        $messages = [];
        $consumptionTariffs = [];
        $fixedTariffs = [];

        foreach ($residences as $residence) {
            if ($residence->rateid == 1) { // Por consumo
                $key = "{$residence->servicename}_{$residence->codevolume}";
                $subkey = "{$residence->ifislower} => \${$residence->ratevaluenew} USD";

                $consumptionTariffs[$key][$subkey] = $consumptionTariffs[$key][$subkey] ?? [
                    'count' => 0,
                    'residences' => []
                ];

                $consumptionTariffs[$key][$subkey]['count']++;
                $consumptionTariffs[$key][$subkey]['residences'][] = $residence->residencename;
            } elseif ($residence->rateid == 2) { // Fija
                $key = "{$residence->fixedrate} USD";

                $fixedTariffs[$key] = $fixedTariffs[$key] ?? [
                    'count' => 0,
                    'residences' => [],
                    'servicename' => $residence->servicename
                ];

                $fixedTariffs[$key]['count']++;
                $fixedTariffs[$key]['residences'][] = $residence->residencename;
            }
        }

        // Mensajes de tarifas por consumo
        // Generar los mensajes para tarifas por consumo
        foreach ($consumptionTariffs as $key => $subtariffs) {
            $count = array_sum(array_column($subtariffs, 'count'));
            $residencesList = array_merge(...array_column($subtariffs, 'residences'));

            $messages[] = [
                'message' => "$count residencias tienen la tarifa por consumo $key",
                'service' => explode('_', $key)[0],
                'residences' => $residencesList
            ];

            foreach ($subtariffs as $subkey => $subtariff) {
                // Verificar si el subkey está vacío o no tiene valores asignados
                $minimumConsumption = $subkey === " => \$ USD"
                    ? "no tienen tarifa de consumo mínimo asignado"
                    : "con tarifa mínima: $subkey";

                $messages[] = [
                    'message' => "--- {$subtariff['count']} residencias $minimumConsumption",
                    'service' => explode('_', $key)[0],
                    'residences' => $subtariff['residences']
                ];
            }
        }
        return $messages;
    }

    public function duplicateparameters(Request $request)
    {
        $checkduplicateParameters = $request->checkduplicateParameters;
        if ($checkduplicateParameters) {

            DB::beginTransaction(); // Iniciar una transacción de base de datos
            try {
                //parametros periodo a copiar
                $prev_parameterstc = parameters_tc_utilities::where('period_id', $request->origenperiod)->get();
                $prev_parameterscosts = parameters_costs_utilities::where('period_id', $request->origenperiod)->get();
                $prev_parametersres = parameters_res_utilities::where('period_id', $request->origenperiod)->get();


                //::::transferir datos tc::::
                foreach ($prev_parameterstc as $param) {
                    parameters_tc_utilities::where('period_id', $request->transperiod)
                        ->update([
                            'tc' => $param->tc,
                            'tax' => $param->tax,
                        ]);
                }

                //::::transferir datos costs::::
                $trans_parameterscosts = parameters_costs_utilities::where('period_id', $request->transperiod)->get();
                $trans_volume_codes = $trans_parameterscosts->pluck('volume_code')->toArray();

                // Recorrer los registros del origenperiod
                foreach ($prev_parameterscosts as $param) {
                    // Verificar si existe un registro con el mismo volume_code en el transperiod
                    $existingRecord = parameters_costs_utilities::where('period_id', $request->transperiod)
                        ->where('volume_code', $param->volume_code)
                        ->first();

                    if ($existingRecord) {
                        // Si existe, actualizamos el registro
                        $existingRecord->update([
                            'volume' => $param->volume,
                            'cost_formula' => $param->cost_formula,
                            'cost' => $param->cost,
                            'service_id' => $param->service_id,
                        ]);

                        // Eliminar el volume_code de la lista de trans, ya que ya se actualizó
                        $trans_volume_codes = array_diff($trans_volume_codes, [$param->volume_code]);
                    } else {
                        // Si no existe, lo creamos en transperiod
                        parameters_costs_utilities::create([
                            'volume_code' => $param->volume_code,
                            'volume' => $param->volume,
                            'cost_formula' => $param->cost_formula,
                            'cost' => $param->cost,
                            'service_id' => $param->service_id,
                            'period_id' => $request->transperiod, // nuevo periodo
                        ]);
                    }
                }

                // Eliminar los registros en transperiod que no existen en el origenperiod
                parameters_costs_utilities::where('period_id', $request->transperiod)
                    ->whereIn('volume_code', $trans_volume_codes)
                    ->delete();


                //::::transferir datos res::::
                // Recorrer los registros del origenperiod
                foreach ($prev_parametersres as $param) {
                    Log::debug('Procesando parámetro:', ['param' => $param]);

                    // Verificar si existe un registro con la misma combinación residence_id y service_id en el transperiod
                    $existingRecord = parameters_res_utilities::where('period_id', $request->transperiod)
                        ->where('residence_id', $param->residence_id)
                        ->where('service_id', $param->service_id)
                        ->first();


                    if ($existingRecord) {
                        // Obtener el id del costo duplicado nuevo, con respecto al origen de donde estamos duplicando.
                        // Obtendremos primero el volume_code del periodo anterior
                        $Costorigencode = parameters_costs_utilities::where('id', $existingRecord->parameters_cost_id)
                            ->pluck('volume_code');


                        // Obtenemos el id del periodo duplicado, con respecto al volume_code de la consulta
                        $Costnewid = parameters_costs_utilities::where('volume_code', $Costorigencode)
                            ->where('period_id', $request->transperiod)
                            ->pluck('id');

                        // Actualizar el registro
                        $existingRecord->update([
                            'rate_id' => $param->rate_id,
                            'fixedrate_value' => $param->fixedrate_value,
                            'parameters_cost_id' => $Costnewid,
                            'fixedrate_isconditional' => $param->fixedrate_isconditional,
                            'consumptionlower_is' => $param->consumptionlower_is,
                            'flaterate_consumption_islower' => $param->flaterate_consumption_islower,
                        ]);
                    } else {
                        Log::warning('No se encontró un registro para el parámetro:', ['param' => $param]);
                    }
                }



                // Actualizamos periodo como registrado
                $period = period::find($request->transperiod);
                if ($period) {
                    $period->parametersutilities = 1;
                    $period->save();
                }

                // Devolvemos el estado y mensaje
                $statusparameter = [
                    'status' => 1,
                    'message' => "Parametros registrados"
                ];

                DB::commit();
                return response()->json(['message' => 'Datos transferidos con éxito a este periodo.', 'statusparameter' => $statusparameter], 201);
            } catch (\Throwable $th) {
                DB::rollback(); // Revertir la transacción si hay algún error
                return response()->json(['error' => 'Problemas para realizar esta acción. ' . $th->getMessage()], 500);
            }
        } else {

            return response()->json(['message' => 'Checkbox no marcado']);
        }
    }
}
