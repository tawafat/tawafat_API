<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
//        return Job::all()->sortByDesc('name')->values();
//        $visitors = Job::select(DB::raw('COUNT(*)  as total'), DB::raw('date(created_at) as dates'))
//            ->groupBy('dates');
        $user_list = Job::where('status', '=','completed')->select(
                DB::raw('DATE_FORMAT(created_at,"%d %b %y") as dates'),
                DB::raw('COUNT(*)  as total')
            )
            ->orderBy('created_at')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d")'))
            ->get();

        $job_all = Job::count();
        $job_inactive = Job::where('status', '=', 'inactive' )->count();
        $job_active = Job::where('status', '=', 'active' )->count();
        $job_completed = Job::where('status', '=', 'completed' )->count();
        $job_cancelled = Job::where('status', '=', 'cancelled' )->count();

        return ['graph' => $user_list,
            'pie'=> [
                'total'=> $job_inactive + $job_active + $job_completed + $job_cancelled,
                'inactive'=> $job_inactive,
                'active'=> $job_active,
                'completed'=> $job_completed,
                'cancelled'=> $job_cancelled,
            ]];
    }

}
