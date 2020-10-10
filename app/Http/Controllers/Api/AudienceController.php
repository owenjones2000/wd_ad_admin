<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AudienceRedis;
use App\Laravue\JsonResponse;
use App\Models\Advertise\App;
use App\Models\Audience;
use App\Models\Idfa;
use App\Models\IdfaLog;
use App\Models\IdfaTag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Dcat\EasyExcel\Excel;
use Dcat\EasyExcel\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;
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

    public function upload(Request $request)
    {
        ini_set('memory_limit', '1024M');
        $this->validate($request, [
            'idfa_file' => 'required|file
            ',
            'tag' => 'required|string|max:20',
        ]);
        $user = Auth::user();
        $file = $request->file('idfa_file');
        $realPath = $file->getRealPath();
        $batchNo = $user->id . '-' . time();
        // $name = $file->getClientOriginalName();
        // dd($file->getClientMimeType());
        // $patharr = explode('.', $name);
        // $appid = array_shift($patharr);
        $tagName = $request->input('tag');
        $tag = IdfaTag::firstOrCreate(['name' => $tagName]);
        $count = 0;
        $idfas = [];
        try {
            if (($handle = fopen($realPath, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 100, ",")) !== FALSE) {
                    if ($data[0] == '00000000-0000-0000-0000-000000000000' || $data[0] == 'IDFA') {
                        continue;
                    }
                    
                    $idfas[] = $data[0];
                    $num = count($idfas);
                    if ($num >= 10000) {
                        $count += $this->insertInto($idfas, $batchNo, $tag->id);
                        $idfas = [];
                    }
                }
                fclose($handle);
            }
            $count += $this->insertInto($idfas, $batchNo, $tag->id);
            DB::table('a_idfa_log')->insert([
                'batch_no' => $batchNo, 'tag' => $tagName, 'count' => $count,
                'created_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['code' => 1000, 'msg' => 'Fail']);
        }
        return response()->json(['code' => 0, 'msg' => 'Successful']);
    }

    public function uploadApp(Request $request)
    {
        $this->validate($request, [
            'idfa_file' => 'required|file
            ',
            'apps' => 'required|string',
        ]);
        
        $user = Auth::user();
        $file = $request->file('idfa_file');
        $realPath = $file->getRealPath();
        $batchNo = $user->id . '-' . time();
        $count = 0;
        $idfas = [];
        $appIds = explode(',', $request->input('apps'));
        try {
            if (($handle = fopen($realPath, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 100, ",")) !== FALSE) {
                    if ($data[0] == '00000000-0000-0000-0000-000000000000' || $data[0] == 'IDFA') {
                        continue;
                    }

                    $idfas[] = $data[0];
                    $num = count($idfas);
                    if ($num >= 10000) {
                        $count += $this->insertIntoRedis($idfas,  $appIds);
                        $idfas = [];
                    }
                }
                fclose($handle);
            }
            $count += $this->insertIntoRedis($idfas, $appIds);
            // DB::table('a_idfa_log')->insert([
            //     'batch_no' => $batchNo, 'tag' => $tagName, 'count' => $count,
            //     'created_at' => Carbon::now(),
            // ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['code' => 1000, 'msg' => 'Fail']);
        }
        return response()->json(['code' => 0, 'msg' => 'Successful']);
        
    }

    public function insertInto($idfas, $batchNo, $tagId)
    {
        $idfas = array_unique($idfas);
        Log::info('count ' . count($idfas));
        $insertdata = [];
        $redisValue = [];
        foreach ($idfas as $key => $value) {
            $insertdata[] = ['idfa' => $value, 'batch_no' => $batchNo, 'tag_id' => $tagId];
            $redisValue[] = $value;
        }
        
        $dbRes = DB::table('a_idfa')->insert($insertdata);
        // $redisRes = Redis::connection('feature')->sadd('app_audience_blocklist_' . $appid, ...$redisValue);
        // $redisRes = Redis::connection('default')->sadd('app_audience_blocklist_' . $tag, ...$redisValue);

        Log::info('dbres  ' . $dbRes);
        // Log::info('redisres  ' . $redisRes);
        return count($idfas);
    }

    public function insertIntoRedis($idfas, $appIds)
    {
        $idfas = array_unique($idfas);
        Log::info('count ' . count($idfas));
        foreach ($appIds as $key => $appid) {
            $redisRes = Redis::connection('feature')->sadd('app_audience_blocklist_' . $appid, ...$idfas);
            Log::info('redisres  ' . $redisRes. 'appid  '. $appid);
        }
        return count($idfas);
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

    public function idfaLog(Request $request)
    {
        $list =  IdfaLog::orderBy('id', 'desc')->paginate($request->get('limit', 10));
        return JsonResource::collection($list);
    }

    public function getApp(Request $request)
    {
        $apps = App::query()
        ->where('status', 1)
        ->where('is_admin_disable', 0) 
        ->get();
        return JsonResource::collection($apps);
    }

    public function taglist(Request $request)
    {
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', 30);
        $keyword = Arr::get($searchParams, 'keyword', '');
        $query = IdfaTag::query();
        if (!empty($keyword)) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }
        $query->withCount('idfas');
        return JsonResource::collection($query->paginate($limit));
    }

    public function tagApps(Request $request, $id)
    {
        $idfatag = IdfaTag::findOrFail($id);
        return new JsonResponse([
            'app' => $idfatag->apps,
        ]);
    }

    public function updateTagApps(Request $request, $id)
    {
        $appIds = $request->get('apps', []);
        $tag = IdfaTag::findOrFail($id);
        $oldAppIds = $tag->apps->pluck('id')->toArray();
        
        $newappIds = array_diff($appIds, $oldAppIds);
        if ($newappIds){
            dispatch(new AudienceRedis($id, $newappIds));
            // $job = new AudienceRedis($id, $newappIds);
            // $job ->dispatch();
        }
        $tag->apps()->sync($appIds);

        return new JsonResponse([
            'tag' => $tag
        ]);
    }
}
