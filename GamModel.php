<?php
require("dbconfig.php");
function addUser($username,$pwd) {
	global $db;
	$sql = "select username from user where username = ? ;";
    $stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);  //將執行完的結果放到$result裏
	if($rs = mysqli_fetch_assoc($result)){ //檢查用戶名有無重複
		return false;
	}else{//看有沒有抓到result那張select出來的表 
        $sql2 = "insert into user (username, password, money) values (?, ?, 1000)"; //sql指令的insert語法
		$stmt2 = mysqli_prepare($db, $sql2); //prepare sql statement
		mysqli_stmt_bind_param($stmt2, "ss", $username, $pwd); //bind parameters with variables(將變數bind到sql指令的問號中)
		mysqli_stmt_execute($stmt2);  //執行SQL
		return true;
	}
}
function loginCheck($username,$pwd){
	global $db;
    $sql = "select username from user where password = ? ;"; 
	//先寫一個sql指令，將使用者輸入的值?，用PASSWORD加密過，在跟password欄位比較是否相同
	//盡量用statement物件($stat)會比較安全
	$stmt = mysqli_prepare($db, $sql);//$db是另一個程式生成的資料庫連線物件,  prepare:表示用這個資料庫($db)把sql指令compile好
	mysqli_stmt_bind_param($stmt,"s",$pwd);//將使用者輸入的password，用字串的形式，去bind到$sql指令的?
	mysqli_stmt_execute($stmt);//執行一個sql指令
	$result = mysqli_stmt_get_result($stmt);  //將執行完的結果放到$result裏
	if($rs = mysqli_fetch_assoc($result)){ //看有沒有抓到result那張select出來的表 
		if($rs['username'] == $username){ //之後再比較相同用戶名欄位
			$_SESSION["userID"] = $username;
			return true;
		}else{
			$_SESSION["userID"] = '';
			return false;
		}
	}
	return false;
}

function getUserInfo($usr) {
	global $db;
	$sql = "select * from user where username = ? ;";
	$stmt = mysqli_prepare($db, $sql );
	mysqli_stmt_bind_param($stmt,"s",$usr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 

	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	while (	$rs = mysqli_fetch_assoc($result)) {
		$tArr=array(); //一維陣列存下面個欄位變數
		$tArr['id']=$rs['id'];
		$tArr['username']=$rs['username'];
		$tArr['money']=$rs['money'];
		$retArr[] = $tArr;
	}
	return $retArr;//最後是回傳一個二維陣列
}

function getRankings(){ //列出每個user(用錢排序)
	global $db;
	$sql = "select * from user ORDER BY money DESC;"; //用錢這個欄位排序(由大到小)
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	while (	$rs = mysqli_fetch_assoc($result)) {
		$tArr=array(); //一維陣列存下面個欄位變數
		$tArr['username']=$rs['username'];
		$tArr['money']=$rs['money'];
		$retArr[] = $tArr;
	}
	return $retArr;//最後是回傳一個二維陣列
}
function addRoom($username,$ansnum) {//莊家要開一個新房間
	global $db;
	$sql = "select * from room where dealerUsername = ? ;";
    $stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);  //將執行完的結果放到$result裏
    if($rs = mysqli_fetch_assoc($result)){ //如果發現房間已存在，更新房間的最終結果以及開放狀態
		$sql2 = "update room set answerNum = ? , status = '1' where dealerUsername = ? ;";
    	$stmt2 = mysqli_prepare($db, $sql2);
		mysqli_stmt_bind_param($stmt2, "is",$ansnum, $username);
   		mysqli_stmt_execute($stmt2);
	}else{  //如果發現房間不存在，新增房間的最終結果以及開放狀態
        $sql2 = "insert into room (dealerUsername, answerNum, status) values (?, ?, 1)";
    	$stmt2 = mysqli_prepare($db, $sql2);
		mysqli_stmt_bind_param($stmt2, "si", $username, $ansnum);
    	mysqli_stmt_execute($stmt2);
	}	
	return true;
	/*
	$sql3 = "select * from user where username = ? ;"; //檢查莊家有沒有錢
	$stmt3 = mysqli_prepare($db, $sql3);
	mysqli_stmt_bind_param($stmt3, "s", $username);
    mysqli_stmt_execute($stmt3);
	$result3 = mysqli_stmt_get_result($stmt3);
	$rs3 = mysqli_fetch_assoc($result3)
	$yourMoney = $rs3['money'];
	if( $yourMoney > '300'){ //莊家錢如果低於300，會自動儲值
		return true; 
	}else{
		$plus = $rs3['money'] + 500; //儲值500
		$sql4 = "update user set money = ? where username = ? ;";
    	$stmt4 = mysqli_prepare($db, $sql4);
		mysqli_stmt_bind_param($stmt4, "i", $plus);
   		mysqli_stmt_execute($stmt4);
		return false;
	}
	*/
}
function getRoomInfo($usr) { //回傳指定莊家的房間資訊(回傳二維陣列)
	global $db;
	$sql = "select * from room where dealerUsername = ?;";
	$stmt = mysqli_prepare($db, $sql );
	mysqli_stmt_bind_param($stmt,"s",$usr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 

	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	while (	$rs = mysqli_fetch_assoc($result)) {
		$tArr=array(); //一維陣列存下面個欄位變數
		$tArr['rid']=$rs['rid'];
		$tArr['username']=$rs['dealerUsername'];
		$tArr['ansnum']=$rs['answerNum'];
		$tArr['status']=$rs['status'];
		$retArr[] = $tArr;
	}
	return $retArr;//最後是回傳一個二維陣列
}
function getRoomInfo2($usr) { //回傳指定莊家的房間資訊(回傳一維陣列)
	global $db;
	$sql = "select * from room where dealerUsername = ?;";
	$stmt = mysqli_prepare($db, $sql );
	mysqli_stmt_bind_param($stmt,"s",$usr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 
	$rs = mysqli_fetch_assoc($result);
	$tArr=array(); //一維陣列存下面個欄位變數
	$tArr['rid']=$rs['rid'];
	$tArr['dealerUsername']=$rs['dealerUsername'];
	$tArr['answerNum']=$rs['answerNum'];
	$tArr['status']=$rs['status'];
	return $tArr;//最後是回傳一個一維陣列
}
function getRoomList(){ //列出房間狀態開著的room
	global $db;
	$sql = "select * from room where status = 1;"; 
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	while (	$rs = mysqli_fetch_assoc($result)) {
		$tArr=array(); //一維陣列存下面個欄位變數
		$tArr['rid']=$rs['rid'];
		$tArr['dealer']=$rs['dealerUsername'];
		$retArr[] = $tArr;
	}
	return $retArr;//最後是回傳一個二維陣列
}
function getMoney($usr){
    global $db;
	$sql = "select money from user where username = ? ;";
    $stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "s", $usr);
    mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);  //將執行完的結果放到$result裏
    $rs = mysqli_fetch_assoc($result);
	return $rs['money'];
}
function addBet($rid,$usr,$ans,$Betmoney) { 
	global $db;
	$money = getMoney($usr);
	if($money <  $Betmoney){ //當押注金額大於自己擁有的錢
		return false;
	}else{ 
		$sql = "select * from bet where username = ? ;";
    	$stmt = mysqli_prepare($db, $sql);
		mysqli_stmt_bind_param($stmt, "s", $usr);
    	mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);  
		if($rs = mysqli_fetch_assoc($result)){ //如果資料表裡有之前押注的訊息，將其覆寫
			$sql2 = "update bet set roomNum = ? , guessNum = ? , betMoney = ? where username = ? ;"; 
			$stmt2 = mysqli_prepare($db, $sql2);
			mysqli_stmt_bind_param($stmt2, "iiis", $rid , $ans, $Betmoney, $usr);
			mysqli_stmt_execute($stmt2);  //執行SQL
		}else{
			$sql2 = "insert into bet (roomNum, username, guessNum, betMoney) values (?, ?, ? ,?)"; //sql指令的insert語法
			$stmt2 = mysqli_prepare($db, $sql2); //prepare sql statement
			mysqli_stmt_bind_param($stmt2, "isii", $rid , $usr, $ans, $Betmoney); //bind parameters with variables(將變數bind到sql指令的問號中)
			mysqli_stmt_execute($stmt2);  //執行SQL
		}
		return true;
	}
}
function getBetInfo($usr) { //取得單一玩家的賭注訊息
	global $db;
	$sql = "select * from bet where username = ?;";
	$stmt = mysqli_prepare($db, $sql );
	mysqli_stmt_bind_param($stmt,"s",$usr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 

	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	while (	$rs = mysqli_fetch_assoc($result)) {
		$tArr=array(); //一維陣列存下面個欄位變數
		$tArr['roomNum']=$rs['roomNum'];
		$tArr['username']=$rs['username'];
		$tArr['guessNum']=$rs['guessNum'];
		$tArr['betMoney']=$rs['betMoney'];
		$retArr[] = $tArr;
	}
	return $retArr;//最後是回傳一個二維陣列
}
function getAllBetList($rid) { //拿到一間房間裏頭的所有賭注訊息
	global $db;
	$sql = "select * from bet where roomNum = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"i",$rid);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 

	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	while (	$rs = mysqli_fetch_assoc($result)) {
		$tArr=array(); //一維陣列存下面個欄位變數
		$tArr['roomNum']=$rs['roomNum'];
		$tArr['username']=$rs['username'];
		$tArr['guessNum']=$rs['guessNum'];
		$tArr['betMoney']=$rs['betMoney'];
		$retArr[] = $tArr;
	}
	return $retArr;//最後是回傳一個二維陣列
}
function countBet($rid) {//計算有幾個人參與這場賭注
	global $db;
	$sql = "select count(username) as count from bet where roomNum = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"i",$rid);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 
    $rs = mysqli_fetch_assoc($result);
	$retArr=array(); //用一個array存下面的每一筆資料(一筆資料也是一個array)
	$tArr=array(); //一維陣列存下面個欄位變數
	$tArr['countBet']=$rs['count'];
	$retArr[] = $tArr;
	return $retArr;//最後是回傳一個二維陣列
}
function PlayerWinUpdateMoney($dealerUsr,$playerUsr,$betmoney){ //玩家贏錢，莊家扣錢
	global $db;
	//查找玩家的錢包
	$sql = "select * from user where username = ? ;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"s", $playerUsr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$rs = mysqli_fetch_assoc($result);
	//玩家贏錢
	$sql = "update user set money = ? + (5 * ?) where username = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"iis",$rs['money'],$betmoney, $playerUsr);
	mysqli_stmt_execute($stmt);
	//查找莊家的錢包
	$sql = "select * from user where username = ? ;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"s", $dealerUsr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$rs = mysqli_fetch_assoc($result);
	//莊家輸錢
	$sql = "update user set money = ? where username = ? ;";
	$stmt = mysqli_prepare($db, $sql);
	$newDealerWallet = (int)$rs['money'] - (5 * (int)$betmoney);
	if($newDealerWallet < 0){ //莊家錢不能為負
		$newDealerWallet = 0;
	}
	mysqli_stmt_bind_param($stmt,"is",$newDealerWallet, $dealerUsr);
	mysqli_stmt_execute($stmt);
	return true;
}
function DealerWinUpdateMoney($dealerUsr,$playerUsr,$betmoney){ //玩家輸錢，莊家贏錢
	global $db;
	//查找莊家的錢包
	$sql = "select * from user where username = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"s", $dealerUsr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$rs = mysqli_fetch_assoc($result);
	//莊家贏錢,更新錢包
	$sql = "update user set money = ? + ? where username = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"iis",$rs['money'], $betmoney, $dealerUsr);
	mysqli_stmt_execute($stmt);
	//查找玩家的錢包
	$sql = "select * from user where username = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"s", $playerUsr);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$rs = mysqli_fetch_assoc($result);
	//玩家輸錢,更新錢包
	$sql = "update user set money = ? - ? where username = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"iis",$rs['money'], $betmoney, $playerUsr);
	mysqli_stmt_execute($stmt);
	return true;
}
function checkRoomStatus($rid){ //確認房間的開關狀態
	global $db;
	$sql = "select status from room where rid = ?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"i", $rid);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 
	$rs = mysqli_fetch_assoc($result);
	if((int)$rs['status'] == 1){
        return false;
	}
	return true;
}
function closeRoom($usr){//開獎後關閉房間狀態
	global $db;
    $sql = "update room set status = '0' where dealerUsername = ? ;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt,"s",$usr);
	mysqli_stmt_execute($stmt);
	return true;
}
function DeleteThisBet($usr){ //
	global $db;
	$sql = "delete from bet where username = ?";
    $stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "s", $usr);
    mysqli_stmt_execute($stmt);
	return true;
}
function checkBetisNull($usr){
	global $db;
	$sql = "select count(*) as count from bet where username = ?";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "s", $usr);
    mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt); 
	$rs = mysqli_fetch_assoc($result);
	if((int)$rs['count'] == 0){
		return true ;
	}
	return false;
}
?>