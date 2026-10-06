<?php

namespace App\Filament\Support;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use FilamentTiptapEditor\Actions\LinkAction;
use FilamentTiptapEditor\TiptapEditor;

/** The article editor's link box with only what editors need: the address and "open in a new tab". */
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
                TextInput::make('href')->label('Link address')->required()->autofocus()
                    ->placeholder('https://… or /services/seo')
                    ->helperText('A full address, or a page on this site such as /services/local-seo.')
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
}
