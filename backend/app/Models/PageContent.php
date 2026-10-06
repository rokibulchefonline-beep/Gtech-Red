<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Copy overrides for a service, industry or main page. Key: "service~seo", "industry~finance", "page~home". */
class PageContent extends Model
{
    protected $table = 'page_contents';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'kind', 'slug', 'meta_title', 'meta_description', 'focus_keyword', 'hero', 'sections', 'faqs'];

    protected function casts(): array
    {
        return ['hero' => 'array', 'sections' => 'array', 'faqs' => 'array'];
    }
}
