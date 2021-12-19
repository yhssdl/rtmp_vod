<?php 
if(!defined('sea_INC'))
{
	exit("Request Error!");
} 
session_start();
function CheckPurview()
{
	if($GLOBALS['cuserLogin']->getUserRank()<>1)
	{
		ShowMsg("对不起，你没有权限执行此操作！<br/><br/><a href='javascript:history.go(-1);'>点击此返回上一页&gt;&gt;</a>",'javascript:;');
		exit();
	}
}

function CheckEditorPurview()
{
	if($GLOBALS['cuserLogin']->getUserRank()>2)
	{
		ShowMsg("对不起，你没有权限执行此操作！<br/><br/><a href='javascript:history.go(-1);'>点击此返回上一页&gt;&gt;</a>",'javascript:;');
		exit();
	}
}


$admincachefile = sea_DATA.'/admin_'.cn_substr(md5($cfg_cookie_encode),24).'.php';
if(!file_exists($admincachefile))
{
	$fp = fopen($admincachefile,'w');
	fwrite($fp,'<'.'?php $admin_path ='." ''; ?".'>');
	fclose($fp);
}
require_once($admincachefile);

class userLogin
{
	var $userName = '';
	var $nickName = '';
	var $userPwd = '';
	var $userID = '';
	var $userVodList = '';
	var $adminDir = '';
	var $groupid = '';
	var $publish = '';
	var $tid = '';
	var $keepUserIDTag = "sea_admin_id";
	var $keepgroupidTag = "sea_group_id";
	var $keepUserNameTag = "sea_admin_name";
	var $keepNickNameTag = "sea_nick_name";
	var $keeVodListTag = "sea_vod_list";
	var $keePublishTag = "sea_publish_name";
	var $keeTidTag = "sea_tid_name";
	//php5构造函数
	function __construct($admindir='')
	{
		global $admin_path;
		if(isset($_SESSION[$this->keepUserIDTag]))
		{
			$this->userID = $_SESSION[$this->keepUserIDTag];
			$this->groupid = $_SESSION[$this->keepgroupidTag];
			$this->userName = $_SESSION[$this->keepUserNameTag];
			$this->nickName = $_SESSION[$this->keepNickNameTag];	
			$this->userVodList = $_SESSION[$this->keeVodListTag];
			$this->publish = $_SESSION[$this->keePublishTag];	
			$this->tid = $_SESSION[$this->keeTidTag];
		}

		if($admindir!='')
		{
			$this->adminDir = $admindir;
		}
		else
		{
			$this->adminDir = $admin_path;
		}
	}

	function userLogin($admindir='')
	{
		$this->__construct($admindir);
	}

	//检验用户是否正确
	function checkUser($username,$userpwd)
	{
		global $dsql;

		//只允许用户名和密码用0-9,a-z,A-Z,'@','_','.','-'这些字符
		$this->userName = m_ereg_replace("[^0-9a-zA-Z_@!\.-]",'',$username);
		$this->userPwd = m_ereg_replace("[^0-9a-zA-Z_@&*#$%^()+=~`!\.-]",'',$userpwd);
		$pwd = substr(md5($this->userPwd),5,20);
		$dsql->SetQuery("Select * From `sea_admin` where name like '".$this->userName."' and state='1' limit 0,1");
		$dsql->Execute();
		$row = $dsql->GetObject();
		if(!isset($row->password))
		{
			return -1;
		}
		else if($pwd!=$row->password)
		{
			return -2;
		}
		else
		{
			$loginip = GetIP();
			$this->userID = $row->id;
			$this->groupid = $row->groupid;
			$this->userName = $row->name;
			$this->userVodList = $row->vod_list;
			$this->nickName = $row->nickname;
			$this->publish = $row->publish;
			$this->tid = $row->tid;
			$inquery = "update `sea_admin` set loginip='$loginip',logintime='".time()."' where id='".$row->id."'";
			$dsql->ExecuteNoneQuery($inquery);
			return 1;
		}
	}

	//保持用户的会话状态
	//成功返回 1 ，失败返回 -1
	function keepUser()
	{
		if($this->userID!=""&&$this->groupid!="")
		{
			global $admincachefile;

			$_SESSION[$this->keepUserIDTag] = $this->userID;
			$_SESSION[$this->keepgroupidTag] = $this->groupid;
			$_SESSION[$this->keepUserNameTag] = $this->userName;
			$_SESSION[$this->keepNickNameTag] = $this->nickName;			
			$_SESSION[$this->keeVodListTag] = $this->userVodList;
			$_SESSION[$this->keePublishTag] = $this->publish;			
			$_SESSION[$this->keeTidTag] = $this->tid;			

			$fp = fopen($admincachefile,'w');
			fwrite($fp,'<'.'?php $admin_path ='." '{$this->adminDir}'; ?".'>');
			fclose($fp);
			return 1;
		}
		else
		{
			return -1;
		}
	}

	//结束用户的会话状态
	function exitUser()
	{
		$_SESSION[$this->keepUserIDTag] = '';
		$_SESSION[$this->keepgroupidTag] = '';
		$_SESSION[$this->keepUserNameTag] = '';
		$_SESSION[$this->keepNickNameTag] = '';		
		$_SESSION[$this->keeVodListTag] = '';	
		$_SESSION[$this->keePublishTag] = '';		
		$_SESSION[$this->keeTidTag] = '';			
		
	}


	//获得用户的权限值
	function getgroupid()
	{
		if($this->groupid!='')
		{
			return $this->groupid;
		}
		else
		{
			return -1;
		}
	}

	//获得用户的发布权限
	function getpublish()
	{
		if($this->publish!='')
		{
			return $this->publish;
		}
		else
		{
			return 0;
		}
	}

	//获得用户的发布默认分类
	function gettypeid()
	{
		if($this->tid!='')
		{
			return $this->tid;
		}
		else
		{
			return 0;
		}
	}

	

	function getUserRank()
	{
		return $this->getgroupid();
	}

	//获得用户的ID
	function getUserID()
	{
		if($this->userID!='')
		{
			return $this->userID;
		}
		else
		{
			return -1;
		}
	}

	//获得用户名
	function getUserName()
	{
		if($this->userName!='')
		{
			return $this->userName;
		}
		else
		{
			return "";
		}
	}

	//获得昵称
	function getNickName()
	{
		if($this->nickName!='')
		{
			return $this->nickName;
		}
		else
		{
			return "";
		}
	}	

		//设置新的昵称
		function setNickName($nickname)
		{
			$this->nickName = $nickname;
			$_SESSION[$this->keepNickNameTag] = $this->nickName;
		}	

	//获得用户可操作VOD列表
	function getUserVodList()
	{
		if($this->userVodList!='')
		{
			return $this->userVodList;
		}
		else
		{
			return 0;
		}
	}

}
?>