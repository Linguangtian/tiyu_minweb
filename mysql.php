<?php  
//最后修改日期20180503
//$tablename = "ims_ewei_shop_article_log";
//getarray($tablename);
$db = new mysqli('localhost', 'root', 'root', 'mysqlcreate'); 
$res = $db->query("SHOW TABLES"); 
$rt = array();  
if ($res instanceof mysqli_result)  
{  
    while (($row = $res->fetch_assoc()) != FALSE)  
    {  
        
        $rt[] = $row;  
    }  
} 
//var_dump($rt);
$allarray = array();
foreach ($rt as $k => $v ) {
	
	$allarray[$k] = getarray($v['Tables_in_mysqlcreate']);
	
}
//var_dump(str_replace('"','\"',serialize($allarray)));
//echo json_encode($allarray);

$ser =  base64_encode(serialize($allarray));

echo $ser;

//函数开始
function getarray($tablename){
$db = new mysqli('localhost', 'root', 'root', 'mysqlcreate');  
  
if ($db->connect_errno)  
{  
    die("数据库连接失败: " . $db->connect_error);  
}  


$res = $db->query("SHOW FULL FIELDS FROM `$tablename`");  
$res2 = $db->query("SHOW TABLE STATUS like '$tablename'"); 
$res3 = $db->query("SHOW  INDEX FROM `$tablename`"); 



$rt = array();  
if ($res instanceof mysqli_result)  
{  
    while (($row = $res->fetch_assoc()) != FALSE)  
    {  
        $row['CanBeNull'] = $row['Null'] === 'YES';   //字段值是否可以为空，是的话值为'YES'  
        $rt[] = $row;  
    }  
}  
$rt2 = array();  
if ($res2 instanceof mysqli_result)  
{  
    while (($row2 = $res2->fetch_assoc()) != FALSE)  
    {  
        
        $rt2[] = $row2;  
    }  
} 
$rt3 = array(); 
if ($res3 instanceof mysqli_result)  
{  
    while (($row3 = $res3->fetch_assoc()) != FALSE)  
    {  
        
        $rt3[] = $row3;  
    }  
}


$fields = array();
$rt = array_iconv($rt);
foreach ($rt as $k => $v ) 
		{
			$fields[$v['Field']]['name'] = $v['Field'];
			
			
			$arr=explode('(',$v['Type']);
			$fields[$v['Field']]['type'] = $arr[0];
			
			
			if(preg_match('/\((.*)\)/i',$v['Type'],$matches)){
			
				$fields[$v['Field']]['length'] = $matches[1];
			}else{
				$fields[$v['Field']]['length'] = "";
			}
		   
		   
			if($v['Null'] == 'YES'){
				$fields[$v['Field']]['null'] = true;
			}else{
				$fields[$v['Field']]['null'] = false;
			}
			
			$fields[$v['Field']]['signed'] = true; //signed暂时不知道什么意思
			if($v['Extra'] == 'auto_increment'){
				$fields[$v['Field']]['increment'] = true;
			}else{
				$fields[$v['Field']]['increment'] = false;
			}
			
			$fields[$v['Field']]['default'] = $v['Default'];
			
		}
	
$indexes = array();
$indexfields = array();
//var_dump($rt3);
foreach ($rt3 as $k => $v ) 
		{
			if($v['Key_name'] =='PRIMARY'){
				$indexes['PRIMARY'] = array();
				$indexes['PRIMARY']['name'] = "PRIMARY";
				$indexes['PRIMARY']['type'] = "primary";
				//$indexes['PRIMARY']['fields'] = array($v['Column_name']);
				$indexfields[$k] = $v['Column_name'];
			}else{
				$indexes[$v['Key_name']]['name'] = $v['Key_name'];
				$indexes[$v['Key_name']]['type'] = "index";
				$indexes[$v['Key_name']]['fields'] = array($v['Column_name']);
				if($v['Sub_part']){
					$indexes[$v['Key_name']]['length'] = $v['Sub_part'];
				}
			}
			
		}
$indexes['PRIMARY']['fields'] = $indexfields;

$array = array();
$array['tablename'] = $rt2[0]['Name'] ;
$array['charset'] = $rt2[0]['Collation'];
$array['engine'] = $rt2[0]['Engine'];
$array['increment'] = $rt2[0]['Auto_increment'];
$array['fields'] = $fields;
$array['indexes'] = $indexes;
//echo count($indexes['PRIMARY']['fields']);

if(count($indexes['PRIMARY']['fields']) == 0){
	unset($array['indexes']);
}



  
//print_r($rt2);
 
  
//print_r($rt);  
  

  
//print_r($array);
//echo json_encode($array);
@$db->close();
//var_dump($array);
  return $array;	
}


function array_iconv($arr, $in_charset="gbk", $out_charset="utf-8")
{
  $ret = eval('return '.iconv($in_charset,$out_charset,var_export($arr,true).';'));
  return $ret;
}
