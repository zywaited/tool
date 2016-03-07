<?php

class Http{
	protected $fh = null;    //与HTTP建立连接后的资源
	protected $line = array();	 //请求信息
	protected $header = array(); //头部信息
	protected $body = '';  //主体信息
	protected $url = array();   //URL信息
	protected $respond = '';    //请求后返回的信息

	public function __construct($url){
		$this->setUrl($url);
		$this->connect();
		$this->setHeader('Host: '.$this->url['host']);
	}

	//GET请求
	public function get(){
		$this->setLine('GET');
		$this->request();
	}

	/*
		POST请求
		$body 数组，主体信息
	*/
	public function post($body=array()){
		$this->setLine('POST');
		$this->setBody($body);
		$header = Array(
				'Content-type: application/x-www-form-urlencoded',
				'Content-length: '.strlen($this->body)
			);
		$this->setHeader($header);
		return $this->request();
	}

	/*
		$body 数组，主体信息,返回拼接好的URL后缀
	*/
	protected function setBody($body){
		$this->body = http_build_query($body);
	}

	/*
		设置头部信息
		$header 索引数组
	*/
	public function setHeader($header){
		if(is_array($header)){
			foreach ($header as $value) {
				$this->header[] = $value;
			}
		}else{
			$this->header[] = $header;
		}
	}

	//分析URL
	public function setUrl($url){
		$this->url = parse_url($url);
	}

	//设置请求行
	public function setLine($line){
		$this->line[] = $line.' '.$this->url['path'].' HTTP/1.1';
	}

	//连接http
	protected function connect(){
		if(!isset($this->url['port'])){
			$port = 80;
		}else{
			$port = $this->url['port'];
		}

		$this->fh = fsockopen($this->url['host'],$port);
	}

	//发送请求
	protected function request(){
		$arr = array_merge($this->line,$this->header,array(''),array($this->body),array(''));
		//print_r($arr);
		$result = implode("\r\n", $arr);
		//echo $result;return;

		fwrite($this->fh, $result);

		while (!feof($this->fh)) {
			$this->respond .= fread($this->fh, 1024);
		}

		$this->close();

		return $this->respond;
	}

	//关闭连接
	protected function close(){
		fclose($this->fh);
	}
}
