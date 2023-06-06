<?php

namespace App\Http\Controllers;

use App\Models\JobDetail;
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
            'created_by',
            'jobDetail'])->get()->sortByDesc('created_at')->values();

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
           // 'category_slug' => 'required',
            'enable_gps' => 'required',
            'enable_studio' => 'required',
            'type' => 'required'
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
            'logs',
            'jobDetail'])->find($id);
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


        // Delete the related JobDetail record
        $jobDetail = $job->jobDetail;
        if ($jobDetail) {
            $jobDetail->delete();
        }

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




    /**
     * Store the job details for the related job ID.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $jobId
     *
     * @return \Illuminate\Http\Response
     *
     * @SWG\Post(
     *     path="/jobs/{jobId}/details",
     *     summary="Store job details",
     *     tags={"Job Details"},
     *     description="Store the job details for the specified job ID.",
     *     operationId="storeJobDetails",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="jobId",
     *         in="path",
     *         description="ID of the job",
     *         required=true,
     *         type="integer",
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Job details to be stored",
     *         required=true,
     *         @SWG\Schema(
     *             @SWG\Property(
     *                 property="job_type",
     *                 type="string",
     *                 description="Type of the job"
     *             ),
     *             @SWG\Property(
     *                 property="no_of_packages",
     *                 type="integer",
     *                 description="Number of packages"
     *             ),
     *             @SWG\Property(
     *                 property="rejected_packages",
     *                 type="integer",
     *                 description="Number of rejected packages"
     *             ),
     *             @SWG\Property(
     *                 property="min_weight",
     *                 type="integer",
     *                 description="Minimum weight"
     *             ),
     *             @SWG\Property(
     *                 property="Date_time",
     *                 type="string",
     *                 format="date-time",
     *                 description="Date and time"
     *             ),
     *             @SWG\Property(
     *                 property="gate_number",
     *                 type="integer",
     *                 description="Gate number"
     *             ),
     *             @SWG\Property(
     *                 property="no_entering",
     *                 type="integer",
     *                 description="Number of entering"
     *             ),
     *             @SWG\Property(
     *                 property="no_exiting",
     *                 type="integer",
     *                 description="Number of exiting"
     *             ),
     *             @SWG\Property(
     *                 property="no_inside",
     *                 type="integer",
     *                 description="Number inside"
     *             ),
     *             @SWG\Property(
     *                 property="camp_number",
     *                 type="integer",
     *                 description="Camp number"
     *             ),
     *             @SWG\Property(
     *                 property="temperature",
     *                 type="integer",
     *                 description="Temperature"
     *             ),
     *             @SWG\Property(
     *                 property="humidity",
     *                 type="integer",
     *                 description="Humidity"
     *             ),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Job details created successfully",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Success message"
     *             ),
     *         ),
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Job not found",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Error message"
     *             ),
     *         ),
     *     ),
     * )
     */
    public function storeDetails(Request $request, $jobId): Response
    {
        $validatedData = $request->validate([
            //'job_type' => 'nullable',
            'no_of_packages' => 'nullable|numeric',
            'rejected_packages' => 'nullable|numeric',
            'min_weight' => 'nullable|numeric',
            'date_time' => 'nullable|date',
            'gate_number' => 'nullable|numeric',
            'no_entering' => 'nullable|numeric',
            'no_exiting' => 'nullable|numeric',
            'no_inside' => 'nullable|numeric',
            'camp_number' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
        ]);





        $job = Job::find($jobId);

        if (!$job) {
            return response(['message' => 'Job not found'], 404);
        }

        // Check if a job detail record already exists for the job
        $jobDetail = JobDetail::where('job_id', $jobId)->first();
        // If a job detail record exists, update it
        if ($jobDetail) {
            $jobDetail->update($validatedData);
        } else {
            // Create a new job detail


            $jobDetail = JobDetail::create($validatedData);
            die();
            $jobDetail->job_type = $job->type;
            $jobDetail->job_id = "'".$jobId."'";

            echo   $jobDetail->job_id;

            $jobDetail->save();

            // Associate the job detail with the job

//            $job->jobDetail()->associate($jobDetail);
            $job->save();
        }


        // log
        $metaData = compact('job');
        $user = $request->user();
        $log = dataLogController::createLog('save a job details', 'jobDetails', $job->getTable() , $job->id, $user, $metaData, $request);
        $job->logs()->save($log);

        $jobWithDetail = Job::with('jobDetail')->find($jobId);
        return response(['message' => 'JobDetails created successfully',
        'job' => $jobWithDetail], 200);


    }





}
