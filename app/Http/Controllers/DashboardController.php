<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
//        return Job::all()->sortByDesc('name')->values();
//        $visitors = Job::select(DB::raw('COUNT(*)  as total'), DB::raw('date(created_at) as dates'))
//            ->groupBy('dates');
        $user_list = Job::where('status', '=','completed')->select(
                DB::raw('DATE_FORMAT(created_at, "%M %Y") as dates'),
                DB::raw('COUNT(*)  as total')
            )
            ->orderBy('created_at')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->get();

        $job_all = Job::count();
        $job_pending = Job::where('status', '=', 'pending' )->count();
        $job_progress = Job::where('status', '=', 'progress' )->count();
        $job_completed = Job::where('status', '=', 'completed' )->count();

        return ['graph' => $user_list,
            'pie'=> [
                'total'=> $job_all,
                'pending'=> $job_pending,
                'progress'=> $job_progress,
                'completed'=> $job_completed,
            ]];
    }

}
