<!DOCTYPE html>
<?php
include 'includes/config/_config.php';

class IamWeb
{
	function Windows_Auth($params, $token)
	{
		$url = BASE_AUTH_URL . "/Windows/Auth";

		$headers  = [
			'Authorization: Bearer ' . $token,
			'Content-Type: application/json'
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		$output = curl_exec($ch);

		if ($output === false) {
			return 'Curl error: ' . curl_error($ch);
		} else {

			return $output;
		}
		curl_close($ch);
	}

	function Get_Bearer()
	{
		$params = array(
			"grant_type" => "client_credentials",
			"scope"      => "Token_WindowsAuth"
		);

		$postData = '';
		foreach ($params as $k => $v) {
			$postData .= $k . '=' . $v . '&';
		}
		$postData = rtrim($postData, '&');

		$url = BASE_AUTH_URL . "/token";

		$headers  = [
			'Content-Type: application/x-www-form-urlencoded'
		];
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, username . ":" . password);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		$output = curl_exec($ch);

		if ($output === false) {
			return 'Curl error: ' . curl_error($ch);
		} else {

			return $output;
		}
		curl_close($ch);
	}
}

function notifyManager($mgrEmail, $user_email)
{
	$to = $user_email;
	$subject = "IMET Access";

	$message = "
<html>
<head>
<title>IMET Access</title>
</head>
<body>
<p>Auto access has been granted for IMET (<a href=\"http://goto/imet\" target=\"_blank\">goto/IMET</a>)</p>
<p>Thanks<br>IMET Team</p>
</body>
</html>
";

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

	// More headers
	$headers .= 'From: HSPE IMET <hspe.imet@intel.com>' . "\r\n";
	$headers .= 'Cc: hspe.imet@intel.com' . "\r\n";
	$headers .= 'Cc: ' . $mgrEmail . "\r\n";

	mail($to, $subject, $message, $headers);
}
function is_bb($idsid)
{
	require_once "functions/general.php";
	$user = cmdUserData($idsid);
	$user_wwid = $user["WWID"];
	$idsid = $user["IDSID"];
	$user_name = $user["ccMailName"];
	$user_email = $user["DomainAddress"];
	$mgrWWID = $user["MgrWWID"];
	$emptype = trim($user["Emptype"]);
	//SH = intern
	if(($emptype == 'REG' || $emptype == 'SH') && ($user_wwid != '' && $user_name != '' && $user_email != '')){
		$mgr = cmdUserData($mgrWWID);
		$mgrEmail = $mgr["DomainAddress"];
		$mgrName = $mgr["ccMailName"];
		$db = new Db();
		$justif = "New user";
		$created_on = date('Y-m-d H:i:s');
		$q_inert = "INSERT INTO `subscribers`(`WWID`, `user_idsid`, `email`, `cc_mail_name`, `emp_type`,`manager_email`,`manager_name`,`justification`,`created_on`) VALUES('$user_wwid', '$idsid', '$user_email','$user_name','$emptype','$mgrEmail','$mgrName','$justif','$created_on')";
		$inerted = $db->query($q_inert);
		/* $q_inert = "INSERT INTO `users_all`(`WWID`, `user_idsid`, `email`, `cc_mail_name`, `justification`,`added_by`) VALUES('$user_wwid', '$idsid', '$user_email','$user_name','$justif','system')";
		$inerted = $db->query($q_inert);
		notifyManager($mgrEmail,$user_email); */
		return true;
	}
	return false;
}

function insertNewSubscribers($idsid)
{
	require_once "functions/general.php";
	$user = cmdUserData($idsid);
	$user_wwid = $user["WWID"];
	$idsid = $user["IDSID"];
	$user_name = $user["ccMailName"];
	$user_email = $user["DomainAddress"];
	$mgrWWID = $user["MgrWWID"];
	$emptype = trim($user["Emptype"]);
	//SH = intern
	if ($user_wwid != '' && $user_name != '' && $user_email != '') {
		$mgr = cmdUserData($mgrWWID);
		$mgrEmail = $mgr["DomainAddress"];
		$mgrName = $mgr["ccMailName"];
		$db = new Db();
		$justif = "New user";
		$created_on = date('Y-m-d H:i:s');
		$q_inert = "INSERT INTO `subscribers`(`WWID`, `user_idsid`, `email`, `cc_mail_name`, `emp_type`,`manager_email`,`manager_name`,`justification`,`created_on`) VALUES('$user_wwid', '$idsid', '$user_email','$user_name','$emptype','$mgrEmail','$mgrName','$justif','$created_on')";
		$inerted = $db->query($q_inert);
		/* $q_inert = "INSERT INTO `users_all`(`WWID`, `user_idsid`, `email`, `cc_mail_name`, `justification`,`added_by`) VALUES('$user_wwid', '$idsid', '$user_email','$user_name','$justif','system')";
		$inerted = $db->query($q_inert);
		notifyManager($mgrEmail,$user_email); */
		return true;
	}
	return false;
}
function isJson($string)
{
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}
function setLogin($idsid)
{
	$redirect_url = isset($_GET['redirect']) && !empty($_GET['redirect']) ? $_GET['redirect'] : '';
	$db = new Db();
	$sql = "SELECT user_idsid FROM `users_all` WHERE `user_idsid`='$idsid' AND `status` = 'active'";
	$readuser	=  $db->select($sql);
	$pass = 0;
	$id = "";
	$msg = "";

	if ($readuser[0]['user_idsid'] === $idsid) {
		$pass	=	1;
		$id 	=	$idsid;
	} else {
		/* if(!insertNewSubscribers($idsid)){
			require_once "templates/home.php";
			exit();
		}else{
			$pass	=	1;
			$id 	=	$idsid; //BB-VALID cookie id
		} */
		setcookie('IDSID', $idsid,time() + (86400 * 30), "/");
		require_once "templates/home.php";
		exit();
		/* if(!is_bb($idsid)){
			require_once "templates/401.php";
			exit();
		}else{
			$pass	=	1;
			$id 	=	$idsid; //BB-VALID cookie id
		} */
	}
	if ($pass == 1) {
		setcookie('IDSID', $id,time() + (86400 * 30), "/");
		$_SESSION['id'] = $id;
		if (!empty($redirect_url)) {
			$redirect_url1 = urldecode($redirect_url);

			header("location:$redirect_url1");
		} else {

			header("location:imet_home.php");
		}
	}
}

$redirect_url = isset($_GET['redirect']) && !empty($_GET['redirect']) ? $_GET['redirect'] : '';
if (isset($_SESSION['id'])) {
	if ($_SESSION['id'] != "") {
		if (!empty($redirect_url)) {
			$redirect_url1 = urldecode($redirect_url);
			header("location:$redirect_url1");
		} else {
			header("location:imet_home.php");
		}
	}
} else {
	//validate cookie idsid with circuit
	$msg = "";
	$token = $_GET['token'];

	if (isset($token)) {
		$params = array(
			"token" => $token,
		);

		$auth = new IamWeb();
		$result = $auth->Get_Bearer();
		if (isJson($result)) {
			$access_token = json_decode($result)->{'access_token'};
			$res = json_decode($auth->Windows_Auth($params, $access_token));
			$res = json_decode(json_encode($res), true);

			if (!empty($res)) {
				$res = $res['IntelUserExtension'];
				$wwid = $res['id'];
				$idsid = $res['externalId'];
				setLogin($idsid);
			} else {
				echo "Error in Authentication!";
			}
		} else {
			print_r($result);
		}
	} else {
		$redirect_url = $_GET['redirect'];
		$iamws = "https://iamws-i.intel.com/api/v1/Windows/Auth?redirectUrl=";
		$to = DIR_WS_SITEURL . "login.php?redirect=" . $redirect_url;
		$url =  $to;
		$iamws_redirect_url = $iamws . $url;
		header('Location: ' . $iamws_redirect_url);
	}
}


?>

<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Login</title>
</head>
<body>
	<span style='color:#0071c5;font-size:20px;'>Authentication in progress, please wait...</span>
</body>

</html>
