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
    private $option;
    private $_pdo;
    private $_RUN_TIME_START;
    private $_RUN_TIME_END;


    public function __construct()
    {
        $this->_RUN_TIME_START = time();
        $this->config = include_once 'config.php';
        $this->from = $this->config['from'];
        $this->to = $this->config['to'];
        $this->setOptions();
    }


    public function __destruct()
    {
    }


    public function run()
    {
        $this->logger('START: Program');

        // 执行命令
        if (isset($this->option['method'])) {
            $method = $this->option['method'];
            if (method_exists($this, $method)) {
                $this->$method();
            } else {
                exit("\r\n" . 'ERROR: no method [' . $method . "]\r\n\r\n");
            }
        }


        $this->_RUN_TIME_END = time();
        $this->logger('---------------');
        $this->logger('占用内存: ' . round(memory_get_usage() / 1024 / 1024, 2) . 'M');
        $this->logger('执行时间: ' . round(($this->_RUN_TIME_END - $this->_RUN_TIME_START) / 60, 2) . '分钟');
        $this->logger('-----------------------------------------');
    }


    /**
     * 导出订单
     */
    private function outTrade()
    {
        $this->logger('START: outTrade');
        foreach ($this->config['trade'] as $category => $server) {
            $fileName = "{$this->config['subject']}_{$category}_trade.csv";
            $sql = "SELECT CONCAT( server_id,'-',role_id ) user_id, amount, gold_real coin, pay_time time FROM order_log WHERE status='complete' AND pay_time>='{$this->from}' AND pay_time<='{$this->to}'";

            // SHELL
            $shell = "mysql -h{$server['host']} -P{$server['port']} -u{$server['user']} -p{$server['pass']} -e \"USE {$server['db']}; {$sql}\" >> /tmp/{$fileName}";
            $this->executeShell($shell);
        }
    }


    /**
     * 导出消耗
     */
    private function outExp()
    {
        $this->logger('START: outExp');
        foreach ($this->config['servers'] as $category => $list) {
            foreach ($list as $server_id => $server) {
                $fileName = "{$this->config['subject']}_{$category}_exp.csv";
                $sql = "SELECT CONCAT($server_id,'-',role_id) user_id, gold coin, reason type, log_time time FROM gold_log WHERE log_time>='{$this->from}' AND log_time<='{$this->to}'";

                // SHELL
                $shell = "mysql -h{$server['host']} -P{$server['port']} -u{$server['user']} -p{$server['pass']} -e \"USE {$server['db']}; {$sql}\" >> /tmp/{$fileName}";
                $this->executeShell($shell);
            }
        }
    }


    /**
     * 导出期末状态
     */
    private function outStatus()
    {
        $this->logger('START: outStatus');
        foreach ($this->config['servers'] as $category => $list) {
            foreach ($list as $server_id => $server) {
                $fileName = "{$this->config['subject']}_{$category}_status.csv";
                $sql = "SELECT CONCAT($server_id,'-',role_id) user_id, gold coin FROM role";

                // SHELL
                $shell = "mysql -h{$server['host']} -P{$server['port']} -u{$server['user']} -p{$server['pass']} -e \"USE {$server['db']}; {$sql}\" >> /tmp/{$fileName}";
                $this->executeShell($shell);
            }
        }

    }


    /**
     * 导入 TODO::订单无coin字段 暂用gateway字段代替
     */
    private function inCSV()
    {
        $this->logger('START: inTrade');
        $sql = "mysql -h{$this->config['audit']['host']} -u{$this->config['audit']['user']} -p{$this->config['audit']['pass']} --local-infile=1";
        dump($sql);
        foreach ($this->config['trade'] as $category => $nothing) {
            $file_tr = "{$this->config['subject']}_{$category}_trade.csv";
            $file_st = "{$this->config['subject']}_{$category}_status.csv";
            $file_ex = "{$this->config['subject']}_{$category}_exp.csv";
            $table_tr = "{$this->config['subject']}_trade";
            $table_st = "{$this->config['subject']}_status";
            $table_ex = "{$this->config['subject']}_exp";
            $sql = <<<END
LOAD DATA LOCAL INFILE '/tmp/$file_tr' INTO TABLE $table_tr CHARACTER SET utf8mb4 FIELDS TERMINATED BY '\t' ENCLOSED BY '"' (@c1,@c2,@c3,@c4) SET user_id=@c1, amount=@c2, gateway=@c3, time=@c4;
LOAD DATA LOCAL INFILE '/tmp/$file_st' INTO TABLE $table_st CHARACTER SET utf8mb4 FIELDS TERMINATED BY '\t' ENCLOSED BY '"' (@c1,@c2) SET user_id=@c1, coin=@c2;
LOAD DATA LOCAL INFILE '/tmp/$file_ex' INTO TABLE $table_ex CHARACTER SET utf8mb4 FIELDS TERMINATED BY '\t' ENCLOSED BY '"' (@c1,@c2,@c3,@c4) SET user_id=@c1, coin=@c2, type=@c3, time=@c4;
END;
            dump($sql);
        }
        $sql = <<<END
DELETE FROM `{$this->config['subject']}_trade` WHERE user_id='user_id';
DELETE FROM `{$this->config['subject']}_status` WHERE user_id='user_id';
DELETE FROM `{$this->config['subject']}_exp` WHERE user_id='user_id';
END;
        dump($sql);
    }


    private function fixTrade()
    {
        $this->logger('START: fixTrade');
    }


    /**
     * 删除消耗中的订单记录,然后导入订单到消耗表
     */
    private function inTrade()
    {
        $this->logger('START: inTrade');
        $sh = "mysql -h{$this->config['audit']['host']} -P{$this->config['audit']['port']} -u{$this->config['audit']['user']} -p{$this->config['audit']['pass']} ";

        // 清理
        $sql = "DELETE FROM {$this->config['subject']}_exp WHERE type='1'";
        $shell = $sh . "-e \"USE {$this->config['audit']['db']}; {$sql}\"";
        $this->executeShell($shell);

        // 插入
        $sql = "INSERT INTO {$this->config['subject']}_exp(user_id,coin,type,time) SELECT user_id,gateway,1,time FROM {$this->config['subject']}_trade";
        $shell = $sh . "-e \"USE {$this->config['audit']['db']}; {$sql}\"";
        $this->executeShell($shell);
    }


    private function balanceExp()
    {
        $this->logger('START: balanceExp');
        $sh = "mysql -h{$this->config['audit']['host']} -P{$this->config['audit']['port']} -u{$this->config['audit']['user']} -p{$this->config['audit']['pass']} ";


        // 准备参数
        $subject = $this->config['subject'];
        $q = substr($subject, -1);
        if ($q == 1) {
            exit('do something here');
        }
        $table_start = substr($subject, 0, -1) . strval($q - 1) . '_status'; //期初表
        $table_end = $subject . '_status';  // 期末表
        $table_exp = $subject . '_exp';     // 消耗表


        // 期初的用户必须在期末中出现，(仅在有合服时导致有期初无期末,更改用户ID导致)
        $sql = "INSERT INTO {$table_end}(user_id, coin) SELECT user_id, coin FROM {$table_start} WHERE user_id NOT IN(SELECT user_id FROM {$table_end})";
        $shell = $sh . "-e \"USE {$this->config['audit']['db']}; {$sql}\"";
        $this->executeShell($shell);


        // TODO :: 清掉exp记录, trade记录 (不在期末, 但在exp表中的记录)  | (outExp,outStatus导出时严格限制充值用户 则不需要此步骤) 也可增加期末状态
        $sql = "SELECT id FROM $table_exp WHERE user_id NOT IN(SELECT user_id FROM $table_end)";
        $pdo = $this->pdo('audit');
        $ids = $pdo->fetchAll($sql);
        if ($ids) {
            $ids = array_column($ids, 'id');
            $ids = implode(',', $ids);
            $sql = "DELETE FROM $table_exp WHERE id IN ($ids)";
            $pdo->execute($sql);
        }


        // 字典-期初
        $sql = "SELECT user_id, coin FROM $table_start";
        $tmp = $pdo->fetchAll($sql);
        $dict_start = array_column($tmp, 'coin', 'user_id');


        // 字典-消耗
        $sql = "SELECT user_id, SUM(coin) coin FROM $table_exp GROUP BY user_id";
        $tmp = $pdo->fetchAll($sql);
        $dict_exp = array_column($tmp, 'coin', 'user_id');


        // 循环期末状态
        $sql_insert = "INSERT INTO {$table_exp}(user_id, coin, type, time) VALUES";
        $execute = false;

        $sql = "SELECT user_id, coin FROM $table_end";
        $tmp = $pdo->fetchAll($sql);
        $dict_end = array_column($tmp, 'coin', 'user_id');
        foreach ($dict_end as $user_id => $coin_end) {
            // 无期初
            if (!isset($dict_start[$user_id])) {
                $dict_start[$user_id] = 0;
            }
            // 无消耗
            if (!isset($dict_exp[$user_id])) {
                $dict_exp[$user_id] = 0;
            }
            $diff = $coin_end - $dict_start[$user_id] - $dict_exp[$user_id];
            if ($diff == 0) {
                continue;
            }

            $this->logger("balanceExp: {$user_id}");
            $execute = true;
            $year = date('Y');
            $month = date('m', strtotime('-1 month'));
            $day = rand(1, 28);
            $hour = rand(0, 22); // 保留移动空间
            $min = rand(0, 59);
            $second = rand(0, 59);

            $time = mktime($hour, $min, $second, $month, $day, $year);
            $date = date('Y-m-d H:i:s', $time);

            $sql_insert .= "('$user_id',$diff,909,'$date'),";
        }
        if ($execute) {
            $sql_insert = substr($sql_insert, 0, -1);
            $pdo->execute($sql_insert);
            $this->logger('balanceExp ok');
        } else {
            $this->logger('no need balanceExp');
        }

    }


    private function moveExp()
    {
        $this->logger('START: moveExp');
    }


    /**
     * @param string $shell
     */
    private function executeShell($shell = '')
    {
        exec($shell);
    }


    /**
     * @param $key
     * @return mixed
     */
    private function pdo($key)
    {
        if (isset($this->_pdo[$key])) {
            return $this->_pdo[$key];
        }

        $cfg = null;
        if (isset($this->config[$key])) {
            $cfg = $this->config[$key];
        } elseif (isset($this->config['trade'][$key])) {
            $cfg = $this->config['trade'][$key];
        } else {
            foreach ($this->config['servers'] as $cate => $dbs) {
                if (array_key_exists($key, $dbs)) {
                    $cfg = $this->config['servers'][$cate][$key];
                    break;
                }
            }
        }

        if (!$cfg) {
            $this->logger("no server: {$key}");
        }

        $config = [
            'host'     => $cfg['host'],
            'port'     => $cfg['port'],
            'database' => $cfg['db'],
            'username' => $cfg['user'],
            'password' => $cfg['pass'],
        ];
        $this->_pdo[$key] = new MySQL($config);
        return $this->_pdo[$key];
    }


    /**
     * 日志
     * @param string $msg
     */
    private function logger($msg = '')
    {
        print date('Y-m-d H:i:sO ') . $msg . "\r\n";
    }


    /**
     * 设置参数
     */
    private function setOptions()
    {
        $this->option = getopt('i::h::', ['method:']);
        if (isset($this->option['h'])) {
            $help = <<<END
-------------------------------------------
-h          帮助
-i          显示配置信息
--method    执行特定方法 例:audit_tools --method outTrade

操作步骤：
1. 导出CSV文件                  从原始数据源导出[outTrade,outExp,outStatus]
2. 导入CSV文件  [inCSV]         导入到审计数据库
3. 修正金额     [fixTrade]      设定目标修正金额 (仅操作订单)
4. 导入订单     [inTrade]       删除消耗中的订单记录,然后导入订单到消耗表
5. 手动检查                     检查测试数据,非法超大数据
6. 平衡消耗     [balanceExp]    无期末则补充,其他情况补消耗(期初+消耗=期末)
7. 移动消耗     [moveExp]       使其任意时间点(期初+消耗>0)
-------------------------------------------

END;
            print_r($help);
            exit;
        }
    }

}


$audit = new AuditTools();
$audit->run();