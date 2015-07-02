<?php
/*
 * theme : 文件下载类
 * intro ： 指定目录获取目录下的所有文件夹及子文件，实现下载功能
 * author / date : marvin / 2015-07-02
 */
error_reporting(E_ALL^E_NOTICE);
$array = ['app','x'];
$dir = new Checkout($array);


//下载文件
if (isset($_GET[option]) && $_GET['option']!='') {
	$info = $dir ->downLoad($_GET['option']);
	die;
}


//显示目录结构
$fileArray = $dir ->showDir();
echo $dir->showHtml($fileArray);

/*
 * theme : 文件目录下载类
 * intro : 包含显示目录结构，遍历文件夹，下载文件，文件显示格式化，简单的html展示，访问限制
 * @parm ：$dir array 目录名称
 * author / date : marvin / 2015-07-02
 */
class Checkout{

 protected $dirArray;
 protected $host;

 public function __construct($dir){
 	header("Content-Type: text/html; charset=UTF-8");
    header("Cache-Control: no-store");
 	$this->host = dirname(__FILE__);
 	$this->dirArray = $dir;
 }

//显示目录结构
 public function showDir () {

 	$arrayDir = ['logs']; //限定可以访问的目录

 	if (!is_array($this->dirArray)) {
 		$info['text'] = 'not a array';
 		$info['status'] =1;
 		return $info;
 	}

 	foreach ($this->dirArray as $key => $value) {
 		//$arrayDir = ['logs'];
 		if (!in_array($value, $arrayDir)) {
 			$info['text'] = '该目录不允许访问';
 			$info['status'] =1;
 			return $info;
 		}

 		$path = $this->host.'/'.$value;
 		if (is_dir($path)){
			$text[$key]= $this->my_scandir($path,$value);
			$info['status'] =0;
		}else{
			$info['text'] = '不是一个真实的目录';
			$info['path'] = $path;
 			$info['status'] =1;
		}
 	}
 	$info['text'] = $text;
 	return $info;
 }

//遍历文件及文件夹
 protected function my_scandir($dir,$dirName) {
 	$files = array();  
    $dir_list = scandir($dir);  
    foreach($dir_list as $key=>$file)  {  

        if ( $file != ".." && $file != "." )   { 
            if ( is_dir($dir . "/" . $file) )   { 

                $files[$dirName.'|'.$file] = $this->my_scandir($dir . "/" . $file,$dirName.'|'.$file); 
            }else{  

                $files[$key]['file'] = $file;
                $files[$key]['dirUrl'] = $dir . "/" . $file; 
                $fmt = filemtime($dir . "/" . $file);
                $files[$key]['fmt'] = date('Y-m-d H:i:s',$fmt); 

            }  
        } 
    }      
    return $files;  
 }

 //下载文件
 public function downLoad ($dir) {

 	if(!file_exists($dir)){ 
		echo "没有该文件文件"; 
		return ;  
	} 

	$fp=fopen($dir,"r"); 
	$file_size=filesize($dir); 

	//获得文件名 
	$array = explode('/', $dir);
	$file_name = end( $array );

	//下载文件需要用到的头 
	Header("Content-type: application/octet-stream"); 
	Header("Accept-Ranges: bytes"); 
	Header("Accept-Length:".$file_size); 
	Header("Content-Disposition: attachment; filename=".$file_name); 

	$buffer=1024; 
	$file_count=0; 

	//向浏览器返回数据 
	while(!feof($fp) && $file_count<$file_size){

		$file_con=fread($fp,$buffer); 
		$file_count+=$buffer; 
		echo $file_con; 

	}

	fclose($fp); 
 }

//文件显示格式化
 public function foreachList ($array,$k) {

	$str = '';
	foreach ($array as $key => $value) {

		if ( !is_numeric($key) ){

		 	$str .= $this->foreachList ($value,$key);
		 }else{

			$str .= "[$k]<a href='Checkout.php?option=".$value['dirUrl']."'>$value[file]--[$value[fmt]]</a><br />";
		}
	}
	return $str;
 }
 
 //html 页面显示
 public function showHtml ($fileArray) {
 	if ($fileArray['status'] <1) {

		if (count ($fileArray['text']) >0) {

			foreach ($fileArray['text'] as $key => $value) {
				$info .= $this ->foreachList ($value,$this->dirArray[$key]);
			}

	    	return  $info;
		}else{
			return  '目录为空';
		}
   

	}else
		return $fileArray[text];
 }

}