<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class Topimage extends Model
{
    /**
     * ステータス
     */
    const OPEN = 1;
    const CLOSE = 99;

    static $statusList = [
        self::OPEN => '公　開',
        self::CLOSE => '非公開',
    ];

    /**
     * トップ画像を格納するディレクトリパス
     *
     * @var string
     */
    public $baseFilePath = '/files/topimage/';

    /**
     * トップ画像で使うファイル名
     *
     * @var string
     */
    public $baseFileName = 'original';

    /**
     * バリデーションメッセージ
     *
     * @var array
     */
    static $validationMessages = [
        //
    ];

    static function getValidationRules()
    {
        return [
            'name' => 'required',
            'status' => 'required',
//            'topimage' => 'required',
        ];
    }

    /**
     * トップ画像を保存するメソッド
     *
     * @param Request $request
     */
    public function saveAll(Request $request)
    {
        //IDが欲しいので一旦保存
        $this->name = $request->name;
        $this->status = $request->status;

        //画像が設定されている場合は拡張子を保存
        if ($request->hasFile('topimage')) {
            $this->extention = $request->file('topimage')->getClientOriginalExtension();
        }

        $this->save(); //IDが欲しいので一旦保存

        //ファイル処理
        if ($request->hasFile('topimage')) {

            $uploadDir = public_path() . $this->baseFilePath . $this->id . '/';
            $fileName = $this->baseFileName . '.' . $this->extention;

            //ディレクトリが空でなければ一旦空にする
            if (File::exists($uploadDir) && !empty(File::files($uploadDir))) {
                File::cleanDirectory($uploadDir);
            }

            //公開ディレクトリに移動、保存
            $request->file('topimage')->move($uploadDir, $fileName);

            /**
             * リサイズ処理
             */
//            $image = Image::make($uploadDir . $fileName);
//            $image->crop(750, 500)->save($uploadDir . 'h700.' . $this->extention);
        }

    }
}