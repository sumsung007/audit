<?php


use Xxtime\Database\MySQL;

ini_set("date.timezone", "UTC");
ini_set('memory_limit', -1);
ini_set('max_execution_time', '0');
include __DIR__ . '/../vendor/autoload.php';

class AuditTools
{

    private $config;
    private $from;
    private $to;
    private $pdo;


    public function __construct()
    {
        $this->config = include_once 'config.php';
        $this->from = $this->config['from'];
        $this->to = $this->config['to'];
    }


    public function run()
    {
    }


    /**
     * 导出订单
     */
    private function getTrade()
    {
        foreach ($this->config['trade'] as $server_id => $server) {
            $fileName = "{$this->config['subject']}_tx_" . $server_id . '.csv';
            $sql = "SELECT CONCAT( server_id,'-',role_id ) user_id, amount, gold_real coin, pay_time time FROM order_log WHERE status='complete' AND pay_time>='{$this->from}' AND pay_time<='{$this->to}'";

            // SHELL
            $shell = "mysql -h{$server['host']} -P{$server['port']} -u{$server['user']} -p{$server['pass']} -e \"USE {$server['db']}; {$sql}\" >> /tmp/{$fileName}";
            exec($shell);
        }
    }


    /**
     * 导出消耗
     */
    private function getExp()
    {
        foreach ($this->config['servers'] as $server_id => $server) {
            $fileName = "{$this->config['subject']}_exp_" . intval($server_id / 1000) . '.csv';
            $sql = "SELECT CONCAT($server_id,'-',role_id) user_id, gold coin, reason type, log_time time FROM gold_log WHERE log_time>='{$this->from}' AND log_time<='{$this->to}'";

            // SHELL
            $shell = "mysql -h{$server['host']} -P{$server['port']} -u{$server['user']} -p{$server['pass']} -e \"USE {$server['db']}; {$sql}\" >> /tmp/{$fileName}";
            exec($shell);
        }
    }


    /**
     * 导出期末状态
     */
    private function getStatus()
    {
        foreach ($this->config['servers'] as $server_id => $server) {
            $fileName = "{$this->config['subject']}_status_" . intval($server_id / 1000) . '.csv';
            $sql = "SELECT CONCAT($server_id,'-',role_id) user_id, gold coin FROM role";

            // SHELL
            $shell = "mysql -h{$server['host']} -P{$server['port']} -u{$server['user']} -p{$server['pass']} -e \"USE {$server['db']}; {$sql}\" >> /tmp/{$fileName}";
            exec($shell);
        }
    }


    /**
     * @param $key
     * @return mixed
     */
    private function getPdo($key)
    {
        if (isset($this->pdo[$key])) {
            return $this->pdo[$key];
        }
        if (!isset($this->config['servers'][$key])) {
            $this->logger("no server: {$key}");
        }
        $config = [
            'host'     => $this->config['servers'][$key]['host'],
            'port'     => $this->config['servers'][$key]['port'],
            'database' => $this->config['servers'][$key]['db'],
            'username' => $this->config['servers'][$key]['user'],
            'password' => $this->config['servers'][$key]['pass'],
        ];
        $this->pdo[$key] = new MySQL($config);
        return $this->pdo[$key];

    }


    /**
     * 日志
     * @param string $msg
     */
    private function logger($msg = '')
    {
        print "\r\n" . $msg . "\r\n";
    }


}


$audit = new AuditTools();
$audit->run();