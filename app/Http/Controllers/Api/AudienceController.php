<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;
use Dcat\EasyExcel\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AudienceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Audience  $audience
     * @return \Illuminate\Http\Response
     */
    public function show(Audience $audience)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Audience  $audience
     * @return \Illuminate\Http\Response
     */
    public function edit(Audience $audience)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Audience  $audience
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Audience $audience)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Audience  $audience
     * @return \Illuminate\Http\Response
     */
    public function destroy(Audience $audience)
    {
        //
    }

    public function upload(Request $request)
    {
        set_time_limit(0);
        $user = Auth::user();
        $file = $request->file('idfa_file');
        $realPath = $file->getRealPath();

        $name = $file->getClientOriginalName();
        $patharr = explode('.', $name);
        $appid = array_shift($patharr);
        $file_content = file_get_contents($realPath);
        $batchNo = $user->id . '-' . time();
        $data = static::strToCsvArray($file_content);
        $idfas = [];
        foreach ($data as $key => $value) {
            if ($value['IDFA'] == '00000000-0000-0000-0000-000000000000') {
                continue;
            }
            $idfas[]  = $value['IDFA'];
        }
        $idfas = array_unique($idfas);
        Log::info(count($idfas));
        $insertdata = [];
        try {
            foreach ($idfas as $key => $value) {
                $insertdata[] = ['idfa' => $value, 'batch_no' => $batchNo, 'tag' => $appid];
            }
            $chunkdata =  array_chunk($insertdata, 10000);

            foreach ($chunkdata as $key => $value) {
                $redisValue = array_column($value, 'idfa');
                $dbRes = DB::table('a_idfa')->insert($value);
                // $redisRes = Redis::connection('default')->sadd('app_audience_blocklist_' . $appid, $redisValue);
                Redis::connection('feature')->pipeline(function($pipe) use ($appid,$redisValue){
                    foreach ($redisValue as $key => $value) {
                        $pipe->sadd('app_audience_blocklist_' . $appid, $value);
                    }
                });
                Log::info($dbRes);
                // Log::info($redisRes);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['code' => 1000, 'msg' => 'Fail']);
        }
        return response()->json(['code' => 0, 'msg' => 'Successful']);

        // dd($request->all());
        // $allSheets = Excel::import($request->file('idfa_file'))->toArray();

    }

    /**
     * CSV字符串转数组
     * @param $content
     * @return array
     */
    public static function strToCsvArray($content)
    {
        ini_set('memory_limit', '1024M');
        $content = trim($content);
        $data = explode("\n", $content);
        $csv = array_map('str_getcsv', $data);
        // dd($csv);
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header
        return $csv;
    }
}
