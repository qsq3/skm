<?php
if(!defined('IN_DISCUZ')) {
        exit('Access Denied');
} 
//����URL
$url="http://bt.sicau.me/search.php?mod=forum";
//POST�ύ���ݣ�����HTTPWATCH�鿴
if(isset($_GET['kw'])) {
	$kw=urldecode($_GET['kw']);
}else {
	Header("Location:search.php");
}
$postfield="srchtxt={$kw}";
//���������
$proxy = '';
//����
$str=curlrequest($url,$postfield,$proxy);
//������
echo $str;


function curlrequest($url,$postfield,$proxy=""){
	$proxy=trim($proxy);
	$user_agent ="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
	$ch = curl_init(); // ��ʼ��CURL���
	if(!empty($proxy)){
		curl_setopt ($ch, CURLOPT_PROXY, $proxy);//���ô��������
	}
	curl_setopt($ch, CURLOPT_URL, $url); //���������URL
	//curl_setopt($ch, CURLOPT_FAILONERROR, 1); // ����ʱ��ʾHTTP״̬�룬Ĭ����Ϊ�Ǻ��Ա��С�ڵ���400��HTTP��Ϣ
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);//����ʱ�Ὣ���������������صġ�Location:������header�еݹ�ķ��ظ�������
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);// ��ΪTRUE��curl_exec()���ת��Ϊ�ִ���������ֱ�����
	curl_setopt($ch, CURLOPT_POST, 1);//����POST�ύ
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield); //����POST�ύ���ַ���
	//curl_setopt($ch, CURLOPT_PORT, 80); //���ö˿�
	curl_setopt($ch, CURLOPT_TIMEOUT, 25); // ��ʱʱ��
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);//HTTP����User-Agent:ͷ
	//curl_setopt($ch,CURLOPT_HEADER,1);//��ΪTRUE������а���ͷ��Ϣ
	//$fp = fopen("example_homepage.txt", "w");//����ļ�
	//curl_setopt($ch, CURLOPT_FILE, $fp);//��������ļ���λ�ã�ֵ��һ����Դ���ͣ�Ĭ��ΪSTDOUT (�����)��
	curl_setopt($ch,CURLOPT_HTTPHEADER,array(
	'Accept-Language: zh-cn',
	'Connection: Keep-Alive',
	'Cache-Control: no-cache'
	));//����HTTPͷ��Ϣ
	$document = curl_exec($ch); //ִ��Ԥ�����CURL
	$info=curl_getinfo($ch); //�õ�������Ϣ������
	//print_r($info);
	if($info['http_code']=="405"){
		echo "bad proxy {$proxy}\n"; //�������
		exit;
	}
	//curl_close($ch);
	return $document;
}

?>
