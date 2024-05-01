<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeaveStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Middleware\isUser;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\Time_schedule;
use App\Models\User;
use App\Models\User_leave;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\Console\Input\Input;

use function PHPUnit\Framework\isNull;

class UserController extends Controller
{

    function allUsers(Request $request)
    {
        // $userId = User::where('role', 'User')->get();
        $usersAndOrders = DB::table('users')
            ->where('role', 'User')
            ->join('designations', 'users.designation', '=', 'designations.id')
            ->select('users.id', 'users.schedule', 'users.name', 'users.email', 'users.role', 'users.status', 'users.address', 'users.cnic', 'users.contactNo', 'users.image','users.isEmployee', 'designations.designation')
            // ->where('users.status', '=', 'Active')
            ->get();


        if ($usersAndOrders == null) {
            return response([
                'success' => false,
                'message' => "User doesn't exist",
                'data' => null,
            ]);
        }

        $perPage = $request->input('perPage', 10);

        $search = $request->input('search');

        $query = User::query();


        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }
        $users = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'message' => 'Data found',
            'data' => $usersAndOrders
        ]);
    }

}
