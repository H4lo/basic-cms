<?php

namespace App\Http\Controllers\Backend;

use App\Picture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Validator;
use App\Activity;

class ActivityController extends Controller
{
    /**
     * １ページに表示する件数
     */
    const PAGINATION = 20;

    /**
     * 画像アップロードの際のkey名
     *
     * @var string
     */
    protected $pictParamName = 'file';

    /**
     * 一覧画面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $activities = Activity::paginate(self::PAGINATION);
        return view('backend.activity.index', compact('activities'));
    }

    /**
     * 新規作成画面表示
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $activity = new Activity;
        return view('backend.activity.edit', compact('activity'));
    }

    /**
     * 新規作成実行
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {

        dd($request);

        $validator = Validator::make($request->all(), Activity::getValidationRules(true));
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            $activity = new Activity;
            $activity->saveAll($request);

            DB::commit();
            return redirect('/activity')->with('flashMsg', '登録が完了しました。');

        } catch (\Exception $e) {

            Log::error($e->getMessage());
            DB::rollback();
            return redirect()->back()->with('flashErrMsg', '登録に失敗しました。');

        }

    }

    /**
     * 編集画面表示
     *
     * @param Activity $activity
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Activity $activity)
    {
        return view('backend.activity.edit', compact('activity'));
    }

    /**
     * 編集実行
     *
     * @param Request $request
     * @param Activity $activity
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Activity $activity)
    {
        $validator = Validator::make($request->all(), Activity::getValidationRules());
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            $activity->saveAll($request);

            DB::commit();
            return redirect('/activity')->with('flashMsg', '登録が完了しました。');

        } catch (\Exception $e) {

            Log::error($e->getMessage());
            DB::rollback();
            return redirect()->back()->with('flashErrMsg', '登録に失敗しました。');

        }
    }

    /**
     * 削除実行
     *
     * @param Activity $activity
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Activity $activity)
    {
        if (File::exists($activity->uploadDir . $activity->id)) {
            File::deleteDirectory($activity->uploadDir . $activity->id);
        }

        $activity->delete();

        return redirect('/activity')->with('flashMsg', '削除が完了しました。');
    }

    /**
     * Dropzone.jsからXHRで渡された画像を一時ディレクトリに保存するメソッド
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pictStore(Request $request)
    {
        //画像名生成。YmdHis + 画像の元の拡張子
        $pictTmpName = date('YmdHis') . '.' . $request->file($this->pictParamName)->getClientOriginalExtension();

        //画像をstorageの一時ファイルに保存
        $request->file($this->pictParamName)->move(storage_path('app/public/act-pict-tmp'), $pictTmpName);

        return response()->json(['code' => '200', 'pictTmpName' => $pictTmpName]);
    }

}
