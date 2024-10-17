<?php

namespace App\Console\Commands\bind9;
use Illuminate\Console\Command;

class SetupDefaultZones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:set-default-bind9-zone-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the default zones in /var/named';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $path = '/var/named';
        $named = view('server.samples.bind9.default-zones.named_zero', [])
            ->render();

        if(!file_put_contents($path . '/named.zero', $named)){
            throw new \Exception('Unable to write file \'' . $path . '/named.zero\'');
        }

        shell_exec("chown named:named {$path}/named.zero");
        shell_exec("chmod 644 {$path}/named.zero");


        $named = view('server.samples.bind9.default-zones.named_rfc1912_zones', [])
            ->render();

        if(!file_put_contents($path . '/named.rfc1912.zones', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.rfc1912.zones\'');
        }

        shell_exec("chown named:named {$path}/named.rfc1912.zones");
        shell_exec("chmod 644 {$path}/named.rfc1912.zones");


        $named = view('server.samples.bind9.default-zones.named_loopback', [])
            ->render();

        if(!file_put_contents($path . '/named.loopback', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.loopback\'');
        }

        shell_exec("chown root:named {$path}/named.loopback");
        shell_exec("chmod 640 {$path}/named.loopback");


        $named = view('server.samples.bind9.default-zones.named_localhost', [])
            ->render();

        if(!file_put_contents($path . '/named.localhost', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.localhost\'');
        }

        shell_exec("chown root:named {$path}/named.localhost");
        shell_exec("chmod 640 {$path}/named.localhost");


        $named = view('server.samples.bind9.default-zones.named_local', [])
            ->render();

        if(!file_put_contents($path . '/named.local', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.local\'');
        }

        shell_exec("chown named:named {$path}/named.local");
        shell_exec("chmod 644 {$path}/named.local");


        $named = view('server.samples.bind9.default-zones.named_ip6_local', [])
            ->render();

        if(!file_put_contents($path . '/named.ip6.local', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.ip6.local\'');
        }

        shell_exec("chown named:named {$path}/named.ip6.local");
        shell_exec("chmod 644 {$path}/named.ip6.local");


        $named = view('server.samples.bind9.default-zones.named_ca', [])
            ->render();

        if(!file_put_contents($path . '/named.ca', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.ca\'');
        }

        shell_exec("chown root:named {$path}/named.ca");
        shell_exec("chmod 640 {$path}/named.ca");


        $named = view('server.samples.bind9.default-zones.named_broadcast', [])
            ->render();

        if(!file_put_contents($path . '/named.broadcast', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/named.broadcast\'');
        }

        shell_exec("chown named:named {$path}/named.broadcast");
        shell_exec("chmod 644 {$path}/named.broadcast");


        $named = view('server.samples.bind9.default-zones.localhost_zone', [])
            ->render();

        if(!file_put_contents($path . '/localhost.zone', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/localhost.zone\'');
        }

        shell_exec("chown named:named {$path}/localhost.zone");
        shell_exec("chmod 644 {$path}/localhost.zone");

        $named = view('server.samples.bind9.default-zones.localdomain_zone', [])
            ->render();

        if(!file_put_contents($path . '/localdomain.zone', $named)) {
            throw new \Exception('Unable to write file \'' . $path . '/localdomain.zone\'');
        }

        shell_exec("chown named:named {$path}/localdomain.zone");
        shell_exec("chmod 644 {$path}/localdomain.zone");

        $this->info('The default bind9 zones are configured.');

        shell_exec('sudo systemctl restart named');
    }

}
