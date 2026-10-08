<x-filament-panels::page>
@php($counts = $this->counts)
@php($open = $this->open)
@php($emails = $this->emails)
@php($folders = ['inbox' => ['Inbox', 'heroicon-o-inbox'], 'sent' => ['Sent', 'heroicon-o-paper-airplane'], 'draft' => ['Drafts', 'heroicon-o-pencil-square'], 'starred' => ['Starred', 'heroicon-o-star'], 'trash' => ['Trash', 'heroicon-o-trash']])
@php($current = $starredOnly ? 'starred' : $folder)
<div class="gt-mail grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
    {{-- Folders --}}
    <aside class="space-y-3">
        @if (auth()->user()?->hasPerm('email.send'))
        <x-filament::button icon="heroicon-o-pencil" class="w-full" size="lg" wire:click="write">Write email</x-filament::button>
        @endif
        <nav class="rounded-xl bg-white p-2 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" aria-label="Folders">
            @foreach ($folders as $key => [$label, $icon])
            <button type="button" wire:click="setFolder('{{ $key }}')" @class(['flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition', 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' => $current === $key, 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => $current !== $key])>
                <x-filament::icon :icon="$icon" class="h-5 w-5" />
                <span class="flex-1 text-start">{{ $label }}</span>
                @if ($key === 'inbox' && $counts['inbox']['unread'])<span class="rounded-full bg-primary-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $counts['inbox']['unread'] }}</span>
                @elseif ($counts[$key]['n'])<span class="text-xs text-gray-500">{{ $counts[$key]['n'] }}</span>@endif
            </button>
            @endforeach
        </nav>
        @if ($counts['failed'])
        <p class="rounded-lg bg-danger-50 px-3 py-2 text-xs text-danger-700 dark:bg-danger-500/10 dark:text-danger-400">{{ $counts['failed'] }} sent {{ \Illuminate\Support\Str::plural('email', $counts['failed']) }} failed. Open one to see why and send it again.</p>
        @endif
        @if ($this->inboxReady())
        <x-filament::button color="gray" icon="heroicon-o-arrow-path" class="w-full" wire:click="checkMail" wire:loading.attr="disabled" wire:target="checkMail">Check for new mail</x-filament::button>
        <p class="px-1 text-xs text-gray-500">Checked automatically every 5 minutes.@if (\App\Models\Setting::group('imap')['lastChecked'] ?? null) Last: {{ \Illuminate\Support\Carbon::parse(\App\Models\Setting::group('imap')['lastChecked'])->diffForHumans() }}.@endif</p>
        @else
        <p class="px-1 text-xs text-gray-500">To see received email here, add your mailbox's IMAP details in <a class="text-primary-600 underline" href="{{ \App\Filament\Admin\Pages\Settings::getUrl() }}?tab=-email-tab">Site settings &gt; Email</a>.</p>
        @endif
    </aside>

    {{-- List or message --}}
    <section class="min-w-0 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if ($composing)
        {{-- Write, reply or forward --}}
        <div class="flex items-center gap-2 border-b border-gray-200 p-3 dark:border-white/10">
            <x-filament::icon-button icon="heroicon-o-x-mark" label="Close" color="gray" wire:click="closeCompose" />
            <h2 class="text-base font-semibold">{{ ($compose['draft_id'] ?? null) ? 'Edit draft' : 'New email' }}</h2>
        </div>
        <form wire:submit="send" class="space-y-4 p-4">
            {{ $this->composeForm }}
            <div class="flex flex-wrap gap-2">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled" wire:target="send">Send</x-filament::button>
                <x-filament::button type="button" color="gray" icon="heroicon-o-document" wire:click="saveDraft">Save draft</x-filament::button>
                <x-filament::button type="button" color="gray" wire:click="closeCompose">Discard</x-filament::button>
            </div>
        </form>
        @elseif ($open)
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 p-3 dark:border-white/10">
            <x-filament::icon-button icon="heroicon-o-arrow-left" label="Back to the list" color="gray" wire:click="closeOpen" />
            @if ($open->folder !== 'trash')
                @if (auth()->user()?->hasPerm('email.send'))
                <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-uturn-left" wire:click="reply({{ $open->id }})">Reply</x-filament::button>
                <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-uturn-left" wire:click="reply({{ $open->id }}, true)">Reply all</x-filament::button>
                <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-uturn-right" wire:click="forward({{ $open->id }})">Forward</x-filament::button>
                @endif
                @if ($open->status === 'failed' && auth()->user()?->hasPerm('email.send'))
                <x-filament::button size="sm" color="danger" icon="heroicon-o-arrow-path" wire:click="retry({{ $open->id }})">Send again</x-filament::button>
                @endif
                <x-filament::icon-button :icon="$open->starred ? 'heroicon-s-star' : 'heroicon-o-star'" :color="$open->starred ? 'warning' : 'gray'" label="Star" wire:click="toggleStar({{ $open->id }})" />
                @if ($open->folder === 'inbox')<x-filament::icon-button icon="heroicon-o-envelope" color="gray" label="Mark as unread" wire:click="markUnread({{ $open->id }})" />@endif
                <x-filament::icon-button icon="heroicon-o-trash" color="gray" label="Move to Trash" wire:click="trash({{ $open->id }})" />
            @else
                <x-filament::button size="sm" color="gray" icon="heroicon-o-arrow-uturn-up" wire:click="restore({{ $open->id }})">Restore</x-filament::button>
                @if (auth()->user()?->hasPerm('email.delete'))
                <x-filament::button size="sm" color="danger" icon="heroicon-o-trash" wire:click="deleteForever({{ $open->id }})" wire:confirm="Delete this email for good? This cannot be undone.">Delete for good</x-filament::button>
                @endif
            @endif
        </div>
        <article class="space-y-4 p-5">
            <h2 class="text-xl font-semibold">{{ $open->subject ?: '(no subject)' }}</h2>
            @if ($open->status === 'failed')
            <div class="rounded-lg bg-danger-50 p-3 text-sm text-danger-700 dark:bg-danger-500/10 dark:text-danger-400"><b>Not delivered.</b> {{ $open->error }}</div>
            @endif
            <div class="flex flex-wrap items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-600 text-sm font-semibold uppercase text-white">{{ mb_substr($open->folder === 'inbox' || $open->trashed_from === 'inbox' ? ($open->from_name ?: $open->from_email) : 'G', 0, 1) }}</span>
                <div class="min-w-0 flex-1 text-sm">
                    <p><b>{{ $open->from_name ?: ($open->from_email ?: 'GTech Digital') }}</b> @if ($open->from_email)<span class="text-gray-500">&lt;{{ $open->from_email }}&gt;</span>@endif</p>
                    <p class="text-gray-600 dark:text-gray-400">To: {{ $open->to }}</p>
                    @if ($open->cc)<p class="text-gray-600 dark:text-gray-400">Cc: {{ $open->cc }}</p>@endif
                    @if ($open->user)<p class="text-xs text-gray-500">Sent by {{ $open->user->name }}</p>@endif
                </div>
                <p class="text-xs text-gray-500">{{ $open->sent_at?->format('D j M Y, H:i') }}</p>
            </div>
            @if ($open->lead)
            <p class="text-sm"><a class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-gray-700 hover:bg-gray-200 dark:bg-white/5 dark:text-gray-300" href="{{ \App\Filament\Admin\Resources\LeadResource::getUrl('view', ['record' => $open->lead]) }}"><x-filament::icon icon="heroicon-o-inbox-arrow-down" class="h-4 w-4" />Lead: {{ $open->lead->name }} · {{ $open->lead->statusLabel() }}</a></p>
            @endif
            {{-- The message in a sandboxed frame: its own styles stay inside and scripts cannot run. --}}
            <iframe title="Message" sandbox="allow-popups allow-popups-to-escape-sandbox" class="w-full rounded-lg border border-gray-200 bg-white dark:border-white/10" style="min-height:420px"
                srcdoc="{{ '<!doctype html><html><head><meta charset="utf-8"><base target="_blank"><style>body{font:15px/1.6 Arial,sans-serif;color:#222;margin:16px;word-wrap:break-word}img{max-width:100%;height:auto}blockquote{border-left:3px solid #ddd;margin:0;padding-left:12px;color:#555}</style></head><body>'.$open->body.'</body></html>' }}"
                x-data x-on:load="$el.style.height = Math.max(420, $el.contentDocument?.body?.scrollHeight + 40 || 420) + 'px'"></iframe>
        </article>
        @else
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 p-3 dark:border-white/10">
            <h2 class="text-base font-semibold">{{ $folders[$current][0] }}</h2>
            <div class="ms-auto w-full sm:w-72">
                <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input type="search" wire:model.live.debounce.400ms="search" placeholder="Search email" />
                </x-filament::input.wrapper>
            </div>
            @if ($current === 'trash' && $counts['trash']['n'] && auth()->user()?->hasPerm('email.delete'))
            <x-filament::button size="sm" color="danger" icon="heroicon-o-trash" wire:click="emptyTrash" wire:confirm="Delete everything in Trash for good?">Empty Trash</x-filament::button>
            @endif
        </div>
        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            @forelse ($emails as $e)
            @php($incoming = $e->folder === 'inbox' || $e->trashed_from === 'inbox')
            <li @class(['group flex cursor-pointer items-center gap-3 px-3 py-3 hover:bg-gray-50 dark:hover:bg-white/5', 'bg-primary-50/40 dark:bg-primary-500/5' => $incoming && ! $e->read_at]) wire:click="open({{ $e->id }})" wire:key="m{{ $e->id }}">
                <button type="button" wire:click.stop="toggleStar({{ $e->id }})" class="shrink-0" aria-label="Star">
                    <x-filament::icon :icon="$e->starred ? 'heroicon-s-star' : 'heroicon-o-star'" @class(['h-5 w-5', 'text-warning-500' => $e->starred, 'text-gray-300 group-hover:text-gray-400 dark:text-gray-600' => ! $e->starred]) />
                </button>
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline gap-2">
                        <p @class(['truncate text-sm', 'font-semibold' => $incoming && ! $e->read_at])>
                            @if ($e->folder === 'draft')<span class="text-danger-600">Draft</span> · @endif
                            {{ $incoming ? ($e->from_name ?: $e->from_email) : 'To: '.\Illuminate\Support\Str::limit($e->to, 60) }}
                        </p>
                        @if ($e->status === 'failed')<x-filament::badge color="danger" size="sm">Failed</x-filament::badge>@endif
                        @if ($e->lead)<x-filament::badge color="gray" size="sm" icon="heroicon-o-inbox-arrow-down">{{ \Illuminate\Support\Str::limit($e->lead->name, 20) }}</x-filament::badge>@endif
                        <span class="ms-auto shrink-0 text-xs text-gray-500" title="{{ $e->sent_at?->format('D j M Y, H:i') }}">{{ $e->sent_at?->isToday() ? $e->sent_at->format('H:i') : $e->sent_at?->format('j M') }}</span>
                    </div>
                    <p class="truncate text-sm"><span @class(['font-semibold' => $incoming && ! $e->read_at])>{{ $e->subject ?: '(no subject)' }}</span> <span class="text-gray-500">— {{ $e->snippet(110) }}</span></p>
                </div>
                <span class="hidden shrink-0 gap-1 group-hover:flex">
                    @if ($e->folder === 'trash')
                    <x-filament::icon-button icon="heroicon-o-arrow-uturn-up" size="sm" color="gray" label="Restore" wire:click.stop="restore({{ $e->id }})" />
                    @else
                    <x-filament::icon-button icon="heroicon-o-trash" size="sm" color="gray" label="Move to Trash" wire:click.stop="trash({{ $e->id }})" />
                    @endif
                </span>
            </li>
            @empty
            <li class="px-6 py-16 text-center">
                <x-filament::icon :icon="$folders[$current][1]" class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
                <p class="mt-2 text-sm text-gray-500">{{ $search !== '' ? 'No email matches "'.$search.'".' : match ($current) { 'inbox' => $this->inboxReady() ? 'No received email yet.' : 'Set up the inbox in Site settings > Email to see received email here.', 'sent' => 'Emails the website and your team send appear here.', 'draft' => 'No drafts.', 'starred' => 'Star an email to keep it here.', default => 'Trash is empty.' } }}</p>
            </li>
            @endforelse
        </ul>
        @if ($emails->hasPages())<div class="border-t border-gray-200 p-3 dark:border-white/10">{{ $emails->links() }}</div>@endif
        @endif
    </section>
</div>

</x-filament-panels::page>
