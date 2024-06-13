<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Throwable;
use App\Models\User;
use App\Models\Referral;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class SettingController extends AdminBaseController
{

    public function __construct(protected Settings $model)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $settings = $this->model->adminSettings();

        return $this->successResponse(trans('web.list_of_settings', [], $this->language), $settings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function store(Request $request): JsonResponse
    {
        $isRemoveRef = false;

        foreach ($request->all() as $index => $item) {
            $this->model->updateOrCreate(['key' => $index],[
                'value' => $item
            ]);

            if ($index === 'referral_active' && $item) {
                $isRemoveRef = true;
            }
        }

        try {
            if ($isRemoveRef) {

                $deActiveReferral = Referral::first();

                if (!empty($deActiveReferral)) {
                    User::withTrashed()->whereNotNull('my_referral')->select(['my_referral', 'id'])
                        ->chunkMap(function (User $model) {
                            $model->update([
                                'my_referral' => null
                            ]);
                        }, 100);
                }

            }
        } catch (Throwable $e) {
            $this->error($e);
        }

        try {
            cache()->delete('admin-settings');
        } catch (Throwable $e) {
            $this->error($e);
        }

       return $this->successResponse(trans('web.record_has_been_successfully_created', [], $this->language));

    }

    public function systemInformation()
    {
        return Cache::remember('server-info', 84600, function (){
            // get MySql version from DataBase
            $mysql = DB::selectOne( DB::raw('SHOW VARIABLES LIKE "%innodb_version%"'));

            return $this->successResponse("success", [
                'PHP Version' => phpversion(),
                'Laravel Version' => app()->version(),
                'OS Version' => php_uname(),
                'MySql Version' => $mysql->Value,
                'NodeJs Version' =>  exec('node -v'),
                'NPM Version' => exec('npm -v'),
                'Composer Version' => exec('composer -V'),
            ]);
        });
    }
}
