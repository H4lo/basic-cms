<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Validator;
use App\Event;

class EventController extends Controller
{

    /**
     * 一覧画面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $events = [];
        $data = Event::all();
        if ($data->count()) {
            foreach ($data as $key => $val) {
                $events[$val->id] = [
                    'title' => $val->title,
                    'start' => $val->date,
                ];
            }
        }

        return view('backend.event.index', compact('events'));

    }

    /**
     * 新規作成実行
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Event::getValidationRules(true));
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            Event::create(['title' => $request->title, 'date' => getStdDate($request->date)]);
            DB::commit();
            return redirect('/event');

        } catch (\Exception $e) {

            Log::error($e->getMessage());
            DB::rollback();
            return redirect()->back()->with('flashErrMsg', '登録に失敗しました。');

        }

    }

    /**
     * 編集実行
     *
     * @param Request $request
     * @param Event $event
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), Event::getValidationRules());
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        $event->update(['title' => $request->title, 'date' => getStdDate($request->date)]);

        try {



            DB::commit();
            return redirect('/event');

        } catch (\Exception $e) {

            Log::error($e->getMessage());
            DB::rollback();
            return redirect()->back()->with('flashErrMsg', '登録に失敗しました。');

        }
    }

    /**
     * 削除実行
     *
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect('/event');
    }
}
