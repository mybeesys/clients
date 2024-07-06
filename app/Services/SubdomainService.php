<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Exception;

class SubdomainService
{
    protected $hosts_file_path;
    protected $vhosts_directory;
    protected $apacheRestartCommand;
    protected $subdomain_env;

    public function __construct()
    {
        //if in production mode
        if (config('app.env') === 'production') {
            $this->hosts_file_path = '/etc/hosts';
            $this->vhosts_directory = '/etc/apache2/sites-available';
            $this->apacheRestartCommand = 'sudo systemctl restart apache2';
            $this->subdomain_env = 'production';
        } elseif (config('app.env') === 'local') {
            $this->hosts_file_path = 'C:\Windows\System32\drivers\etc\hosts';
            $this->vhosts_directory = 'C:\xampp3\apache\conf\extra\httpd-vhosts.conf';
            $this->apacheRestartCommand = 'C:\xampp\apache_restart.bat';
            $this->subdomain_env = 'local';
        }
    }

    public function create_subdomain($subdomain)
    {
        try {
            $this->update_vhost_file($subdomain);
            $this->update_hosts_file($subdomain);
            $this->restart_apache();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return ['message' => "Subdomain $subdomain.erp.local created successfully."];
    }

    protected function update_hosts_file($subdomain)
    {
        // $hostsEntry = "\n127.0.0.1   $subdomain.erp.local";


        // if (file_exists($this->hosts_file_path)) {
        //     $tempFilePath = tempnam(sys_get_temp_dir(), 'hosts');
        //     file_put_contents($tempFilePath, $hostsEntry, FILE_APPEND);

        //     if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        //         // Windows command
        //         $command = "type \"$tempFilePath\" >> \"$this->hosts_file_path\"";
        //     } else {
        //         // Unix/Linux command
        //         $command = "cat \"$tempFilePath\" | sudo tee -a \"$this->hosts_file_path\"";
        //     }

        //     $this->run_as_admin($command);
        //     unlink($tempFilePath);
        // } else {
        //     throw new Exception("Hosts file not found.");
        // }
        $vhost_file_path = $this->hosts_file_path;
        $vhost_entry = "\n127.0.0.1   $subdomain.erp.local";

        if (File::exists($this->vhosts_directory)) {
            File::append($vhost_file_path, $vhost_entry);
        } else {
            throw new Exception("Virtual hosts directory not found.");
        }
    }

    protected function run_as_admin($command)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = 'cmd /c "' . $command . '"';

            $output = shell_exec("start /B /wait $command");
            file_put_contents(storage_path('logs/command_output.log'), $output);
        } else {
            $command = 'sudo ' . $command;
            $output = shell_exec($command);
            file_put_contents(storage_path('logs/command_output.log'), $output);
        }
    }



    protected function update_vhost_file($subdomain)
    {
        $vhost_file_path = $this->getvhost_file_path($subdomain);
        $vhost_entry = "
<VirtualHost *:80>
    DocumentRoot \"/var/www/html/ERP/ERP/public\"
    ServerName $subdomain.erp.local
    <Directory \"/var/www/html/ERP/ERP/public\">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/$subdomain-error.log
    CustomLog \${APACHE_LOG_DIR}/$subdomain-access.log combined
</VirtualHost>";

        if (File::exists($this->vhosts_directory)) {
            File::append($vhost_file_path, $vhost_entry);
        } else {
            throw new Exception("Virtual hosts directory not found.");
        }
    }

    protected function restart_apache()
    {
        exec($this->apacheRestartCommand);
    }

    protected function gethosts_file_path()
    {
        return $this->hosts_file_path;
    }

    protected function getvhost_file_path($subdomain)
    {
        return $this->subdomain_env === 'local' ? "$this->vhosts_directory" : "$this->vhosts_directory/$subdomain.conf";
    }
}
