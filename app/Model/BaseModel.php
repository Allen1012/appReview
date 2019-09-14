<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model{

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = '';

    /**
     * 用来向表中插入数据的字段
     *
     * @var array
     */
    protected $tableColumns = [];

    /**
     * 批量插入或更新表中数据
     *
     * @param $data
     * @param string $table
     * @param array $columns
     * @return array
     */
    public function batchInsertOrUpdate($data,$table = '',$columns = []){

        if(empty($data)){//如果传入数据为空 则直接返回
            return [
                'insertNum' => 0,
                'updateNum' => 0
            ];
        }

        empty($table) && $table = $this->getTable();  //如果未传入table则通过对象获得
        empty($columns) && $columns = $this->getTableColumns();  //如果未传入table则通过对象获得

        //拼装sql
        $sql = "insert into ".$table." (";
        foreach ($columns as $k => $column) {
            $sql .= $column ." ,";
        }
        $sql = trim($sql,',');
        $sql .= " ) values ";

        foreach ($data as $k => $v){
            $sql .= "(";
            foreach ($columns as $kk => $column){
                if('updated_at' == $column){ //如果库中存在，create_at字段会被更新
                    $sql .= " '".date('Y-m-d H:i:s')."' ,";
                }else{
                    $val = ''; //插入数据中缺少$colums中的字段时的默认值
                    if(isset($v[$column])){
                        $val = $v[$column];
                        $val = addslashes($val);  //在预定义的字符前添加反斜杠的字符串。
                    }
                    $sql .= " '".$val."' ,";
                }
            }
            $sql = trim($sql,',');
            $sql .= " ) ,";
        }
        $sql = trim($sql,',');
        $sql .= "on duplicate key update ";
        foreach ($columns as $k => $column){
            $sql .= $column ." = values (".$column.") ,";
        }
        $sql = trim($sql,',');
        $sql .= ';';

        $columnsNum = count($data);
        $retNum = DB::update(DB::raw($sql));
        $updateNum = $retNum - $columnsNum;
        $insertNum = $columnsNum - $updateNum;
        return [
            'insertNum' => $insertNum,
            'updateNum' => $updateNum
        ];
    }

    /**
     * 返回表中字段
     *
     * @return array
     */
    public function getTableColumns(){
        if(empty($this->tableColumns)){
            return [];
        }
        return $this->tableColumns;
    }
}
