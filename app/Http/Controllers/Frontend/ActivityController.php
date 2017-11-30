<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Activity;
use Intervention\Image\Exception\NotFoundException;

class ActivityController extends Controller
{

    /**
     * １ページに表示する数
     */
    const INDEX_PAGINATION = 24;
    const DETAIL_ACT_NUM = 6;

    /**
     * 一覧画面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $activities = Activity::open()->paginate(self::INDEX_PAGINATION);
        return view('frontend.activity.index', compact('activities'));
    }

    /**
     * 詳細画面
     *
     * @param Activity $activity
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(Activity $activity)
    {
        //一覧で表示する用のグループ
        $activities = Activity::where('id', '!=', $activity->id)
            ->open()
            ->take(self::DETAIL_ACT_NUM)
            ->get();

        //単記事表示用。公開ステータスのものしか表示しない
        $singleAct = Activity::open()->find($activity->id);

        //記事がない場合は例外を投げる
        if(!$singleAct){
            throw new NotFoundException('指定されたURLは無効です。URLを確認してください。');
        }

        return view('frontend.activity.detail', compact('activities', 'singleAct'));
    }
}
