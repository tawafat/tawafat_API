<?php

namespace App\Http\Controllers;

use App\Models\Attach;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachController extends Controller
{
    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:jpg,png,jpeg,gif,svg,mp3,pdf|max:2048',
            'type' => 'required',
            'folder' => 'nullable',
            'note' => 'nullable',
            'description' => 'nullable',
        ]);
        $d = $request->all();
        $file = $request->file('file');
        $data = $this->uploadFile($file, $d, $request->user()->id);

        return response($data, Response::HTTP_CREATED);
    }


    public function preview($id)
    {

        $file = Attach::find($id);
        $storagePath = storage_path('app\public\\' . $file['url']);
        $file->counter = $file->counter + 1;
        $file->save();
        //  return $storagePath;
        return response()->file($storagePath);
    }


    public function show($id)
    {

        $file = Attach::find($id);
        $storagePath = storage_path('app/public/' . $file['url']);
        $file->counter = $file->counter + 1;
        $file->save();
        $file['attachment_url'] = url('api/attach/' . $file['id']);

        return $file;
    }

    public function index()
    {
        return Attach::all()->sortByDesc('id')->values();
    }

    // service
    public function uploadFile($file, $d, $userId)
    {
        $image_path = $file->store('uploads/' . ($d['folder'] ?? 'general'), 'public');
        $data = Attach::create([
            'name' => $file->getClientOriginalName(),
            'type' => $d['type'],
            'url' => $image_path,
            'size' => $file->getSize(),
            'folder' => $d['folder'] ?? 'general',
            'note' => $d['note'] ?? null,
            'description' => $d['description'] ?? null,
            'created_by_id' => $userId,
            'counter' => 0,
        ]);

        return $data;
    }
}
