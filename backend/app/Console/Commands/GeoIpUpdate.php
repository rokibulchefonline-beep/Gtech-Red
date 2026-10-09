<?php

namespace App\Console\Commands;

use App\Support\Analytics\Geo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/** Downloads the free DB-IP "IP to Country Lite" database (CC BY 4.0, https://db-ip.com) used to tell visitors' countries. */
class GeoIpUpdate extends Command
{
    protected $signature = 'gtech:geoip-update';
    protected $description = 'Download the latest free DB-IP country database for website analytics';

    public function handle(): int
    {
        @mkdir(dirname(Geo::path()), 0755, true);
        foreach ([now(), now()->subMonthNoOverflow()] as $month) {
            $url = 'https://download.db-ip.com/free/dbip-country-lite-'.$month->format('Y-m').'.mmdb.gz';
            try {
                $res = Http::timeout(120)->get($url);
            } catch (\Throwable $e) {
                $this->warn("Could not reach $url: ".$e->getMessage());
                continue;
            }
            if (! $res->successful()) continue;
            $data = @gzdecode($res->body());
            if (! $data || strlen($data) < 100000) continue;
            $tmp = Geo::path().'.tmp';
            file_put_contents($tmp, $data);
            rename($tmp, Geo::path());
            Geo::reset();
            $this->info('Country database updated ('.$month->format('F Y').', '.round(strlen($data) / 1048576, 1).' MB).');
            return self::SUCCESS;
        }
        $this->error('Could not download the country database. Check the server can reach download.db-ip.com, then try again.');
        return self::FAILURE;
    }
}
