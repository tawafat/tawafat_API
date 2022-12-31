<?php

namespace App\Http\Controllers;

use App\Models\Complain;
use Illuminate\Http\Request;

class ComplainController extends Controller
{

    public function index()
    {
        return Complain::with([
            'job', 'created_by'])->get()->sortByDesc('id')->map(function ($v) {
                $v['attachment_url'] = url('api/attach/' .  $v['attachment_id']);
                return $v;
        })->values();
    }


    public function store(Request $request)
    {
        $request->validate([
            'comment' => 'required',
            'attachment_id' => 'nullable|numeric',
            'attachment_file' => 'nullable|file|mimes:jpg,png,jpeg,gif,svg,mp3,pdf|max:2048',
            'attachment_type' => 'string|nullable',
            'job_id' => 'required',
        ]);
        $data = $request->all();
        $data['created_by_id'] = $request->user()->id;

        if (isset($data['attachment_file']) && isset($data['attachment_type'])) {
            $fileData = [
                "type" => $data['attachment_type'],
                "folder" => 'complains',
                "note" => 'complains from job id '. $data['job_id'],
                ];
            $attach = app('App\Http\Controllers\AttachController')->
            uploadFile($request->file('attachment_file'), $fileData, $data['created_by_id'] );
            $data['attachment_id'] = $attach['id'];;
        }


        return Complain::create($data);
    }


    public function show($id)
    {
        $complain = Complain::with(['job', 'created_by' /*, 'attachment' */ ])->find($id);
        $complain['attachment_url'] = url('api/attach/' . $complain['attachment_id']);
        return $complain;
    }


    public function update(Request $request, $id)
    {
        $complain = Complain::find($id);
        if ($complain) {
            $complain->update($request->all());
            return $complain;
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }


    public function destroy($id)
    {
        $response = Complain::destroy($id);
        if ($response) {
            return response(['message' => 'Deleted Successfully'], 200);
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }
}
