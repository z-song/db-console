<?php

namespace Encore\Dbconsole;

class Loop {

    private $shell;

    private $savegame;
    
    public function run(Shell $shell)
    {
        $this->shell = $shell;

        list($up, $down) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        if (!$up) {
            throw new \RuntimeException('Unable to create socket pair.');
        }

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException('Unable to start execution loop.');
        } elseif ($pid > 0) {
            // This is the main thread. We'll just wait for a while.

            // We won't be needing this one.
            fclose($up);

            // Wait for a return value from the loop process.
            $read   = array($down);
            $write  = null;
            $except = null;
            if (stream_select($read, $write, $except, null) === false) {
                throw new \RuntimeException('Error waiting for execution loop.');
            }

            $content = stream_get_contents($down);
            fclose($down);

            if ($content) {
            //    $shell->setScopeVariables(@unserialize($content));
            }

            return;
        }

        // This is the child process. It's going to do all the work.
        if (function_exists('setproctitle')) {
            setproctitle('dbconsole (loop)');
        }

        // We won't be needing this one.
        fclose($down);

        // Let's do some processing.
        $this->doLoop();

        // Send the scope variables back up to the main thread
        //fwrite($up, $this->serializeReturn($shell->getScopeVariables()));
        fclose($up);

        exit;
    }

    public function doLoop()
    {
        do{
            $this->beforeLoop();

            $this->shell->getInput();

            try {

                $result = $this->shell->getConnection()->query($this->shell->getQuery());

            } catch(\Exception $e) {
                $this->shell->handleException($e);

                continue;
            }

            $this->shell->addHistory($this->shell->getQuery());
            $this->shell->renderResult($result);

            $this->beforeLoop();

        } while (true);
    }

    public function beforeLoop()
    {
        $this->createSavegame();
    }

    /**
     * Clean up old savegames at the end of each loop iteration.
     */
    public function afterLoop()
    {
        // if there's an old savegame hanging around, let's kill it.
        if (isset($this->savegame)) {
            posix_kill($this->savegame, SIGKILL);
            pcntl_signal_dispatch();
        }
    }

    /**
     * Create a savegame fork.
     *
     * The savegame contains the current execution state, and can be resumed in
     * the event that the worker dies unexpectedly (for example, by encountering
     * a PHP fatal error).
     */
    private function createSavegame()
    {
        // the current process will become the savegame
        $this->savegame = posix_getpid();

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException('Unable to create savegame fork.');
        } elseif ($pid > 0) {
            // we're the savegame now... let's wait and see what happens
            pcntl_waitpid($pid, $status);

            // worker exited cleanly, let's bail
            if (!pcntl_wexitstatus($status)) {
                posix_kill(posix_getpid(), SIGKILL);
            }

            // worker didn't exit cleanly, we'll need to have another go
            $this->createSavegame();
        }
    }
}