<?php

namespace App\Http\Controllers;

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
            'complains'])->find($id);
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
        $data = $request->all();
        $data['updated_by_id'] = $user->id;
        $job->update($data);
        return $job;
        //
    }

    public function startEnd(Request $request, $id)
    {
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

        if ($request->action === 'start') {
            $job->actual_start_date = $request->date;
        } else {
            $job->actual_end_date = $request->date;
        }
        $job->updated_by_id = $user->id;
        $job->save();
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
    public function destroy($id)
    {
        $response = Job::destroy($id);
        if ($response) {
            return response(['message' => 'Deleted Successfully'], 200);
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }
}
