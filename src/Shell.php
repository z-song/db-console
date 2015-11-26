<?php

namespace Encore\Dbconsole;

use ErrorException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\Table;
use Encore\Dbconsole\Factory as Connection;
use Encore\Dbconsole\TabCompletion\AutoCompleter;
use Encore\Dbconsole\Connection\ConnectionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Db Console application.
 *
 * Usage:
 *
 *     $shell = new Shell();
 *     $shell->run();
 *
 * @author Zou Song <zosong@126.com>
 */
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

    /**
     * Initialize the shell.
     *
     * @param $config
     * @throws ErrorException
     */
    public function init($config)
    {
        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        $this->loadConfig($config);

        Factory::setConfig($this->config);

        $this->completion = new AutoCompleter($this);
        $this->completion->activate();

        $this->loop = new Loop();
    }

    /**
     * Load config with $config or from default config file.
     *
     * @param $config
     * @throws ErrorException
     */
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

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Exception When doRun returns Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, $output);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
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

    /**
     * Get current input from readline
     *
     * @throws \Exception
     */
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

    /**
     * Add query to buffer
     *
     * @param $query
     * @throws \Exception
     */
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

    /**
     * Get current query.
     *
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Check whether the query in this shell's query buffer is valid.
     *
     * @return bool
     */
    public function hasValidQuery()
    {
        return !$this->queryBufferOpen && $this->query !== false;
    }

    /**
     * Reset current query buffer.
     */
    public function resetQueryBuffer()
    {
        $this->query = false;
        $this->queryBuffer = [];
    }

    /**
     * Render results.
     *
     * @param $result
     */
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

        $this->string($this->connection->appendResult());
    }

    /**
     * Render table.
     *
     * @param array $headers
     * @param $rows
     * @param string $style
     */
    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Render sting.
     *
     * @param $string
     */
    public function string($string)
    {
        $this->output->writeln($string);
    }

    /**
     * Render list.
     *
     * @param $list
     */
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
     * Set the shell outputer
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

    /**
     * Set the database connection, if $connection is a string,
     *
     * then create a connection use $connection as connection name.
     *
     * @param string $connection
     * @throws \Exception
     */
    public function setConnection($connection = 'default')
    {
        if($connection instanceof ConnectionInterface) {
            $this->connection = $connection;
        }

        if(is_string($connection)) {
            $this->connection = Factory::create($connection);
        }
    }

    /**
     * Get the database connection.
     *
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Handle all errors.
     *
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line, null);
        }
    }

    /**
     * Handle all exceptions.
     *
     * @param $e
     */
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