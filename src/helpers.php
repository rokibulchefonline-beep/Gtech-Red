<?php
declare(strict_types=1);

function e(?string $s): string
{
    return App\Site::e($s);
}

function slug(string $s): string
{
    return App\Site::slug($s);
}
