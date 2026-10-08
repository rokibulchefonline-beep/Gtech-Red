<?php

return [
    'direction' => 'ltr',
    'max_content_width' => '5xl',
    'disable_stylesheet' => false,
    'disable_link_as_button' => true,

    /*
    |--------------------------------------------------------------------------
    | Profiles
    |--------------------------------------------------------------------------
    |
    | Profiles determine which tools are available for the toolbar.
    | 'default' is all available tools, but you can create your own subsets.
    | The order of the tools doesn't matter.
    |
    */
    'profiles' => [
        'default' => [
            'heading', 'bullet-list', 'ordered-list', 'checked-list', 'blockquote', 'hr', '|',
            'bold', 'italic', 'strike', 'underline', 'superscript', 'subscript', 'lead', 'small', 'color', 'highlight', 'align-left', 'align-center', 'align-right', '|',
            'link', 'media', 'oembed', 'table', 'grid-builder', 'details', '|', 'code', 'code-block', 'source', 'blocks',
        ],
        'simple' => ['heading', 'hr', 'bullet-list', 'ordered-list', 'checked-list', '|', 'bold', 'italic', 'lead', 'small', '|', 'link', 'media'],
        'minimal' => ['bold', 'italic', 'link', 'bullet-list', 'ordered-list'],
        'none' => [],
        // Blog articles: only what the public site keeps after cleaning the HTML (no embeds, grids or styled blocks).
        'blog' => ['heading', 'bullet-list', 'ordered-list', 'blockquote', 'hr', '|', 'bold', 'italic', 'underline', 'strike', 'highlight', 'align-left', 'align-center', 'align-right', '|', 'link', 'media', 'table', '|', 'source'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    |
    */
    'media_action' => App\Filament\Support\SiteMediaAction::class,
    //    'media_action' => Awcodes\Curator\Actions\MediaAction::class,
    'edit_media_action' => App\Filament\Support\SiteEditMediaAction::class,
    'link_action' => App\Filament\Support\SimpleLinkAction::class,
    'grid_builder_action' => FilamentTiptapEditor\Actions\GridBuilderAction::class,
    'oembed_action' => FilamentTiptapEditor\Actions\OEmbedAction::class,

    /*
    |--------------------------------------------------------------------------
    | Output format
    |--------------------------------------------------------------------------
    |
    | Which output format should be stored in the Database.
    |
    | See: https://tiptap.dev/guide/output
    */
    'output' => FilamentTiptapEditor\Enums\TiptapOutput::Html,

    /*
    |--------------------------------------------------------------------------
    | Media Uploader
    |--------------------------------------------------------------------------
    |
    | These options will be passed to the native file uploader modal when
    | inserting media. They follow the same conventions as the
    | Filament Forms FileUpload field.
    |
    | See https://filamentphp.com/docs/3.x/panels/installation#file-upload
    |
    */
    // No SVG (it can carry scripts when opened directly) and no PDF (not an image).
    'accepted_file_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    'disk' => 'public',
    'directory' => 'images',
    'visibility' => 'public',
    'preserve_file_names' => false,
    'max_file_size' => 2042,
    'min_file_size' => 0,
    'image_resize_mode' => null,
    'image_crop_aspect_ratio' => null,
    'image_resize_target_width' => null,
    'image_resize_target_height' => null,
    'use_relative_paths' => true,

    /*
    |--------------------------------------------------------------------------
    | Menus
    |--------------------------------------------------------------------------
    |
    */
    'disable_floating_menus' => false,
    'disable_bubble_menus' => false,
    'disable_toolbar_menus' => false,

    'bubble_menu_tools' => ['bold', 'italic', 'underline', 'link'],
    'floating_menu_tools' => ['media', 'table'],

    /*
    |--------------------------------------------------------------------------
    | Extensions
    |--------------------------------------------------------------------------
    |
    */
    'extensions_script' => null,
    'extensions_styles' => null,
    'extensions' => [],

    /*
    |--------------------------------------------------------------------------
    | PresetColors
    |--------------------------------------------------------------------------
    |
    | Possibility to define presets colors in ColorPicker.
    | Only hexadecimal value
    'preset_colors' => [
        'primary' => '#f59e0b',
        //..
    ]
    |
    */
    'preset_colors' => [],

    /*
    |--------------------------------------------------------------------------
    | Protocols
    |--------------------------------------------------------------------------
    |
    | With newer versions of Tiptap, you need to define additional protocols
    | for the link extension. i.e. 'ftp', 'mailto', etc.
    |
    */
    'link_protocols' => [],
];
