<?php

namespace Encore\Dbconsole;

use ErrorException;
use Encore\Dbconsole\Factory as Connection;
use Encore\Dbconsole\Connection\ConnectionInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Shell extends Application
{
    const VERSION       = 'v0.0.1';
    const PROMPT_SUFFIX = '> ';
    const BUFF_PROMPT   = '... ';

    private $output;
    private $connection;
    private $loop;
    private $query;
    private $queryBuffer;
    private $queryBufferOpen;

    private $config;

    /**
     * @param array|string $config Config or config path
     */
    public function __construct($config = null)
    {
        parent::__construct('Db Console', self::VERSION);

        $this->init($config);
    }

    public function init($config)
    {
        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        $this->loadConfig($config);

        Factory::setConfig($this->config);

        $this->loop = new Loop();
    }

    public function loadConfig($config)
    {
        if(is_array($config)) {
            $this->config = $config;
        } elseif(is_string($config) && file_exists($config)) {
            $this->config = require $config;
        } else {
            throw new ErrorException('config invalid');
        }
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, $output);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);
        $this->setUpConnection();
        $this->resetQueryBuffer();
        $this->output->writeln($this->getHeader());

        try {
            $this->loop->run($this);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    public function getInput()
    {
        $this->queryBufferOpen = false;

        do{
            $query = readline($this->getPrompt());

            if(! ($query = trim($query))) {
                continue;
            }

            $this->addQuery($query);

        } while(!$this->hasValidQuery());
    }

    public function addQuery($query)
    {
        try {
            if($this->connection->queryClosed($query)) {
                $this->queryBufferOpen = false;
            } else {
                $this->queryBufferOpen = true;
            }

            $this->queryBuffer[] = $query;
            $this->query         = join(' ', $this->queryBuffer);
        } catch (\Exception $e) {
            $this->addHistory(join("\n", $this->queryBuffer));
            throw $e;
        }
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function hasValidQuery()
    {
        return !$this->queryBufferOpen && $this->query !== false;
    }

    public function resetQueryBuffer()
    {
        $this->query = false;
        $this->queryBuffer = [];
    }

    public function renderResult($result)
    {
        $this->resetQueryBuffer();

        if(empty($result)) return;

        if(is_string($result) || is_numeric($result)) {
            $this->string($result);
        } elseif (is_array($result) || is_object($result)) {

            $output = json_encode($result);
            $output = json_decode($output, true);

            if ( ! is_array(current($output))) {
                $this->lists($output);
            } else {
                $this->table(array_keys(current($output)), $output);
            }
        }
    }

    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    public function string($string)
    {
        $this->output->writeln($string);
    }
    
    public function lists($list)
    {
        $output = '';

        foreach($list as $key => $val) {
            $output .= "$key) \"$val\"\r\n";
        }

        $this->output->writeln(trim($output));
    }

    /**
     * Add $query to readline history
     *
     * @param $query
     */
    public function addHistory($query)
    {
        if(empty($query)) return;

        readline_add_history($query);
    }

    /**
     * Get current DB Console version
     *
     * @return string
     */
    public function getVersion()
    {
        return sprintf('DB Console %s (PHP %s — %s)', self::VERSION, phpversion(), php_sapi_name());
    }

    /**
     * Get the shell output header.
     *
     * @return string
     */
    protected function getHeader()
    {
        return sprintf('%s by Zou Song', $this->getVersion());
    }

    /**
     * Get current prompt
     *
     * @return string
     */
    public function getPrompt()
    {
        return $this->queryBufferOpen ? static::BUFF_PROMPT :
            $this->connection->getPrompt() . static::PROMPT_SUFFIX;
    }

    /**
     * Set outputer
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Setup the database connection
     */
    private function setUpConnection()
    {
        if($this->connection instanceof ConnectionInterface) {
            return;
        }

        $this->connection = Connection::create();
    }

    public function setConnection($connection = 'default')
    {
        if($connection instanceof ConnectionInterface) {
            $this->connection = $connection;
        }

        if(is_string($connection)) {
            $this->connection = Factory::create($connection);
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line, null);
        }
    }

    public function handleException($e)
    {
        $this->renderException($e, $this->output ?: new ConsoleOutput());

        $this->resetQueryBuffer();
    }

    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(new ErrorException(
                $error['message'], $error['type'], 0, $error['file'], $error['line'], null
            ));
        }
    }

    /**
     * Check if is a fatal error type
     *
     * @param $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}