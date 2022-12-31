<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{

    public function index()
    {
        return Job::with([
            'location',
            'category',
            'assigned_to',
            'updated_by',
            'created_by'])->get()->sortByDesc('created_at')->values();
    }


    public function store(Request $request)
    {
       $user =  $request->user();
        $request->validate([
            'name' => 'required|max:25',
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
//            'location_id' => 'required',
            'location' => 'required',
            'location.long' => 'required',
            'location.lat' => 'required',
            'category_slug' => 'required',
            'assigned_to_id' => 'int|nullable'

        ]);
        $data = $request->all();
        $data['updated_by_id'] = $user->id;
        $data['created_by_id'] = $user->id;
        $job =  Job::create($data);
        $location = $job->location()->create($request->get('location'));
        $job->location_id = $location->id;
        $job->save();
        return  $job;
    }


    public function show($id)
    {
        return Job::with([
            'location',
            'category',
            'assigned_to',
            'updated_by',
            'created_by',
            'complains'])->find($id);
    }


    public function update(Request $request, $id)
    {
        $user =  $request->user();
        $job = Job::find($id);
        $data = $request->all();
        $data['updated_by_id'] = $user->id;
        $job->update($data);
        return $job;
        //
    }


    public function destroy($id)
    {
        $response =  Job::destroy($id);
        if($response){
            return response( ['message'=> 'Deleted Successfully'], 200);
        } else{
            return response( ['message'=> 'Not Found'], 404);
        }
    }
}
