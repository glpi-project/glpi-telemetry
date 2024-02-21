<?php

namespace Deployer;

use Deployer\Component\ProcessRunner\Printer;
use Deployer\Component\Ssh\Client;
use Deployer\Exception\RunException;
use Deployer\Exception\TimeoutException;
use Deployer\Host\Host;
use Deployer\Logger\Logger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

require 'recipe/symfony.php';
require 'contrib/npm.php';
require 'contrib/webpack_encore.php';

Deployer::get()->sshClient = function ($c) {
    return new class($c['output'], $c['pop'], $c['logger']) extends Client {
        private $output;
        private $pop;
        private $logger;
        public function __construct(OutputInterface $output, Printer $pop, Logger $logger)
        {
            $this->output = $output;
            $this->pop = $pop;
            $this->logger = $logger;
        }
        public function run(Host $host, string $command, array $config = []): string
        {
            $defaults = [
                'timeout' => $host->get('default_timeout', 300),
                'idle_timeout' => null,
                'real_time_output' => false,
                'no_throw' => false,
            ];
            $config = array_merge($defaults, $config);

            $shellId = bin2hex(random_bytes(10));
            $shellCommand = $host->getShell();
            if ($host->has('become')) {
                $shellCommand = "sudo -H -u {$host->get('become')} " . $shellCommand;
            }

            // $ssh = array_merge(['ssh'], $host->connectionOptionsArray(), [$host->connectionString(), ": $shellId; $shellCommand"]);
            $ssh = array_merge(['ssh'], $host->connectionOptionsArray());
            $ssh[] = $host->connectionString();
            if ($host->has('ssh_remote_command')) {
                $ssh[] = '--';
                $ssh[] = '--quiet';
                $ssh[] = $host->get('ssh_remote_command');
            }
            $ssh[] = '--';
            $ssh[] =  ": $shellId; $shellCommand";

            // -vvv for ssh command
            if ($this->output->isDebug()) {
                $sshString = $ssh[0];
                for ($i = 1; $i < count($ssh); $i++) {
                    $sshString .= ' ' . escapeshellarg((string)$ssh[$i]);
                }
                $this->output->writeln("[$host] $sshString");
            }

            $this->pop->command($host, 'run', $command);
            $this->logger->log("[{$host->getAlias()}] run $command");

            $command = str_replace('%secret%', strval($config['secret'] ?? ''), $command);
            $command = str_replace('%sudo_pass%', strval($config['sudo_pass'] ?? ''), $command);

            $process = new Process($ssh);
            $process
                ->setInput("( $command ); printf '[exit_code:%s]' $?;")
                ->setTimeout((null === $config['timeout']) ? null : (float) $config['timeout'])
                ->setIdleTimeout((null === $config['idle_timeout']) ? null : (float) $config['idle_timeout']);

            $callback = function ($type, $buffer) use ($config, $host) {
                $this->logger->printBuffer($host, $type, $buffer);
                $this->pop->callback($host, boolval($config['real_time_output']))($type, $buffer);
            };

            try {
                $process->run($callback);
            } catch (ProcessTimedOutException $exception) {
                // Let's try to kill all processes started by this command.
                $pid = $this->run($host, "ps x | grep $shellId | grep -v grep | awk '{print \$1}'");
                // Minus before pid means all processes in this group.
                $this->run($host, "kill -9 -$pid");
                throw new TimeoutException(
                    $command,
                    $exception->getExceededTimeout()
                );
            }

            $output = $this->pop->filterOutput($process->getOutput());
            $exitCode = $this->parseExitStatus($process);

            if ($exitCode !== 0 && !$config['no_throw']) {
                throw new RunException(
                    $host,
                    $command,
                    $exitCode,
                    $output,
                    $process->getErrorOutput()
                );
            }

            return $output;
        }
        private function parseExitStatus(Process $process): int
        {
            preg_match('/\[exit_code:(\d*)]/', $process->getOutput(), $match);
            return (int)($match[1] ?? -1);
        }
    };
};

set('allow_anonymous_stats', false);

set('application', 'telemetry.glpi-project.org');
set('repository', 'git@github.com:glpi-project/glpi-telemetry.git');

set('deploy_path', '/var/www/{{application}}');
set('http_user', 'www-data');

set('remote_user', 'deployer-telemetry'); // user used to connect through the bastion
set('become', 'deployer'); // user used on the server itself
set('ssh_multiplexing', false);

// Hosts
host('telemetry-dev.glpi-project.org')
    ->set('hostname', 'bastion.teclib.com')
    ->set('labels', ['stage' => 'development'])
    ->set('ssh_remote_command', 'telemetry-dev.glpi-project.org');

// Install and build CSS/JS dependencies
after('deploy:vendors', 'npm:install');
after('npm:install', 'webpack_encore:build');

// Run migrations
before('deploy:publish', 'database:migrate');

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
