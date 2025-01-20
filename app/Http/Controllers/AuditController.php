<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\audit;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class AuditController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('audit.index'), only: ['index'])
        ];
    }

    public function index()
    {

        //datos tabla
        $data = Audit::with('user')
            ->orderBy('id', 'desc')
            ->get();

        //datos select
        $users = $data->pluck('user.username')->unique();
        $panels = $data->pluck('panel')->unique();
        $modules = $data->pluck('module')->unique();

        return view('audit', compact('data', 'users', 'panels', 'modules'));
    }

    public function getAuditData()
    {
        $data = Audit::with('user')
            ->orderBy('id', 'desc')
            ->get();

        $html = '';

        foreach ($data as $audit) {
            $html .= '<tr>';
            $html .= '<td>' . $audit->id . '</td>';
            $html .= '<td>' . $audit->user->name . '</td>';
            $html .= '<td>' . $audit->action . '</td>';
            $html .= '<td>' . $audit->panel . '</td>';
            $html .= '<td>' . $audit->module . '</td>';
            $html .= '<td>' . $audit->description . '</td>';
            $html .= '<td>' . $audit->datatable . '</td>';
            $html .= '<td>' . $audit->databefore . '</td>';
            $html .= '<td>' . $audit->dataafter . '</td>';
            $html .= '<td>' . $audit->created_at . '</td>';
            $html .= '</tr>';
        }

        return $html;
    }


    public function metadata(Request $request)
    {
        try {
            $data = Audit::with('user') ->where('id', $request->id) ->get();
            return response()->json(['audit' =>  $data]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Problemas al leer datos solicitados' . $th->getMessage()], 500);
        }
    }
}
