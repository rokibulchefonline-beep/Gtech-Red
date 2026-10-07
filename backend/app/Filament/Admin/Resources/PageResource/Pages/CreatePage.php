<?php

namespace App\Filament\Admin\Resources\PageResource\Pages;

use App\Filament\Admin\Resources\PageResource;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A new landing page at an address you choose, started blank or as a copy of any page's sections. It is created
 * unpublished: build it, preview it, then publish.
 */
class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'New landing page';
    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        parent::mount();
        if ($from = request()->query('from')) {
            $src = Page::query()->find($from);
            if ($src) $this->form->fill(['from' => $src->key, 'name' => $src->name.' (copy)', 'slug' => Str::slug($src->slug.'-copy')]);
        }
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->label('Page name')->required()->maxLength(120)->live(onBlur: true)
                    ->afterStateUpdated(fn (Get $get, Set $set, ?string $state) => $get('slug') ? null : $set('slug', Str::slug((string) $state))),
                Forms\Components\TextInput::make('slug')->label('Address')->required()->maxLength(80)->prefix(rtrim(config('gtech.public_url'), '/').'/')
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->validationMessages(['regex' => 'Use lower-case letters, numbers and hyphens, e.g. free-seo-audit.'])
                    ->rule(fn () => function ($attr, $v, $fail) {
                        if (in_array($v, PageResource::RESERVED, true)) return $fail('This address belongs to the website. Choose another one.');
                        if (Page::query()->where('path', "/$v")->exists()) return $fail('A page already uses this address.');
                    }),
                Forms\Components\Select::make('from')->label('Start from')->placeholder('A blank page')->searchable()
                    ->options(fn () => Page::query()->whereIn('kind', Page::BUILDER_KINDS)->orderByRaw("kind = 'landing' desc")->orderBy('name')->get()
                        ->mapWithKeys(fn (Page $p) => [$p->key => $p->name.' ('.(Page::KINDS[$p->kind] ?? $p->kind).')'])->all())
                    ->helperText('Copies the hero, sections, FAQs and related services, to change as you like.'),
            ])->columns(1)->maxWidth('2xl'),
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $src = ! empty($data['from']) ? Page::query()->find($data['from'])?->withDraft() : null;
        $slug = $data['slug'];
        $name = trim($data['name']);
        $hero = $src ? array_merge((array) $src->hero, ['h1' => $name]) : ['h1' => $name, 'lead' => '', 'points' => [], 'motion' => ''];
        Page::$revisionLabel = $src ? 'Created as a copy of '.$src->name : 'Created';
        return Page::query()->create([
            'key' => "landing~$slug", 'kind' => 'landing', 'slug' => $slug, 'name' => $name, 'path' => "/$slug", 'sort' => 0, 'published' => false,
            'meta_title' => '', 'meta_description' => $src->meta_description ?? '', 'focus_keyword' => '',
            'hero' => $hero,
            'sections' => $src ? (array) $src->sections : [['type' => 'text', 'id' => 'overview', 'heading' => 'Why choose [[GTech Digital]]', 'paras' => ['Write your opening paragraph here.']]],
            'faqs' => $src ? (array) $src->faqs : [], 'related' => $src ? (array) $src->related : [],
            'data' => $src && $src->kind === 'landing' ? (array) $src->data : ($src && $src->kind === 'service' ? ['service' => $src->name, 'service_slug' => $src->slug] : ['service' => '']),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return PageResource::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Page created. Build it, preview it, then publish.';
    }
}
