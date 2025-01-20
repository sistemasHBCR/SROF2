<?php

namespace App\Http\Controllers;

use App\Models\parameters_costs_utilities;
use App\Models\parameters_res_utilities;
use App\Models\parameters_tc_utilities;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendsAzureNotificationEmail;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\user;
use App\Models\status;
use App\Models\period;
use App\Models\utilities;
use App\Models\residence;
use App\Models\service;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MainController extends Controller implements HasMiddleware
{
    use LogsActivity;
    use SendsAzureNotificationEmail;

    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('utilities.periods'), only: ['Index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('utilities.createperiod'), only: ['createperiod']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('utilities.viewperiod'), only: ['viewperiod', 'utilities_bills']),
        ];
    }

    /**VISTA PERIODOS MENSUALES UTILITIES**/

    public function index(Request $request)
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


        Carbon::setLocale('es'); // Configurar Carbon para usar español

        $months = [];
        $periods = [];
        // obtener fecha inicio y final de cada Mez de acuerdo al año actual $dateNow
        for ($i = 1; $i <= 12; $i++) {
            $monthStart = Carbon::createFromDate($dateNow->year, $i, 1);
            $monthEnd = $monthStart->endOfMonth();
            $monthData = [
                'target' => strtolower($monthStart->translatedFormat('F')),
                'number' => $i,
                'title' => ucfirst(strtolower($monthStart->translatedFormat('F'))),
                'start_date' => $monthStart->copy()->startOfMonth()->format('d/m/Y'),
                'end_date' => $monthEnd->format('d/m/Y')
            ];
            $months[] = $monthData;


            //obtener periodos registrados de cada mes del recorrido
            $period = Period::where('start', '=', $monthStart->copy()->startOfMonth()->format('Y-m-d'))
                ->where('end', '=', $monthEnd->format('Y-m-d'))
                ->with('status') // Cargar la relación status
                ->get();

            $periods[] = $period->toArray();
        }


        //sin capturar, en curso, capturado.
        $status1 = Status::select('id', 'name', 'class')->find(1);
        $status2 = Status::select('id', 'name', 'class')->find(2);
        $status3 = Status::select('id', 'name', 'class')->find(3);


        return view('index', compact('months', 'dateNow', 'status1',  'status2', 'status3', 'periods'));
    }


    //**VISTA CAPTURA DE CONSUMOS  DE UN PERIODO UTILITIES***/
    public function viewperiod(Request $request)
    {
        $request->validate([
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date);
        $end_date = Carbon::createFromFormat('d/m/Y', $request->end_date);
        $current_date = Carbon::now();

        //sin capturar, en curso, capturado
        $status1 = Status::find(1);
        $status2 = Status::find(2);
        $status3 = Status::find(3);


        $period = Period::where('start', '=', $start_date->format('Y-m-d'))->with('status')
            ->where('end', '=', $end_date->format('Y-m-d'))
            ->get();

        $utilities = Utilities::where('token', $period->first()->token)->get();
        $residences = residence::orderBy('name')->get();

        //** REVISAR CONDICION PARA ENTRAR A ESTE MODULO**/
        //Primero: Verificar si fecha inicio y final que se esta comparando sean del mismo mes y año
        if ($start_date->month == $end_date->month && $start_date->year == $end_date->year) {
            //segundo: Ahora verificamos si son el primer y último día del mes
            if ($start_date->day == 1 && $end_date->day == $end_date->daysInMonth) {

                //tercero: Verificar si el período que se está abriendo no sea mayor que la fecha actual
                if ($current_date <=  $start_date) {
                    return view("error")->with('error', 'No se puede abrir un período que es mayor que la fecha actual.');
                } else {
                    //cuarto: Verificar si el período esta en status 1
                    if (!$period->isEmpty()) {
                        if ($period->first()->status_id == 1) {
                            //se ha creado periodo en el modelo pero su estatus esta en 1
                            return view("error")->with('error', 'No se puede abrir este período porque su estado actual es: ' . $status1->name . '. Por favor, cámbielo a ' . $status2->name . '.');
                        } else {
                            //periodo no esta en status 1, entonces rederigir a vista captura de consumos
                            return view('viewperiod', compact('start_date', 'end_date', 'period', 'utilities', 'residences'));
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
    }

    //**CREAR O ACTUALIZAR UN PERIODO UTILITIES DESDE EL INDEX***/
    public function createPeriod(Request $request)
    {
        DB::beginTransaction(); // Iniciar una transacción de base de datos
        try {
            // Buscar un período existente con el mismo inicio y fin
            $existingPeriod = Period::where('start', $request->start)
                ->where('end', $request->end)
                ->first();

            if ($existingPeriod) {
                return $this->updateExistingPeriod($existingPeriod);
            }
            return $this->createNewPeriod($request);
        } catch (\Throwable $th) {
            DB::rollback(); // Revertir la transacción si hay algún error
            return response()->json(['error' => 'Problemas para crear este periodo ' . $th->getMessage()], 500);
        }
    }

    private function updateExistingPeriod($existingPeriod)
    {
        $datePeriod = Carbon::parse($existingPeriod->start)->locale('es_ES')->isoFormat('MMMM YYYY'); // mes, año
        $before = clone $existingPeriod;  // Capturar periodo antes de actualizar
        $existingPeriod->update(['status_id' => 2]); // Actualizar periodo
        $after = $existingPeriod; // Capturar datos actualizados

        // Registrar la actividad
        $this->logActivity(
            'update',
            'Utilities',
            'Periodos',
            'Abrio un nuevo periodo, ' . ucfirst($datePeriod),
            'periods',
            $before,
            $after
        );

        //mandar notificacion
        $notificacion = 'utilities.openperiod';
        $this->sendNotification($notificacion, $datePeriod);

        DB::commit();
        return response()->json(['message' => 'Período actualizado con éxito', 'period' => $existingPeriod]);
    }

    private function createNewPeriod($request)
    {
        //Crear periodo
        $period = Period::create([
            'start' => $request->start,
            'end' => $request->end,
            'concept' => "Utilities",
            'status_id' => 2, // en curso
        ]);
        $datePeriod = Carbon::parse($period->start)->locale('es_ES')->isoFormat('MMMM YYYY'); // mes, año

        //crear parametros para modulo parametros, seccion datos generales
        $pgeneral = parameters_tc_utilities::create([
            'tc' => 0,
            'tax' => 0,
            'period_id' => $period->id,
        ]);
        //crear servicios para modulo parametros, seccion costos
        $services = service::where('active', 'Y')->get();
        foreach ($services as $service) {
            $pcosts = parameters_costs_utilities::create([
                'volume_code' => $service->code . 1, //ejemplo a1
                'volume' => 0,
                'cost_formula' => '',
                'cost' => 0,
                'service_id' => $service->id,
                'period_id' => $period->id,
            ]);
        }
        //crear residencias para modulo parametros, seccion tarifa residencias x servicios
        $residences = residence::where('active', 'Y')->get();
        foreach ($residences as $residence) {
            foreach ($services as $service) {
                parameters_res_utilities::create([
                    'residence_id' => $residence->id,
                    'service_id' => $service->id,
                    'period_id' => $period->id,
                    'rate_id' => null,
                    'flaterate_value' => null,
                    'parameters_cost_id' => null,
                    'consumptionlower_is' => null,
                    'flaterate_consumption_islower' => null
                ]);
            }
        }

        // Registrar la actividad
        $this->logActivity(
            'create',
            'Utilities',
            'Periodos',
            'Abrio un nuevo periodo, ' . ucfirst($datePeriod),
            'periods',
            null,
            $period->toArray()
        );

        //mandar notificacion
        $notificacion = 'utilities.openperiod';
        $this->sendNotification($notificacion, $datePeriod);

        DB::commit();
        return response()->json(['message' => 'Período creado con éxito', 'period' => $period]);
    }

    private function sendNotification($notificacion, $date)
    {
        $subject = 'Utilities | Periodo abierto';
        $message = 'El usuario ' . Auth::user()->name . ' ' . Auth::user()->last_name . ' abrió un nuevo periodo: ' . ucfirst($date) . '. Ya se pueden capturar datos en este panel.';
        $users = User::whereNotNull('email')->get();
        $filteredUsers = $users->filter(function ($user) {
            return !$user->hasRole('Suspendido');
        });

        try {
            $this->sendNotificationEmail($subject, $message, $filteredUsers);
        } catch (\Exception $e) {
            // Registrar un error en los logs si falla el envío de correos
            Log::error('Error al enviar notificación por correo: ' . $e->getMessage());
            // Continuar con el proceso sin interrumpir la ejecución
        }
    }

    //**Recibos Utilities***/
    public function utilities_bills(Request $request)
    {
        $start_date = Carbon::parse($request->start_date);
        $end_date = Carbon::parse($request->end_date);
        $status = $request->period_status;

        $period = Period::where('start', '=', $start_date->format('Y-m-d'))->with('status')
            ->where('end', '=', $end_date->format('Y-m-d'))
            ->get();

        //sin capturar, en curso, capturado
        $status1 = Status::find(1);
        $status2 = Status::find(2);
        $status3 = Status::find(3);


        //** REVISAR CONDICION PARA ENTRAR A ESTE MODULO**/
        //cuarto: Verificar si el período correspondiente esta en status 3
        if (!$period->isEmpty()) {
            if ($period->first()->status_id == 3) {
                $data = utilities::where('token', $period->first()->token)->get();
                //periodo esta en status 3, entonces rederigir a vista imprimir recibos
                return view('bills.utilities', compact('data', 'start_date', 'end_date'));
            } else {
                //se ha creado periodo en el modelo pero su estatus esta en 1 o 2
                return view("error")->with('error', 'No se puede abrir este recibo porque su periodo actual esta en: ' . $status1->name . ' o ' . $status2->name . '. Disponible solo como: ' . $status3->name . '.');
            }
        }
    }
}
