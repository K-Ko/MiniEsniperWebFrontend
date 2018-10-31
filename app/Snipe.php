<?php
/**
 *
 *
 * @author     Knut Kohl <github@knutkohl.de>
 * @copyright  (c) 2016 Knut Kohl
 * @licence    MIT License - http://opensource.org/licenses/MIT
 */
namespace App;

/**
 * Hold all data for a specific snipe
 */
class Snipe
{
    /**
     * Auction group name
     *
     * @var string
     */
    public $name;

    /**
     * Auction group data
     *
     * @var string
     */
    public $data;

    /**
     * End time(s)
     *
     * @var string
     */
    public $end;

    /**
     * esniper log
     *
     * @var string
     */
    public $log;

    /**
     * Action was won
     *
     * @var boolean
     */
    public $won;

    /**
     * esniper pid for auction group
     *
     * @var int
     */
    public $pid;

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $data
     * @return void
     */
    public function __construct($name = '', $data = '')
    {
        $this->reset();

        $this->name = $name;
        $this->data = $data;

        $this->config = Config::getInstance();
    }

    /**
     * Load auction group data from file
     *
     * DEPRECATED, will be removed in 2.0.0
     *
     * @param string $file
     * @return instance $this
     */
    public function loadTxt($file)
    {
        $data = file($file, FILE_IGNORE_NEW_LINES);

        unlink($file);

        // Extract auction group name from 1st line
        $this->name = trim(array_shift($data), '# ');
        $this->data = implode(PHP_EOL, $data);

        // Rewrite to INI file
        $this->save();

        $this->log = $this->getLog();
        $this->pid = $this->getPid();

        Hook::apply('snipe.loaded', $this);

        return $this;
    }

    /**
     * Load auction group data from file
     *
     * @param string $file
     * @return instance $this
     */
    public function loadIni($file)
    {
        $file = realpath($file);

        $ini = parse_ini_file($file);

        $this->name = $ini['name'];
        $this->data = $ini['data'];

        $this->log = $this->getLog();
        $this->pid = $this->getPid();

        Hook::apply('snipe.loaded', $this);

        return $this;
    }

    /**
     * Save auction group data to file
     *
     * @return integer Size of written file on success
     */
    public function save()
    {
        if ($this->name != '' && $this->data != '') {
            return file_put_contents(
                $this->getFileName('ini'),
                sprintf(
                    'name = "%2$s"%1$s%1$sdata = "%3$s%1$s"%1$s',
                    PHP_EOL,
                    str_replace('"', '\\"', $this->name),
                    trim($this->data)
                )
            );
        }

        return 0;
    }

    /**
     * Start esniper for auction group
     *
     * @return integer PID
     */
    public function start()
    {
        if ($this->name == '' || $this->data == '') {
            return;
        }

        $this->save();

        // Put ebay credentials and snipes data into temp. file,
        $token    = $this->getHash();
        $dataFile = tempnam(sys_get_temp_dir(), 'mef.') . $token;
        $confFile = $dataFile . '.conf';

        // 1st config file, user and password
        file_put_contents(
            $confFile,
            'username = ' . User::$name . PHP_EOL .
            'password = ' . User::$password
        );

        // 2nd data file, auctions and bids
        $data = trim(str_replace(',', '.', $this->data)); // US numeric

        if (!preg_match('~^ *seconds *=~m', $data)) {
            $data = 'seconds = ' . $this->config->seconds . PHP_EOL . $data;
        }

        file_put_contents($dataFile, $data);

        // Put all together ...
        $cmd = sprintf(
            "cd %s && \\\nnohup %s -b -c %s %s \\\n>>%s 2>&1 & \\\necho -n $! >%s",
            User::$dir,
            $this->config->esniper,
            $confFile,
            $dataFile,
            $this->getFileName('log'),
            $this->getFileName('pid')
        );

        // Init log
        $sep = PHP_EOL . str_repeat('-', 70) . PHP_EOL;

        file_put_contents(
            $this->getFileName('log'),
            $this->config->debug ? $cmd . $sep . $data . $sep . PHP_EOL : ''
        );

        // Let's snipe
        set_time_limit(0);
        $wait = 90;

        $startTime = microtime(true);

        exec($cmd);

        // Wait a bit until esniper comes up with pid file and log
        sleep(2);

        do {
            $wait--; // Wait max. 90 sec.
            sleep(1);
            // Check fpr PID, exit if not more running in case of error
            $this->pid = $this->getPid();
            $this->log = $this->getLog();
        } while ($wait > 0 && $this->pid &&
                 strpos($this->log, 'Sleeping for') === false &&
                 strpos($this->log, 'Error:') === false);

        // Remove temp. files!
        unlink($confFile);
        unlink($dataFile);

        if (!$this->pid || strpos($this->log, 'Error') !== false) {
            $_SESSION['message'] = [
                'class' => 'danger',
                'text'  => I18N::_('start_failed')
            ];
        } else {
            $_SESSION['message'] = [
                'class' => 'success',
                'text'  => I18N::_('started', microtime(true) - $startTime)
            ];
        }

        if ($wait > 0) {
            // Wait a bit more to make sure log file is really complete
            sleep(2);
            $this->pid = $this->getPid();
            $this->log = $this->getLog();
        }

        return $this->pid;
    }

    /**
     * Stop esniper for auction group
     *
     * @param boolean $silent Don't update log file (before delete)
     * @return string
     */
    public function stop($silent = false)
    {
        if (!$this->pid) {
            return;
        }

        exec('kill ' . $this->pid);

        if (!$silent) {
            // Append marker for manual stop
            file_put_contents(
                $this->getFileName('log'),
                $this->getLog() . PHP_EOL .
                '::: Manually stopped ' . date(DATE_RFC2822) . ' :::'
            );

            @unlink($this->getFileName('pid'));

            // Just reload
            $this->loadIni($this->getFileName('ini'));

            return $this->log;
        }
    }

    /**
     * Restart esniper for auction group
     *
     * @return integer PID
     */
    public function restart()
    {
        $this->stop(true);
        return $this->start();
    }

    /**
     * Delete auction group files, stop before if needed
     *
     * @return boolean
     */
    public function delete()
    {
        $this->stop(true);
        @unlink($this->getFileName('ini'));
        @unlink($this->getFileName('log'));
        @unlink($this->getFileName('pid'));
        $this->reset();
        return true;
    }

    public function getHash()
    {
        return substr(md5($this->name), -12);
    }

    // -----------------------------------------------------------------------
    // PROTECTED
    // -----------------------------------------------------------------------

    /**
     * Application configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * Init variables
     *
     * @return void
     */
    protected function reset()
    {
        $this->name  = '';
        $this->data  = '';
        $this->end   = '';
        $this->log   = '';
        $this->won   = false;
        $this->pid   = 0;
    }

    /**
     * Build log file name
     *
     * @return string
     */
    protected function getLog()
    {
        $file = $this->getFileName('log');

        if (is_file($file)) {
            $log = file_get_contents($file);

            $enc = mb_detect_encoding($log, ['UTF-8', 'ISO-8859-1']);

            if ($enc != 'UTF-8') {
                $log = iconv($enc ?: 'ISO-8859-1', 'UTF-8', $log);
            }

            // Auction won?
            if (preg_match('~won \d+ item~', $log) &&
                preg_match_all('~Currently: ([\d.]+).*?([\d.]+)~', $log, $args)) {
                // Get last and your max. price from list
                $last = array_slice($args[1], -1)[0];
                $your = $args[2][0];

                if ($your >= $last) {
                    $this->won = sprintf('%.2f (%.2f)', $last, $your);
                    $log .= PHP_EOL . sprintf('You paid %d%% of your maximum bid.', $last/$your*100);
                }
            }

            // Get auction end time
            if (preg_match_all('~End time: *(.*)$~m', $log, $args)) {
                // Get last end time from log
                $ts = strptime(array_pop($args[1]), '%d/%m/%Y %H:%M:%S');

                // Make timestamp for reformating
                $ts = mktime(
                    $ts['tm_hour'],
                    $ts['tm_min'],
                    $ts['tm_sec'],
                    $ts['tm_mon'] + 1,
                    $ts['tm_mday'],
                    $ts['tm_year'] + 1900
                );

                // Not ended auction?
                if ($ts > time()) {
                    $this->end = date(I18N::_('timestamp_format'), $ts);
                }
            }

            return $log;
        }
    }

    /**
     * Get auction group PID
     *
     * @return integer
     */
    protected function getPid()
    {
        $pidFile = $this->getFileName('pid');

        if (is_file($pidFile)) {
            $pid = file_get_contents($pidFile);
            if (!exec('ps -o pid= --pid ' . $pid)) {
                $pid = 0;
                unlink($pidFile);
            }
        } else {
            $pid = (int) exec('ps -fea | grep -v grep | grep '.$this->getHash().' | awk \'{print $2}\'');
            if ($pid) {
                file_put_contents($pidFile, $pid);
            }
        }

        return $pid;
    }

    /**
     * Build generic file name
     *
     * @param string $ext File extension
     * @return string
     */
    protected function getFileName($ext)
    {
        return sprintf('%s/%s.%s', User::$dir, $this->getHash(), $ext);
    }
}
