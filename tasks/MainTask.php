<?php

use Phalcon\Cli\Task;

class MainTask extends Task
{
    /**
     * 插入SQL
     */
    const INSERT_SQL = 'INSERT INTO `sample`(`string`, `string_json`) VALUES ';

    /**
     * 每多少条执行一次插入
     */
    const INSERT_PER = 500;

    public function mainAction()
    {
        echo '---- json convert tasks start ----' . PHP_EOL;

        set_time_limit(0);
        echo 'memory usage at beginning :' . memory_get_usage() . PHP_EOL;
        $timeStart = microtime(true);

        $count = 0;
        $insertData = [];

        //用迭代器读取数据
        $iterator = new \Lib\JsonReader('storage/sample.json');
        foreach ($iterator as $json) {
            try {
                //验证原始内容是否为json
                $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                echo 'read invalid json data :' . $json . PHP_EOL;
                continue;
            }

            if (isset($data['string'])) {
                $origin = $data['string'];
                try {
                    //转化为json进行再次验证内容，直接替换^^字符变为json字符串也可以 效率差距很小
                    $after = json_encode(explode('^^', trim($origin, '^')), JSON_THROW_ON_ERROR);
//                    $after = '["'.str_replace('^^', '","', substr($origin, 1, -1)).'"]';
                    array_push($insertData, $origin, $after);
                    $count++;
                } catch (JsonException $e) {
                    echo 'convert invalid string data :' . $origin . PHP_EOL;
                    continue;
                }
            } else {
                echo 'error data :' . json_encode($data) . PHP_EOL;
                continue;
            }

            /**
             * 可以通过多条同时插入提升执行效率
             */
            if ($count % self::INSERT_PER * 2 === 0) {
                $this->multiInsert($insertData);
                $insertData = [];
            }
        }

        //最后没有导入的数据一次性插入
        if (count($insertData) > 0) {
            $this->multiInsert($insertData);
        }

        $timeEnd = microtime(true);
        echo 'process ended, and ' . $count . ' cols has been inserted' . PHP_EOL;
        echo 'memory usage at last :' . memory_get_usage() . PHP_EOL;
        echo 'total execution time :' . ($timeEnd - $timeStart) . PHP_EOL;
        echo '---- json convert tasks end ----' . PHP_EOL;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function multiInsert($data): bool
    {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'shanlu1*Wan');
            $sql = self::INSERT_SQL . implode(',', array_fill(0, count($data) / 2, '(?,?)'));
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $pdo = null;
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage() . PHP_EOL;
            return false;
        }
    }
}