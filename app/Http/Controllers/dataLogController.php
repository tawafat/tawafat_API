<?php

namespace App\Http\Controllers;

use App\Models\DataLog;
use Exception;


class dataLogController
{
    //
    public static function createLog(string $action, string $type,string $predicateTableName, $predicateId, $user, $data, $request)
    {
        $e = (new Exception)->getTrace()[1];

        $functionName = $e['class'] . '\\' . $e['function'];
        $requestInfo = ['functionName' => $functionName, 'requestBody' => $request->all(), 'url' => $request->url()];
        $data['requestInfo'] = $requestInfo;
        $dataJson = json_encode($data);
        return DataLog::create([
            'action' => $action,
            'type' => $type,
            'data' => $dataJson,
            'created_by_id' => $user->id,
            'predicate_table_name' =>$predicateTableName,
            'predicate_id' =>  strval($predicateId),
            'table_name' => $predicateTableName,
            'col_id' => $predicateId
        ]);


    }
}
