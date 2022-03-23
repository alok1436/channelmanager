<?php 
$total_page_querys = 0;
$querys_ran = array();
$strFields = '';
function pr($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit();
}
/*****************************************************************************
	Database Class for MySQL Server. Please do not change anything
*****************************************************************************/
class Database {
	//var $Con;
	private static $db;
	private $Con;	
	function __construct() {
		$this->Con = mysqli_connect(DATABASE_HOST,DATABASE_USER,DATABASE_PASSWORD,DATABASE_NAME);
	}
	
	function __destruct() {
    	$this->Con->close();
	}
	public static function getConnection() {
        if (self::$db == null) {
            self::$db = new Database();
        }
        return self::$db->Con;
	}
	function connect(){
		$this->Con = mysqli_connect(DATABASE_HOST,DATABASE_USER,DATABASE_PASSWORD,DATABASE_NAME);
	}


	function getTimeout(){
		$sql = "select edit_session_timeout from tbl_account a LEFT JOIN tbl_user b ON a.client_id = b.primary_client where b.user_id = '".$_SESSION['userid']."'";
		$result = $this->query($sql,__FILE__,__LINE__);
		$row = $this->fetch_assoc($result);
		return $row["edit_session_timeout"];
	}
	
	function close() {
		mysqli_close($this->Con);
	}

	function err(){
		echo  mysqli_error($this->Con);
	}

	function getalertTimeout(){
		$sql = "select edit_alert_timeout from tbl_account a LEFT JOIN tbl_user b ON a.client_id = b.primary_client where b.user_id = '".$_SESSION['userid']."'";
		$result = $this->query($sql,__FILE__,__LINE__);
		$row = $this->fetch_assoc($result);
		return $row["edit_alert_timeout"];
	}

	function geturl(){
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
		return $actual_link;
	}

	
	function query($sql,$errorFile='__FILE__',$errorLine='__LINE__') {
		$this->close();
		$this->connect();
		global $total_page_querys, $querys_ran;
		$total_page_querys++;
		$querys_ran[] = $sql;
		$result = mysqli_query($this->Con,$sql) or $this->error($sql,__FILE__,__LINE__);
		if($result){
			
		}else{
			$err = mysqli_error($this->Con);
		}
		return $result;
		$this->close();

	}
	function query_log($sql,$case_id,$errorFile='__FILE__',$errorLine='__LINE__') {
		$this->close();
		$this->connect();
		global $total_page_querys, $querys_ran;
		$total_page_querys++;
		$querys_ran[] = $sql;
		$result = mysqli_query($this->Con,$sql);
		if($result){
			
		}else{
			$err = mysqli_error($this->Con);
			$errt = str_replace("'","",$err);
			$a = array();
			$a['case_id'] = $case_id;
			$a['method'] = "Insert";
			$a['sql_error'] = "Error:".$errt;
			$this->specialInsert('tbl_error_list',$a);
		}
		return $result;
		$this->close();

	}
	function query_new($sql,$errorFile='__FILE__',$errorLine='__LINE__') {
		global $total_page_querys, $querys_ran;
		$total_page_querys++;
		$querys_ran[] = $sql;
		//MYSQLI_USE_RESULT
		$result = mysqli_query($this->Con,$sql);
		if($result){
			
			return $result;
		}else{
			$err = mysqli_error($this->Con,$sql);
		}
		return $result;
		
	
	}
	function getItem($tbl_name,$field_name,$field_id='',$output_field=''){
		  $sql = "Select * from ".$tbl_name. " where ".$field_name." = '".$field_id."'";
		  $result = $this->query($sql);
		  $return = array();
		  $row = $this->fetch_assoc($result);
		  		$rowoutput = $row[$output_field];
		  return $rowoutput;

	}

	function getRow($tbl_name,$field_name,$field_id=''){
		echo $sql = "Select * from ".$tbl_name. " where ".$field_name." = '".$field_id."'";
		$result = $this->query($sql);
		$return = array();
		$row = $this->fetch_assoc($result);
		return $row;
  	}

	function query_for_special_insert($sql,$errorFile='__FILE__',$errorLine='__LINE__') {
		$this->close();
		$this->connect();
		global $total_page_querys, $querys_ran;
		$total_page_querys++;
		$querys_ran[] = $sql;
		$result = mysqli_query($this->Con,$sql);
		$last_inserted_id = $this->last_insert_id();
		$this->close();
		return $last_inserted_id;
	}
	
	function fetch_field($result,$i)
	{
		return mysqli_fetch_field($result,$i);
	}
	function real_escape_string($result){
		return mysqli_real_escape_string($this->Con,$result);
	}
	function fetch_array($result) {
		$row = mysqli_fetch_array($result);
		
		if(!empty($row) && count($row)-1>0)
		foreach($row as $key=>$value)
		$row[$key]=stripslashes($value);
		
		return $row;
	}
	
	function fetch_row($result) {
		$row=mysqli_fetch_row($result);
		
		if(!empty($row) && count($row)-1>0)
		foreach($row as $key=>$value)
		$row[$key]=stripslashes($value);
		
		return $row;
	}
	
	function insert($table,$DataArray,$printSQL = false,$keep_tags='',$remove_tags='',$filterhtml=1) {
		if(count($DataArray) == 0) {
			die($this->error("INSERT INTO statement has not been created",__FILE__,__LINE__));
		}
		$strFields = $strValues = '';
		foreach($DataArray as $key => $val) {
			$strFields.= "`".$key."`,";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues.= "CURDATE(),";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues.= "CURTIME(),";
			} else {
			
			if($filterhtml==1) {
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($val);
			}
				
				$strValues.= "'".addslashes($this->filter($val))."',";	
			}
		}
		$strFields = substr($strFields,0,strlen($strFields)-1);
		$strValues = substr($strValues,0,strlen($strValues)-1);
		 $sql = "INSERT INTO `".$table."` (".$strFields.") VALUES(".$strValues.")";

		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			$this->query($sql,__FILE__,__LINE__);
		}
	}

	function bulkInsert($table,$fieldArray,$DataArray, $printSQL = false) {
		$values = array();
		$strFields = $strValues = '';
		foreach ($DataArray as $rowValues) {
			foreach ($rowValues as $key => $rowValue) {
				$rowValues[$key] = "'".$rowValues[$key]."'";
			}
			$values[] = "(" . implode(', ', $rowValues ) . ")";
		}
		$sql = "INSERT INTO `".$table."` (".implode(', ',($fieldArray)).") VALUES " . implode (', ', $values) . "";
		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			$this->query($sql,__FILE__,__LINE__);
		}
	}

	function filter($str=''){
		$val = str_replace("'","&#39;",$str);
		return $val;
	}
	function filter_back($str=''){
		$val = str_replace(["&#39;","'"],"",$str);
		return $val;
	}
	function specialInsert($table,$DataArray,$printSQL = false,$keep_tags='',$remove_tags='',$filterhtml=1) {
		if(count($DataArray) == 0) {
			die($this->error("INSERT INTO statement has not been created",__FILE__,__LINE__));
		}
		foreach($DataArray as $key => $val) {
			$strFields.= "`".$key."`,";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues.= "CURDATE(),";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues.= "CURTIME(),";
			} else {
			
			if($filterhtml==1) {
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($this->filter($val));
			}
				
				$strValues.= "'".addslashes($val)."',";	
			}
		}
		$strFields = substr($strFields,0,strlen($strFields)-1);
		$strValues = substr($strValues,0,strlen($strValues)-1);
 		$sql = "INSERT INTO `".$table."` (".$strFields.") VALUES(".$strValues.")";

		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			return $this->query_for_special_insert($sql,__FILE__,__LINE__);
		}
	}
	
	function specialInsertNew($table,$DataArray,$printSQL = false,$keep_tags='',$remove_tags='',$filterhtml=1) {
		if(count($DataArray) == 0) {
			die($this->error("INSERT INTO statement has not been created",__FILE__,__LINE__));
		}
		foreach($DataArray as $key => $val) {
			$strFields.= "`".$key."`,";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues.= "CURDATE(),";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues.= "CURTIME(),";
			} else {
			
			if($filterhtml==1) {
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($this->filter($val));
			}
				
				$strValues.= "'".addslashes($val)."',";	
			}
		}
		$strFields = substr($strFields,0,strlen($strFields)-1);
		$strValues = substr($strValues,0,strlen($strValues)-1);
 		echo $sql = "INSERT INTO `".$table."` (".$strFields.") VALUES(".$strValues.")";
		die();

		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			return $this->query_for_special_insert($sql,__FILE__,__LINE__);
		}
	}

	function specialInsert_new($table,$DataArray,$printSQL = false,$keep_tags='',$remove_tags='',$filterhtml=1) {
		if(count($DataArray) == 0) {
			die($this->error("INSERT INTO statement has not been created",__FILE__,__LINE__));
		}
		foreach($DataArray as $key => $val) {
			$strFields.= "`".$key."`,";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues.= "CURDATE(),";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues.= "CURTIME(),";
			} else {
			
			if($filterhtml==1) {
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($this->filter($val));
			}
				
				$strValues.= "'".addslashes($val)."',";	
			}
		}
		$strFields = substr($strFields,0,strlen($strFields)-1);
		$strValues = substr($strValues,0,strlen($strValues)-1);
 		echo $sql = "INSERT INTO `".$table."` (".$strFields.") VALUES(".$strValues.")";
	
		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			return $this->query_for_special_insert($sql,__FILE__,__LINE__);
		}
	}

	function update($table,$DataArray,$updateOnField,$updateOnFieldValue,$printSQL = false,$keep_tags='',$remove_tags='') {
		if(count($DataArray) == 0) {
			die($this->error("UPDATE statement has not been created",__FILE__,__LINE__));
		}
		$sql = "UPDATE ".$table." SET ";
		foreach($DataArray as $key => $val) {
			$strFields = "`".$key."`";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues = "CURDATE()";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues = "CURTIME()";
			} else {
			
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($val);
				
				$strValues = "'".addslashes($this->filter($val))."'";
			}
			$sql.= $strFields."=".$strValues.", ";
		}
		$sql = substr($sql,0,strlen($sql)-2);
	  	$sql.= " WHERE `".$updateOnField."`='".addslashes($updateOnFieldValue)."'";
		
		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			$a = "";
			if($updateOnField == 'case_id'){
				$a = addslashes($updateOnFieldValue);
			}
			$this->query_log($sql,$a,__FILE__,__LINE__);
		}
	}
	function specialUpdate($table,$DataArray,$updateOnField,$updateOnFieldValue,$printSQL = false,$keep_tags='',$remove_tags='') {
		if(count($DataArray) == 0) {
			die($this->error("UPDATE statement has not been created",__FILE__,__LINE__));
		}
		$sql = "UPDATE ".$table." SET ";
		foreach($DataArray as $key => $val) {
			$strFields = "`".$key."`";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues = "CURDATE()";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues = "CURTIME()";
			} else {
			
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($val);
				
				$strValues = "'".addslashes($this->filter($val))."'";
			}
			$sql.= $strFields."=".$strValues.", ";
		}
		$sql = substr($sql,0,strlen($sql)-2);
	  	$sql.= " WHERE `".$updateOnField."`='".addslashes($updateOnFieldValue)."'";
	
		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			$a = "";
			if($updateOnField == 'case_id'){
				$a = addslashes($updateOnFieldValue);
			}
			$this->query_log($sql,$a,__FILE__,__LINE__);
		}
	}
	function update_new($table,$DataArray,$updateOnField,$updateOnFieldValue,$printSQL = false,$keep_tags='',$remove_tags='') {
		if(count($DataArray) == 0) {
			die($this->error("UPDATE statement has not been created",__FILE__,__LINE__));
		}
		$sql = "UPDATE ".$table." SET ";
		foreach($DataArray as $key => $val) {
			$strFields = "`".$key."`";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues = "CURDATE()";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues = "CURTIME()";
			} else {
			
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($val);
				
				$strValues = "'".addslashes($val)."'";
			}
			$sql.= $strFields."=".$strValues.", ";
		}
		$sql = substr($sql,0,strlen($sql)-2);
	 	$sql.= " WHERE `".$updateOnField."`='".addslashes($updateOnFieldValue)."'";
		//echo $sql;
		//die();
		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			$this->query($sql,__FILE__,__LINE__);
		}
	}
	
	function last_insert_id() {
		return mysqli_insert_id($this->Con);
	}
	
	function result($result,$row,$column) {
		return mysqli_result($result,$row,$column);
	}
	
	function num_rows($result) {
		return mysqli_num_rows($result);
	}
	
	function getDateDiff($coming_date) {
		$diff_sql = "SELECT DATEDIFF('".$coming_date."','".date('Y-m-d')."')";
		$diff_res = $this->query($diff_sql,__FILE__,__LINE__);
		return $this->result($diff_res,0,0);
	}
	
	function getTimeStampDiff($comming_timestamp)
	{
		$startdate = time();
		$enddate = $comming_timestamp;
		
		$time_period = ( $enddate - $startdate );
		
		$days = 0;
		$hours = 0;
		$minutes = 0;
		$seconds = 0;
		
		$time_increments = array( 'Days' => 86400,
		'Hours' => 3600,
		'Minutes' => 60,
		'Seconds' => 1 );
		
		$time_span = array();
		
		while( list( $key, $value ) = each( $time_increments )) {
		$this_value = (int) ( $time_period / $value );
		$time_period = ( $time_period % $value );

		$time_span[$key] = $this_value;
		}
		
		return $time_span;
	}	
	
		
	function record_number($sql) {
		$result = $this->query($sql,__FILE__,__LINE__);
		$cnt = $this->num_rows($result);
		return $cnt;
	}
	
	function pagination($sql,$rowsPerPage,$Page) {
	
		$PageResult = $this->record_number($sql);
		if($Page == "" || $Page == 1) {
			$Page = 0;
		} else {
			$Page = ($Page-1) * $rowsPerPage;
		}
		$RecordPerPage = ceil($PageResult/$rowsPerPage);
		$ReturnResult = $this->query($sql." limit ".$Page.",".$rowsPerPage."",__FILE__,__LINE__);
		return $ReturnResult;
	}
	
	function DisplayAjaxPage($rowsPerPage,$Page,$allCount,$object='contact',$method='GetContact',$target='search_result')
	{
		if($Page > 1){ ?>
		<a onclick="javascript:<?php echo $object; ?>.<?php echo $method; ?>(document.getElementById('search').value, <?php echo $Page-1; ?> ,'','','<?php echo $object; ?>',{target: '<?php echo $target; ?>', preloader: 'prl'});" href="#">Previous</a>
		<?PHP }
		
		if($allCount <= $rowsPerPage) $limit = 0;
		elseif(($allCount % $rowsPerPage) == 0) $limit = ($allCount / $rowsPerPage) + 1;
		else $limit = ($allCount / $rowsPerPage) + 1;
		
		if($limit > 10 && $Page > 5){
		if($Page + 4 <= $limit){
		$start = $Page - 5;
		$end = $Page + 4;
		}else{
		$start = $limit - 9;
		$end = $limit;
		}
		}elseif($limit > 10){
		$start = 1;
		$end = 10;
		}else{
		$start = 1;
		$end = $limit;
		}
		
		if($start > 1) echo "...&nbsp;";
		$start = ceil($start);
		$end = ceil($end);
		for($i=$start;$i<$end;$i++){
		if($i != $Page)
		 $ext = ' onclick="javascript:'.$object.'.'.$method.'(document.getElementById(\'search\').value, '.($i).','."'','','".$object."',".'{target: \''.$target.'\', preloader: \'prl\'});" href="#" ';
		
		else $ext = ' style="color:#FF0000; text-decoration:none;" ';
		echo '<a' . $ext . '>' . $i . '</a>&nbsp;';
		}
		if($end < ceil($limit)) echo "...";
		if($Page < ($allCount / $rowsPerPage) and $limit>1){ ?>
		<a onclick="javascript:<?php echo $object; ?>.<?php echo $method; ?>(document.getElementById('search').value, <?php echo $Page+1; ?> ,'','','<?php echo $object; ?>',{target: '<?php echo $target; ?>', preloader: 'prl'});" href="#">Next</a>
		<?PHP } 	
	}
	
	function pagination_page_number($sql,$DividedRecordNumber,$Page,$PageName,$QueryString) 
	{
		$PageResult = $this->record_number($sql);
		$RecordPerPage = ceil($PageResult/$DividedRecordNumber);
		if($Page == "") {
			$Page = 1;
		}
		
		$GET_INDEX = $_GET["index"];
		if( $GET_INDEX == 'List' ){ $QueryString = "index=List"; }
			
			
			$str = "<select class=\"txt\" name=\"cmbPage\" id=\"cmbPage\" onchange=\"javascript:_doPagination('".$PageName."','".$QueryString."');\">\n";
			for($i = 1;$i <= $RecordPerPage;$i++) {
				if($Page == $i) {
					$selected = ' selected';
				} else {
					$selected = '';
				}
				$str.= "<option value=\"".$i."\"".$selected.">Page ".$i."</option>\n";
			}
			$str.= "</select>";
			echo $str;
	}
	
	function pagination_page_number_new($sql,$DividedRecordNumber,$Page,$PageName,$QueryString) {
		$PageResult = $this->record_number($sql);
		$RecordPerPage = ceil($PageResult/$DividedRecordNumber);
		if($Page == "") {
			$Page = 1;
		}
		
			
		$str = "<select class=\"txt\" name=\"cmbPage\" id=\"cmbPage\" onchange=\"javascript:_doPagination('".$PageName."','".$QueryString."');\">\n";
		for($i = 1;$i <= $RecordPerPage;$i++) {
			if($Page == $i) {
				$selected = ' selected';
			} else {
				$selected = '';
			}
			$str.= "<option value=\"".$i."\"".$selected.">Page ".$i."</option>\n";
		}
		$str.= "</select>";
		echo $str;
	}
	
	function paging($sql,$DividedRecordNumber,$Page,$PageName,$QueryStringName,$Class) {
		$PageResult = $this->record_number($sql);
		if($PageResult > $DividedRecordNumber):
			$RecordPerPage = ceil($PageResult/$DividedRecordNumber);
			if($Page == "") {
				$Page = 1;
			}
			$PageCount = $Page - 1;
			if($PageCount > 0) {
				if(empty($QueryStringName)) {
					echo "<a href='".$PageName."?page=".$PageCount."' class='".$Class."'>&lt;&lt; Prev</a>&nbsp;";
				} else {
					echo "<a href='".$PageName."?page=".$PageCount."&".$QueryStringName."' class='".$Class."'>&lt;&lt; Prev</a>&nbsp;&nbsp;";
				}
			} else {
				echo "";
			}
			for($i = 1;$i <= $RecordPerPage;$i++) {
				if($Page == $i) {
					echo "<b>".$i."</b>&nbsp;";
				} else {
					echo "<a href='".$PageName."?page=".$i."&".$QueryStringName."' class='".$Class."'>".$i."</a>&nbsp;";
				}
			}
			$PageCount = $Page + 1;
			if($PageCount < $RecordPerPage + 1) {
				if(empty($QueryStringName)) {
					echo "<a href='".$PageName."?page=".$PageCount."' class='".$Class."'>Next &gt;&gt;</font>";
				} else {
					echo "<a href='".$PageName."?page=".$PageCount."&".$QueryStringName."' class='".$Class."'>Next &gt;&gt;</a>";
				}
			} else {
				echo "";
			}
		else:
			echo "&nbsp;";
		endif;
	}			
	
	function error($arg_error_msg,$arg_file,$arg_line) {
		if(empty($arg_error_msg)==false) {
			$error_msg = "<div style=\"font-family: Tahoma; font-size: 11px; padding: 10px; background-color: #FFD1C4; color: #990000; font-weight: bold; border: 1px solid #FF0000; text-align: center;\">";
			$error_msg.= $arg_error_msg."<br>in file ".$arg_file." at line number ".$arg_line;
			$error_msg.= "</div>";
			return $error_msg;
		}
	}

 	function remove_HTML($s , $keep = 'p|strong|em|span|ol|li|ul|img|a' , 
 							$expand = 'script|style|noframes|select|option|html|head|meta|title|body|input|textarea|link|h1|form|table|tr|tbody|td|th|div')
	{
        $s = ' ' . $s;       
        if(strlen($keep) > 0){
            $k = explode('|',$keep);
            for($i=0;$i<count($k);$i++){
                $s = str_replace('<' . $k[$i],'[{(' . $k[$i],$s);
                $s = str_replace('</' . $k[$i],'[{(/' . $k[$i],$s);
            }
        }
   
        while(stripos($s,'<!--') > 0){
            $pos[1] = stripos($s,'<!--');
            $pos[2] = stripos($s,'-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }
        if(strlen($expand) > 0){
            $e = explode('|',$expand);
            for($i=0;$i<count($e);$i++){
                while(stripos($s,'<' . $e[$i]) > 0){
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s,'<' . $e[$i]);
                    $pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s,$pos[1],$len[2]);
                    $s = str_replace($x,'',$s);
                }
            }
        }
   
        while(stripos($s,'<') > 0){
            $pos[1] = stripos($s,'<');
            $pos[2] = stripos($s,'>', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 1;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }
       
        for($i=0;$i<count($k);$i++){
            $s = str_replace('[{(' . $k[$i],'<' . $k[$i],$s);
            $s = str_replace('[{(/' . $k[$i],'</' . $k[$i],$s);
        }       
    	return trim($s);
	}

   	function field_name($result,$num) {
		$field_name = mysqli_field_name($result,$num);
		return $field_name;
	}
	
   	function num_fields($result) {
		$field_count = mysqli_num_fields($result);
		return $field_count;
	}
	
	function fetch_assoc($result) {
		return mysqli_fetch_assoc($result);
	}
	
	function field_seek($result, $i) {
		return mysqli_field_seek($result,$i);
	}
	
	function free_result($result) {
		return mysqli_free_result($result);
	}
	
	function affected_rows($con) {
		return mysqli_affected_rows($con->Con);
	}
	
	function data_seek($result,$i) {
		return mysqli_data_seek($result , $i);
	}

	function get($tbl_name,$where){
		echo $sql = "Select * from ".$tbl_name. " where ".$where;
		$result = $this->query($sql);
		$rows = array();
		if($this->num_rows($result)){
	        while($row = $result->fetch_assoc()) {
	            $rows[] = $row;
	        }
    	}
		return $rows;
  	}

  	function update_by_multiple_wheres($table,$DataArray,$whereArray,$printSQL = false,$keep_tags='',$remove_tags='') {
		if(count($DataArray) == 0) {
			die($this->error("UPDATE statement has not been created",__FILE__,__LINE__));
		}
		$sql = "UPDATE ".$table." SET ";
		foreach($DataArray as $key => $val) {
			$strFields = "`".$key."`";
			if($val == "CURDATE()" && $val != '' && $val != NULL) {
				$strValues = "CURDATE()";
			} elseif($val == "CURTIME()" && $val != '' && $val != NULL) {
				$strValues = "CURTIME()";
			} else {
			
				if($keep_tags!='')
				$val=strip_tags($val,$keep_tags);
				else
				$val=strip_tags($val);
				
				$strValues = "'".addslashes($this->filter($val))."'";
			}
			$sql.= $strFields."=".$strValues.", ";
		}
		$sql = substr($sql,0,strlen($sql)-2);
		if(isset($whereArray) && !empty($whereArray)){
		    $where_query = " WHERE ";
		    foreach($whereArray as $where_key => $where_val){
		        $where_query .= "`".$where_key."`='".addslashes($where_val)."' AND ";
		    }
		    $where_query = rtrim($where_query," AND ");
		}
	  	$sql .= $where_query;
		
		if($printSQL == true) {
			echo $this->error($sql,__FILE__,__LINE__);
		} else {
			$this->query($sql,__FILE__,__LINE__);
		}
	}
}?>