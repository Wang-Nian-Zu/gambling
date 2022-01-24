<?php
require('GamModel.php');

if(isset($_REQUEST['act'])){
    $act = $_REQUEST['act'];
}else{
    $act = '';
}
switch ($act) { //用switch語法，判斷act這個變數要做哪件事
	case "addUser": // 增加工作的功能
		$username=$_POST['username']; //前端用post傳，Controller就用post接
		$pwd=$_POST['pwd']; //待辦事項內容

		if (($username != "")&($pwd != "")) { //防呆，一樣做簡單邏輯判斷，當title不是空的，再將它導入函數
			if(addUser($username,$pwd)){
				$message = "註冊成功";
				echo "<script type='text/javascript'>alert('$message');location.href = 'loginUI.html';</script>";
			}else{
				$message = "此用戶名已經被註冊過了";
           		echo "<script type='text/javascript'>alert('$message');location.href = 'register.html';</script>";
			}
		}else{
			$message = "用戶名或密碼不能為空值";
            echo "<script type='text/javascript'>alert('$message');location.href = 'register.html';</script>";
		}
		break;
	case "loginCheck": //檢查登入(用戶名不能重複且不能為空)
		$_SESSION["userID"] = "";
		$username=$_POST['username']; //前端用post傳，Controller就用post接
		$pwd=$_POST['pwd']; //待辦事項內容
		if (($username != "")&($pwd != "")) { //白手套的概念，先確認id大於一，再將它導入函數
			if(loginCheck($username, $pwd)){
				header("Location: mainUI.html");
				break;
			}else{
				$message = "用戶名或密碼錯誤";
            	echo "<script type='text/javascript'>alert('$message');location.href = 'loginUI.html';</script>";
				break;
			}
		}else{
			$message = "用戶名或密碼不能為空值";
            echo "<script type='text/javascript'>alert('$message');location.href = 'loginUI.html';</script>";
			break;
		}
		header("Location: loginUI.html");
		break;
    case "getUserInfo": //拿到指定用的用戶名與金額
		$usr = $_SESSION["userID"];
        $list = getUserInfo($usr); // 從Model端得到未完成工作清單
        echo json_encode($list); //將陣列變成JSON字串傳回
        break;   
	case "getRankings": //傳回排序好的財富用戶清單
		$list = getRankings(); 
		echo json_encode($list); 
		break;   
	case "setRoom": // 增加一個莊家房間
		$usr=$_SESSION["userID"];
		$ans=$_POST['AnsNum']; //前端用post傳，Controller就用post接
	    if($ans){		
            /*if(addRoom($usr,$ans)){ //回傳開房的錢夠的話
				header("Location: waitToBet.html");
			}else{ //開房的錢不夠的話
				$message = "錢包最低限制300元才能開房，以自動儲值500元，請到附近超商繳費";
            	echo "<script type='text/javascript'>alert('$message');location.href = 'waitToBet.html';</script>";
			}*/
			addRoom($usr,$ans);
		}
		header("Location: waitToBet.html");
		break;
	case "getRoomInfo": //傳回房間資訊
		$usr = $_SESSION["userID"];
		$list = getRoomInfo($usr); 
		echo json_encode($list); //將陣列變成JSON字串傳回
		break;
	case "getRoomList": //傳回有開啟的房間資訊(status = 1)
		$list = getRoomList(); 
		echo json_encode($list); //將陣列變成JSON字串傳回
		break;
	case "setBet": //新增玩家押注的訊息
		$usr=$_SESSION["userID"];
		$ans=$_POST['AnsNum'];
		$rid=$_POST['roomID'];
		$Betmoney = $_POST['Betmoney'];
		if(checkRoomStatus($rid)){ //判斷房間是否關閉，已關閉
			$message = "該房間已經關閉，請重新加入其他房間";
			echo "<script type='text/javascript'>alert('$message');location.href='player.html';</script>";
		}else{//房間未關閉
			if(($ans != "")&($rid != "")&($Betmoney != "")){
				if(addBet($rid,$usr,$ans,$Betmoney)){
					header("Location: waitToReveal.html");
				}else{
					$message = "錢包金額不足";
					echo "<script type='text/javascript'>alert('$message');location.href = 'playerBet.html?roomid=".$rid."';</script>";
				}
			}else{
				$message = "輸入的值不能為空";
				echo "<script type='text/javascript'>alert('$message');location.href = 'playerBet.html?roomid=".$rid."';</script>";
			}
		}
		break;
	case "getBetInfo": //傳回該玩家此次押注的資訊
		$usr = $_SESSION["userID"];
		$list = getBetInfo($usr); 
		echo json_encode($list); //將陣列變成JSON字串傳回
		break;
	case "countBet": //傳回有幾個人參與該房間的賭注(二維陣列傳回)
		$rid = $_REQUEST['rid'];
		$list = countBet($rid); 
		echo json_encode($list); //將陣列變成JSON字串傳回
		break;
	case "revealResult": //當莊家按下開獎按鈕時
		$usr = $_SESSION["userID"]; //session取莊家的username
		$DealerRoomlist = getRoomInfo2($usr); //莊家的房間資訊(回傳一維陣列)
		$AllBetlist = getAllBetList($DealerRoomlist['rid']); //這間房間所有的賭注訊息(二維陣列)
		$resultArray = array(); //將結果表設一個二維陣列
		$tArray = array();//設一個一維陣列存裏頭的資訊
		$tArray['BeforeMoney'] = getMoney($usr); //回傳結算前的莊家錢包
		for($i = 0 ; $i < count($AllBetlist); $i++){//每個玩家要逐一和莊家比對
			if($DealerRoomlist['answerNum'] == $AllBetlist[$i]['guessNum']){ //點數相同，玩家贏錢
				PlayerWinUpdateMoney($usr, $AllBetlist[$i]['username'], $AllBetlist[$i]['betMoney']);
				//回傳一維陣列，裏頭欄位有，玩家的用戶名，莊家起始錢，莊家計算後的錢，。
			}else{ //點數不同，莊家贏錢
				DealerWinUpdateMoney($usr, $AllBetlist[$i]['username'], $AllBetlist[$i]['betMoney']);//回傳結果表
			}
		}	
		$tArray['AfterMoney'] = getMoney($usr); //回傳結算後的莊家錢包
		$resultArray[] = $tArray ;
		closeRoom($usr); //關掉莊家的房間的狀態
		echo json_encode($resultArray);
		break;
		
	case "getRevealResult": //回傳玩家開獎前後的錢
		$usr = $_SESSION["userID"]; //session取玩家的username
		$OriMoney = $_SESSION["OriginalMoney"];		
		$resultArray = array(); 
		$tArray = array();
		$tArray['BeforeMoney'] = $OriMoney;
		$tArray['AfterMoney'] = getMoney($usr); //取得玩家開獎計算後的錢
		$resultArray[] = $tArray ;
		DeleteThisBet($usr); //刪除這則押注訊息
		echo json_encode($resultArray);
		break;
	case "getOriginalMoney": //查出玩家開獎前的原始金額
		$usr = $_SESSION["userID"];
		$_SESSION["OriginalMoney"] = getMoney($usr);
        echo $_SESSION["OriginalMoney"];
		break;
	case "checkRoomstatus":
		$usr = $_SESSION["userID"]; //session取玩家的username
		$list = getBetInfo($usr);  //找到玩家的押注訊息(回傳二維陣列)
		$resultArray = array();
		$tArray = array();
		if($list != ""){//如果抓到Bet資訊
			$rid = $list[0]['roomNum']; //拿到房號(上方的二維陣列一定只有一筆資料，所以這裡可以寫死)
			if(checkRoomStatus($rid)){//return true表示房間關起來了(已經開獎了)
				$tArray['status'] = "close";
			}else{
				$tArray['status'] = "open";
			}
		}
		$resultArray[] = $tArray;
		echo json_encode($resultArray);
		break;
	case "checkBetisNull":
		$usr = $_SESSION["userID"];
		$resultArray = array();
		$tArray = array();
		if(checkBetisNull($usr)){
			$tArray['isNull'] = "havenoValue";
		}
		$resultArray[] = $tArray;
		echo json_encode($resultArray);
		break;
	default:
}
?>

