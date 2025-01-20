<?php

namespace App\Traits;
use App\Models\User;
use App\Models\audit;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public function logActivity($action, $panel, $module, $description = null, $datatable = null, $before = null, $after = null, $email = null)
    {


        // Determinar el user_id
        $user_id = Auth::id();
        if (!$user_id && $email) {
            $user = User::where('email', $email)->first();
            $user_id = $user ? $user->id : null;
        }


        $databefore = $before !== null ? json_encode($before) : '';
        $dataafter = $after !== null ? json_encode($after) : '';

        audit::create([
            'user_id' => $user_id,
            'action' => $action,
            'panel' => $panel,
            'module' => $module,
            'description' => $description,
            'datatable' => $datatable,
            'databefore' =>  $databefore,
            'dataafter' => $dataafter
        ]);
    }
}
