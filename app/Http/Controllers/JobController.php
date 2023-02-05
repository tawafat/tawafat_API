<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Job;
use Illuminate\Http\Response;
use Mailjet\LaravelMailjet\Facades\Mailjet;
use Mailjet\Resources;


class JobController extends Controller
{

    /**
     * @OA\Get(
     *      path="/job",
     *      operationId="jobList",
     *      tags={"job"},
     *      summary="Get list of projects",
     *      description="Returns list of projects",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     **/
    public function index()
    {
        $jobs = Job::with([
            'location',
            'category',
            'assigned_to',
            'complains',
            'updated_by',
            'created_by'])->get()->sortByDesc('created_at')->values();

        foreach ($jobs as $job) {
            $job->complains_counts = count($job->complains);
            unset($job->complains);
        }
        return $jobs;
    }

    public function assignedToMe(Request $request)
    {

        $user = $request->user();
        $jobs = Job::with([
            'location',
            'category',
            'complains',
            'updated_by',
            'created_by'])->where(['assigned_to_id' => $user->id])->get()->sortByDesc('created_at')->values();

        foreach ($jobs as $job) {
            $job->complains_counts = count($job->complains);
            unset($job->complains);
        }
        return $jobs;
    }


    /**
     * @OA\Post(
     *      path="/projects",
     *      operationId="storeProject",
     *      tags={"Projects"},
     *      summary="Store new project",
     *      description="Returns project data",
     *      @OA\RequestBody(
     *          required=true,
     *
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|max:25',
            'description' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required',
//          'location_id' => 'required',
            'location' => 'required',
            'location.long' => 'required',
            'location.lat' => 'required',
            'category_slug' => 'required',
            'assigned_to_id' => 'int|nullable'
        ]);


        $data = $request->all();
        $data['updated_by_id'] = $user->id;
        $data['created_by_id'] = $user->id;
        $job = Job::create($data);
        $location = $job->location()->create($request->get('location'));
        $job->location_id = $location->id;
        $job->save();


        // save on the log
        $metaData = compact('user', 'job', 'location');
        $log = dataLogController::createLog('Create a new job', 'update', $job->getTable() , $job->id, $user, $metaData, $request);
        $job->logs()->save($log);


        return $job;


    }


    /**
     * @OA\Get(
     *      path="/projects/{id}",
     *      operationId="getProjectById",
     *      tags={"Projects"},
     *      summary="Get project information",
     *      description="Returns project data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Project id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function show($id)
    {
        return Job::with([
            'location',
            'category',
            'assigned_to',
            'updated_by',
            'created_by',
            'complains',
            'logs'])->find($id);
    }


    /**
     * @OA\Put(
     *      path="/projects/{id}",
     *      operationId="updateProject",
     *      tags={"Projects"},
     *      summary="Update existing project",
     *      description="Returns updated project data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Project id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=202,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $job = Job::find($id);
        $job_before = $job->toArray();
        $requestData = $request->all();
        $requestData['updated_by_id'] = $user->id;
        $job->update($requestData);

        // save on the log
        $metaData = compact('user', 'job', 'job_before');
        $log = dataLogController::createLog('Update a job', 'update', $job->getTable() , $id, $user, $metaData, $request);
        $job->logs()->save($log);



        return $job;
        //
    }

    public function assignTo(Request $request, $id)
    {
        $request->validate([
            'assigned_to_id' => 'int|required'
        ]);

        $user = $request->user();
        $job = Job::find($id);
        $job_before = $job->toArray();
        $job->updated_by_id = $user->id;
        $job->assigned_to_id = $request->assigned_to_id;

        // save on the log
        $metaData = compact('user', 'job', 'job_before');
        $log = dataLogController::createLog('Assign a job to a user', 'update', $job->getTable() , $id, $user, $metaData, $request);
        $job->logs()->save($log);


        return $job;
        //
    }

    public function startEnd(Request $request, $id)
    {

        // Validation the Request
        $request->validate([
            'action' => 'required',
            'date' => 'required|date',
        ]);
        if ($request->action !== 'start' && $request->action !== 'end') {
            return new Response([
                'message' => 'The given data was invalid.',
                'errors' => ['action' => ["Action should be 'start' or 'end' only"]]], 422);
        }
        $job = Job::find($id);
        $job_before = $job->toArray();
        $user = $request->user();

        if ($request->action === 'start' && !empty($job->actual_start_date)) {
            return new Response([
                'message' => 'The given data was invalid.',
                'errors' => ['action' => ["This Job is already started before"]]], 422);
        }
        if ($request->action === 'end' && !empty($job->actual_end_date)) {
            return new Response([
                'message' => 'The given data was invalid.',
                'errors' => ['action' => ["This Job is already ended before"]]], 422);
        }
        /// end Validation the Request



        if ($request->action === 'start') {
            $job->actual_start_date = $request->date;
        } else {
            $job->actual_end_date = $request->date;
        }
        $job->updated_by_id = $user->id;
        $job->save();


        // save on the log
        $metaData = compact('user', 'job', 'job_before');
        $log = dataLogController::createLog('set actual start or end date of a job', 'update', $job->getTable() , $id, $user, $metaData, $request);
        $job->logs()->save($log);


        return $job;
        //
    }

    /**
     * @OA\Delete(
     *      path="/projects/{id}",
     *      operationId="deleteProject",
     *      tags={"Projects"},
     *      summary="Delete existing project",
     *      description="Deletes a record and returns no content",
     *      @OA\Parameter(
     *          name="id",
     *          description="Project id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     */
    public function destroy(Request $request,$id)
    {
        // save on the log
        $user = $request->user();
        $job = Job::find($id);
        $job_before = $job->toArray();
        $metaData = compact('user',  'job_before');
        $log = dataLogController::createLog('Delete a job', 'delete', $job->getTable() , $id, $user, $metaData, $request);
        $job->logs()->save($log);
        /// end save


        $response = Job::destroy($id);
        if ($response) {
            return response(['message' => 'Deleted Successfully'], 200);
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }





       public function jobLogs($id)
       {
           $job = Job::with(['logs'])->find($id);
           $job->logs->makeVisible('data');

           return $job;
       }




}
