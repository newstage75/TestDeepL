<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Models\TranslationItem;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class TranslationCommand extends Command
{
    private $from_locale =''; //翻訳元の言語
    private $to_locales = []; //翻訳先の言語

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate {--from=ja : The language to translate from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate text through DeepL';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $all_locales = Translation::LOCALES;
        $this->from_locale = $this->option('from');
        $this->to_locales = Arr::except($all_locales, [$this->from_locale]); //元言語を除外した言語

        if(!in_array($this->from_locale, $all_locales, true)){ //言語がサポーロされてない場合
          return Command::INVALID;
      }

      $translation = $this->getTranslation();

      if(is_null($translation)) {

          $this->info('Nothing to translate.');
          return Command::SUCCESS; // 翻訳すべきデータがない場合は終了

      }

      foreach ($this->to_locales as $to_locale) {

          $translation_item = $translation->items->where('locale', $to_locale)->first();

          if(is_null($translation_item)) { // 翻訳先の言語が存在しない場合

              $this->translate($translation, $to_locale);

          }

      }
      return Command::SUCCESS;
    }

    private function getTranslation()
    {
        return Translation::query()
            ->with('items')
            ->whereHas('items', function($query) {

                $query->where('locale', $this->from_locale); // 元言語が存在していて、

            })
            ->where(function($query) {

                foreach($this->to_locales as $to_locale) {

                    $query->orWhereDoesntHave('items', function($q) use ($to_locale) {

                        $q->where('locale', $to_locale); // 他言語が存在しないもの

                    });

                }

            })
            ->first();
    }

    private function translate(Translation $translation, $to_locale)
    {
        $from_text = $translation->items
            ->firstWhere('locale', $this->from_locale)
            ->text; // 翻訳元のテキスト

        $url = 'https://api-free.deepl.com/v2/translate';
        $params = [
            'auth_key' => env('DEEPL_AUTH_KEY'), // 本来は config/services.php などにセットすべきです
            'text' => $from_text,
            'source_lang' => $this->from_locale,
            'target_lang' => $to_locale,
        ];
        $response = Http::get($url, $params);

        if($response->ok()) {

            $response_data = $response->json();
            $translated_text = Arr::get($response_data, 'translations.0.text'); // ない場合は null が返される

            $translation_item = new TranslationItem();
            $translation_item->translation_id = $translation->id;
            $translation_item->locale = $to_locale;
            $translation_item->text = $translated_text;
            $translation_item->save();

            $this->info('Translated to '. $to_locale .': "'. $from_text .'" -> "'. $translated_text .'"');

        } else {

            $this->error('Oh, something went wrong...');

        }

    }
}
