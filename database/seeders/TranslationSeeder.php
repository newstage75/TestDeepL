<?php

namespace Database\Seeders;

use App\Models\Translation;
use App\Models\TranslationItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $jp_texts = [
          'login' => 'ログイン',
          'logout' => 'ログアウト',
          'sign_up' => 'ユーザー登録',
          'contact' => 'お問い合わせ',
          'to_password_reminder' => 'パスワードを忘れましたか？'
      ];

        foreach($jp_texts as $key => $text) {

                  $translation = new Translation();
                  $translation->key = $key;
                  $translation->save();

                  $translation_item = new TranslationItem();
                  $translation_item->translation_id = $translation->id;
                  $translation_item->locale = 'ja';
                  $translation_item->text = $text;
                  $translation_item->save();

              }
    }
}
