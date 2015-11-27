<?php

namespace Encore\Dbconsole\Commands;

use Encore\Dbconsole\Shell;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class DbConsoleCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:console';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open a database console.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $shell = new Shell($this->laravel['config']['database']);

        if ($connection = $this->option('connection')) {
            $shell->setConnection($connection);
        }

        $shell->run();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['connection', 'c', InputOption::VALUE_OPTIONAL, 'Specify a connection in laravel database config.'],
        ];
    }
}