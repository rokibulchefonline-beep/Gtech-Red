<?php

namespace App\Filament\Support;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Forms\Components\Toggle;
use FilamentTiptapEditor\Actions\LinkAction;
use FilamentTiptapEditor\TiptapEditor;

/**
 * The article editor's link box with only what editors need: pick a page of this site (internal link) or type an
 * address, and "open in a new tab".
 */
class SimpleLinkAction extends LinkAction
{
    protected function setUp(): void
    {
        parent::setUp();
        $this
            ->modalWidth('md')
            ->mountUsing(fn (ComponentContainer $form, array $arguments) => $form->fill([
                'href' => $arguments['href'] ?? '',
                'new_tab' => ($arguments['target'] ?? '') === '_blank',
            ]))
            ->form([
                Select::make('internal')->label('Link to a page on this site')->placeholder('Search services, industries, posts…')
                    ->searchable()->options(fn () => self::sitePages())->live()->dehydrated(false)
                    ->afterStateUpdated(fn (?string $state, Set $set) => $state ? $set('href', $state) : null),
                TextInput::make('href')->label('Link address')->required()->autofocus()
                    ->placeholder('https://… or /services/seo')
                    ->helperText('Filled in when you pick a page above, or type any full address.')
                    ->rule('not_regex:/^\s*(javascript|data|vbscript):/i')->validationMessages(['not_regex' => 'That kind of link is not allowed.']),
                Toggle::make('new_tab')->label('Open in a new tab'),
            ])
            ->action(function (TiptapEditor $component, array $data, array $arguments) {
                $component->getLivewire()->dispatch(
                    event: 'insertFromAction',
                    type: 'link',
                    statePath: $component->getStatePath(),
                    href: trim($data['href']),
                    id: '',
                    hreflang: '',
                    target: $data['new_tab'] ? '_blank' : '',
                    rel: $data['new_tab'] ? 'noopener noreferrer' : '',
                    referrerpolicy: '',
                    as_button: false,
                    button_theme: '',
                    coordinates: $arguments['coordinates'] ?? [],
                );
                $component->state($component->getState());
            });
    }

    /** Every live page of the site, grouped, for the internal link picker: address => name. */
    public static function sitePages(): array
    {
        $kinds = ['page' => 'Main pages', 'landing' => 'Landing pages', 'service' => 'Services', 'industry' => 'Industries', 'legal' => 'Legal'];
        $out = [];
        foreach (\App\Models\Page::query()->where('published', true)->orderBy('sort')->orderBy('name')->get(['kind', 'name', 'path']) as $p) {
            $out[$kinds[$p->kind] ?? 'Pages'][$p->path] = $p->name;
        }
        foreach (['/case-studies' => 'Case studies', '/blogs' => 'Blog'] as $path => $name) $out['Main pages'][$path] = $name;
        $out['Blog posts'] = \App\Support\Site\Repo::posts()->mapWithKeys(fn ($p) => ['/blogs/'.$p->slug => $p->title])->all();
        $out['Case studies'] = \App\Support\Site\Repo::caseStudies(200)->mapWithKeys(fn ($c) => ['/case-studies/'.$c->slug => $c->title])->all();
        return array_filter(array_merge(array_intersect_key(array_flip($kinds), $out), $out));
    }
}
