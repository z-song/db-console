<?php

namespace Dbconsole;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Database\QueryException;

class Shell extends Application
{
    const VERSION       = 'v0.0.1';
    const PROMPT        = 'mysql> ';

    private $output;

    private $connection;

    private $history = [];

    public function __construct()
    {
        parent::__construct('Db Console', self::VERSION);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, $output);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);

        $this->output->writeln($this->getHeader());

        try {
            $this->setConnection(new Connection());

            $this->loop();

        } catch (\Exception $e) {
            echo $e->getMessage();

        }
    }

    protected function loop()
    {
        do{
            $query = readline(static::PROMPT);

            if(! ($query = trim($query))) {
                continue;
            }

            try {
                $result = $this->connection->query($query);
            } catch(\Exception $e) {
                $this->writeException($e);

                continue;
            }

            $this->addHistory($query);

            $this->writeResult($result);

        } while (true);
    }

    public function writeResult($result)
    {
        //$output = json_encode($result);

        $this->output->writeln((new ResultFormatter($result))->output());
    }

    public function writeException(\Exception $e)
    {
        try{
            throw $e;
        } catch (QueryException $e) {
            $this->output->writeln("\033[01;31m <ERR> {$e->getMessage()} \033[0m");
        }
    }

    public function addHistory($query)
    {
        if(empty($query)) return;

        readline_add_history($query);

        $this->history[] = $query;
    }

    public function getVersion()
    {
        return sprintf('DB Console %s (PHP %s — %s)', self::VERSION, phpversion(), php_sapi_name());
    }

    protected function getHeader()
    {
        return sprintf('%s by Zou Song', $this->getVersion());
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }
}