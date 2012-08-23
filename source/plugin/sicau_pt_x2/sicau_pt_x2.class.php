<?php

/**
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: sicau_pt_x2.class.php 1 2011-9-25 14:39:00Z shenmao1989 $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Plugin_Sicau_Pt {
	function __construct() {
	}
	//test
	function test(){
		global $_G;
		echo $_G['cache']['plugin']['sicau_pt_x2']['test'];
		echo('test ok');
	}
	//�ж��û��Ƿ����
	//@param id
	//@return true or false
	function xbt_userexist($id){
		$id=intval($id);
		if(!intval($id))
			return false;
		$sql="SELECT id FROM ".DB::table('xbtit_users')." WHERE id='$id'";
		$query = DB::query($sql);
		if(count($query))
			return true;
		return false;
	}
	//��ȡ�û���Ϣ
	//@param id
	//@return array() or false
	function xbt_userinfo($id,$param='*'){
		$id=intval($id);
		if(!intval($id))
			return false;
		$sql="SELECT $param FROM ".DB::table('xbtit_users')." WHERE id='$id'";
		$query = DB::query($sql);
		if(count($query))
			return $query[0];
		return false;
	}
	//����û�
	//@param id,name,pass,type
	//@return true or false
	function xbt_useradd($id,$name,$pass='6501qq',$type=3){
		$id=intval($id);
		$type=intval($type);
		
		if(!intval($id) || !$name)
			return false;
		if(xbt_userexist($id))
			return false;
		
		$sql="INSERT INTO ".DB::table('xbtit_users')." (id,username, password, random, id_level, email, style, language, flag, joined, lastconnect, pid, time_offset) VALUES ($id,'$name', '" . md5($pass) . "', ".rand(10000, 60000).", $type, '$email', 1, 19, 0, NOW(), NOW(),'".md5(uniqid(rand(),true))."', '8')";
		if(DB::query($sql))
			return true;
		return false;
	}
	//ɾ���û�
	//@param id
	//@return true or false
	function xbt_userdel($id){
		$id=intval($id);
		
		if(!intval($id))
			return false;
		if(!xbt_userexist($id))
			return false;
		$sql="DELETE FROM ".DB::table('xbtit_users')." WHERE id=$id";
		if(DB::query($sql))
			return true;
		return false;
	}
	//�༭�û�
	//@param id,name,pass,type
	//@return true or false
	//�ݲ�ʵ�֣�
	function xbt_useredit($id,$name,$pass,$type){
		$id=intval($id);
		$type=intval($type);
		
		if(!userexist($id))
			return false;
			
		return true;
	}
	//������ӣ��˴�Ϊfiles�������ֶ�aid��atype�ֶΣ����ڶ�Ӧ����aid�Լ���Ӧ���ӵ��������ͣ�
	//@param �������飨��discuz�ϴ��ļ�������
	//@return 0,1,2,3
	function xbt_torradd($attach){
		global $_G;
		//fb("����addTorrent()��",FirePHP::INFO);
		require ("BDecode.php");
		require ("BEncode.php");
		$torrentURL=$_G['attachdir'].'/'.$attach['attachment'];
		$torrentName=$attach['filename'];
		$torrentAid=$attach['aid'];
		//fb("��ȡtorrent�ļ���".$torrentAid,FirePHP::INFO);
		//fb("��ȡtorrent�ļ���".$torrentURL,FirePHP::INFO);
		clearstatcache();
		$fd = fopen($torrentURL, "rb");
		$length=filesize($torrentURL);
		//fb("��ȡtorrent�ļ���".$length.'Bytes',FirePHP::INFO);
		//��ȡ�ļ���$alltorrent
		if ($length)
			$alltorrent = fread($fd, $length);
		else {
			return -1;
			//fb("��ȡtorrent�ļ�����",FirePHP::ERROR);
		
		}
		//��BDecode�⿪torrent�ļ�����ȡ�ļ���Ϣ��$array
		$array = BDecode($alltorrent);
		if (!isset($array))
		{
			return -2;
			//BD�������
		}
		
		//fb("torrent announce��Ϣ��".$array["announce"],FirePHP::INFO);
		
		//��������Ϊ˽��
		$array["info"]["private"]=1;
		
		//���������´����torrent����sha1ֵ
		$hash=sha1(BEncode($array["info"]));
		
		//�����Ƿ����
		if(xbt_torrexist($hash,$type='hash')){
			//errorlog('XBT',"(xbt_torradd)�����Ѵ��ڣ�hash:".$hash." ".$type, 0);
			return -3;
			//�����Ѵ���
		}
		//fb("torrent announce��Ϣ��".$hash,FirePHP::INFO);
		fclose($fd);
		
		   $filename = $torrentName;
		//����torrent�ļ��洢λ��
			$url = $torrentURL;
		//fb("torrent announce��Ϣ��".$url,FirePHP::INFO);
		
		// filename not writen by user, we get info directly from torrent.
		if (strlen($filename) == 0 && isset($array["info"]["name"]))
		   $filename = mysql_escape_string(htmlspecialchars($array["info"]["name"]));
		
		// description not writen by user, we get info directly from torrent.
		if (isset($array["comment"]))
		   $info = mysql_escape_string(htmlspecialchars($array["comment"]));
		else
			$info = "no info";
		
		//fb("torrent announce��Ϣ��".$filename,FirePHP::INFO);
		
		if (isset($array["info"]) && $array["info"]) $upfile=$array["info"];
			else $upfile = 0;
		
		//���ļ������뵥�ļ����Ӵ����ȡ�ļ���С
		if (isset($upfile["length"]))
		{
		  $size = (float)($upfile["length"]);
		}
		else if (isset($upfile["files"]))
		{
			// multifiles torrent
			$size=0;
			foreach ($upfile["files"] as $file)
			{
				$size+=(float)($file["length"]);
			}
		}
		else
			$size = "0";
		
		if (!isset($array["announce"]))
			{
			//err_msg($language["ERROR"], $language["EMPTY_ANNOUNCE"]);
			//stdfoot();
			//return 'EMPTY_ANNOUNCE';
		}
		
		$categoria = intval(8);//ȡ�����࣬ͳһ����Ϊ8
		$anonyme=false;
		//fb("$discuz_uid��".$discuz_uid,FirePHP::INFO);
		$curuid=intval($attach['uid']);
		
		$announce=str_replace(array("\r\n","\r","\n"),"",$array["announce"]);
		
		//      if ((strlen($hash) != 40) || !verifyHash($hash))
		//      {
		//         echo("<center><font color=\"red\">".$language["ERR_HASH"]."</font></center>");
		//         endOutput();
		//      }
		//      if ($announce!=$BASEURL."/announce.php" && $EXTERNAL_TORRENTS==false)
		//�ж�announce�Ƿ�Ϸ�
		//if (!in_array($announce,$TRACKER_ANNOUNCEURLS) && $EXTERNAL_TORRENTS==false)
		//{
		//	//return "NOT_ALOWED_ANNOUNCE";
		//}
		//      if ($announce!=$BASEURL."/announce.php")
			
		// maybe we find our announce in announce list??
		 $internal=false;
		 if (isset($array["announce-list"]) && is_array($array["announce-list"]))
			{
			for ($i=0;$i<count($array["announce-list"]);$i++)
				{
				if (in_array($array["announce-list"][$i][0],$TRACKER_ANNOUNCEURLS))
				  {
				   $internal = true;
				   continue;
				  }
				}
			}
		  //����announce�����ж�
		  $internal = true;
		  //fb("torrent ��ʼ���룡".$hash.",".$filename.",".$url.",".$info.",".$size.",".$comment.",".$announce.",".$curuid.",".$hash.",",FirePHP::INFO);
		  if ($internal)
			{
			// ok, we found our announce, so it's internal and we will set our announce as main
			   $array["announce"]=$TRACKER_ANNOUNCEURLS[0];
			   $query = "INSERT INTO ".DB::table('xbtit_files')." (info_hash, filename, url, info, category, data, size, comment, uploader, bin_hash,aid,atype,lastactive) VALUES (\"$hash\", \"$filename\", \"$url\", \"$info\",0 + $categoria,NOW(), \"$size\", \"$comment\",$curuid,0x$hash, $torrentAid,0,UNIX_TIMESTAMP())";
			}
		  else
			  $query = "INSERT INTO ".DB::table('xbtit_files')." (info_hash, filename, url, info, category, data, size, comment,external,announce_url, uploader,anonymous, bin_hash,aid,atype,lastactive) VALUES (\"$hash\", \"$filename\", \"$url\", \"$info\",0 + $categoria,NOW(), \"$size\", \"$comment\",\"yes\",\"$announce\",$curuid,$anonyme,0x$hash, $torrentAid,0,UNIX_TIMESTAMP())";
		  //echo $query;
		  
		//fb("torrent ��ʼ���룡".$query,FirePHP::INFO);
		  $db->query($query); //makeTorrent($hash, true);
		return 0;
	}

	//ɾ������,�˴���ɾ���ļ����ļ���discuz����
	//@param $aid
	//@return true or false
	function xbt_torrdel($id){
		$id=intval($id);
		//errorlog('XBT',"(xbt_torrdel)ɾ�����ӣ�aid:".$id, 0);
		if(!intval($id))
			return false;
		if(!xbt_torrexist($id))
			return false;
		$sql="DELETE FROM ".DB::table('xbtit_files')." WHERE aid=$id";
		if($db->query($sql))
			return true;
		return false;
	}
	//�����Ƿ����
	//@param $id 
	//@param $type aid ���� hash��ѯ Ĭ��aid
	//@return true or false
	function xbt_torrexist($id,$type=''){
		if($type=='hash')
			$sql="SELECT * FROM ".DB::table('xbtit_files')." WHERE info_hash='$id'";
		else
			$sql="SELECT * FROM ".DB::table('xbtit_files')." WHERE aid='$id'";
		//errorlog('XBT',"(xbt_torrexist)������Ӵ��ڣ�hash:".$id." ".$type, 0);
		$query = DB::query($sql);
		if(count($query))
			return true;
		return false;
	}
	//�༭����
	//@param ����
	//@return true or false
	//�ݲ�ʵ��
	function xbt_torredit(){
			
		return true;
	}
	//��ȡtracker
	//@param id
	//@return string or false
	function xbt_gettracker($id){
		$t=xbt_userinfo($id,'pid');
		return  $t['pid'];
	}
	//ˢ��tracker
	//@param id
	//@return string or false
	function xbt_torreflush($id){
		$id=intval($id);
		
		if(!$id)
			return false;
		$newt=md5(uniqid(rand(),true));	
		$sql="UPDATE ".DB::table('xbtit_users')." SET pid='$newt' WHERE id=$id";
		if($db->query($sql))
			return $newt;
		return false;
	}
	//torrent�ļ����飬��ȡ�ļ�����
	//@param $aid
	//@return array() or false
	function xbt_torrfileinfo($id){
		$sql="SELECT url FROM ".DB::table('xbtit_files')." WHERE aid='$id'";
		$query=DB::query($sql);
		$torrenturl=$query[0]['url'];
		if(!$torrenturl)
			return false;
		clearstatcache();
		require ("BDecode.php");
		require ("BEncode.php");
		$fd = fopen($torrentURL, "rb");
		$length=filesize($torrentURL);
		//fb("��ȡtorrent�ļ���".$length.'Bytes',FirePHP::INFO);
		//��ȡ�ļ���$alltorrent
		if ($length)
			$alltorrent = fread($fd, $length);
		else {
			return false;
			//fb("��ȡtorrent�ļ�����",FirePHP::ERROR);
		}
		//��BDecode�⿪torrent�ļ�����ȡ�ļ���Ϣ��$array
		return BDecode($alltorrent);
	}
	//torrent�ļ����飬���ݿ����
	//@param $id
	//@return array() or false
	function xbt_torrinfo($id,$type='',$param='*'){
		if($type=='hash')
			$sql="SELECT $param FROM ".DB::table('xbtit_files')." WHERE info_hash='$id'";
		else{
			$id=intval($id);
			if(!intval($id))
				return false;
			$sql="SELECT $param FROM ".DB::table('xbtit_files')." WHERE aid='$id'";
		}
		$query = DB::query($sql);
		if(count($query))
			return $query[0];
		return false;
	}
	//ĳһ���ӵ�peers��Ϣ
	//@param $id
	//@return array() or false
	function xbt_peersinfo($id,$param='*'){
		$id=intval($id);
		if(!intval($id))
			return false;
		$sql="SELECT info_hash FROM ".DB::table('xbtit_files')." WHERE aid='$id'";
		$query=DB::query($sql);
		$infohash=$query[0]['info_hash'];
		//$infohash=$db->result_first($sql);

		$sql="SELECT $param FROM ".DB::table('xbtit_peers')." WHERE infohash='$infohash'";
		$query = DB::query($sql);
		if(count($query))
			return $query[0];
		return false;
	}
	//������������
	//@param $id
	//@return true or false
	function xbt_settorrtype($id,$type=0){
		$id=intval($id);
		$type=$type;
		$sql="UPDATE ".DB::table('xbtit_files')." SET atype=$type WHERE aid=$id";
		return DB::query($sql);
	}
	//��ȡ��������
	//@param $id
	//@return true or false
	function xbt_gettorrtype($id,$type=''){
	$id=intval($id);
	if($type=='hash')
		$sql="SELECT atype FROM ".DB::table('xbtit_files')." WHERE info_hash='$id'";
	else
		$sql="SELECT atype FROM ".DB::table('xbtit_files')." WHERE aid='$id'";
	$query=DB::query($sql);
	return $query[0]['atype'];
	}
	//��ת����������ҳ��
	//@param ����
	//@return
	function xbt_gettorrdown(){
	}
	
}

/*class plugin_cloudstat_forum extends plugin_sicau_pt {


}*/

?>