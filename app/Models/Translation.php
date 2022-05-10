<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    const LOCALES = [ //サポートする言語
      'ja',
      'en',
      'zh', //中国語
    ];

    // Relationship
    public function items()
    {
      return $this->hasMany(TranslationItem::class, 'translation_id', 'id');
    }
    // Others
    public static function getConfigData($locale) //翻訳データをkey=>value形式にして取得
    {
      $translations = Translation::whereHas('items', function($query) use($locale){
          $query->where('locale', $locale);
      })
      ->get();
      return $translations->map(function($translation) use($locale){
        return[
          'key' => $translation->key,
          'text' => $translation->items
            ->firstWhere('locale', $locale)
            ->text
        ];
      })
      ->pluck('text', 'key');
    }
}
