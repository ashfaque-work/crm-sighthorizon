<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\LoginDetail;


class UserLogController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // $created_by = $user->creatorId();
        $month = [
            ''   => __('All'),
            '01' => __('JAN'),
            '02' => __('FEB'),
            '03' => __('MAR'),
            '04' => __('APR'),
            '05' => __('MAY'),
            '06' => __('JUN'),
            '07' => __('JUL'),
            '08' => __('AUG'),
            '09' => __('SEP'),
            '10' => __('OCT'),
            '11' => __('NOV'),
            '12' => __('DEC'),
        ];

        $user = User::where('created_by','=', $user->ownerId())->get()->pluck('name','id');
        $user->prepend('All', '');
        $users = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.name  as user_name  , users.email as user_email'))
                ->where(['login_details.created_by' => \Auth::user()->id]);

                if(!empty($request->username))
                {
                    $users->where('user_id',$request->username);
                }
                if(!empty($request->month))
                {
                    $users->whereMonth('date',$request->month);
                }
                $users = $users->get();

        return view('users.userLog', compact('users','month','user'));
    }

    public function show(LoginDetail $loginDetail,$id)
    {
        $userlog = LoginDetail::find($id);
        $request = json_decode($userlog->details,true);

        return view('users.showUserLog', compact('userlog','request'));
    }

    public function destroy(LoginDetail $loginDetail,$id)
    {
        $userlog = LoginDetail::find($id);

        $userlog->delete();
        return redirect()->back()->with('success', 'User Log Deleted Successfully.');
    }

}
