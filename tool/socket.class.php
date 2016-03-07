<?php

error_reporting(E_ALL);
ob_implicit_flush();

class Sock{
    public $sockets;
    public $users;
    public $master;
     
    public function __construct($address, $port){
        $this->master=$this->WebSocket($address, $port);
        $this->sockets=array('s'=>$this->master);
    }
     
     
    function run(){
        while(true){
            $changes=$this->sockets;
            $write = NULL;
            $except = NULL;
            socket_select($changes,$write,$except,NULL);
            foreach($changes as $sock){
                if($sock==$this->master){
                    $client=socket_accept($this->master);
                    $this->sockets[]=$client;
                    $this->users[]=array(
                        'socket'=>$client,
                        'shou'=>false
                    );
                }else{
                    $len=socket_recv($sock,$buffer,2048,0);

                    $k=$this->search($sock);
                    if($len<7){
                        $name=$this->users[$k]['ming'];
                        $this->close($sock);
                        $this->send2($name,$k);
                        continue;
                    }
                    if(!$this->users[$k]['shou']){
                        $this->woshou($k,$buffer);
                    }else{
                        $buffer = $this->uncode($buffer);
                        $this->send($k,$buffer);
                    }
                }
            }
             
        }
         
    }
     
    function close($sock){
        $k=array_search($sock, $this->sockets);
        socket_close($sock);
        unset($this->sockets[$k]);
        unset($this->users[$k]);
        $this->e("key:$k close");
    }
     
    function search($sock){
        foreach ($this->users as $k=>$v){
            if($sock==$v['socket'])
            return $k;
        }
        return false;
    }
     
    function WebSocket($address,$port){
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($server, $address, $port);
        socket_listen($server);
        $this->e('Server Started : '.date('Y-m-d H:i:s'));
        $this->e('Listening on   : '.$address.' port '.$port);
        return $server;
    }
     
     
    function woshou($k,$buffer){
        $buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
        $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
     
        $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
         
        $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $new_message .= "Upgrade: websocket\r\n";
        $new_message .= "Sec-WebSocket-Version: 13\r\n";
        $new_message .= "Connection: Upgrade\r\n";
        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
         
        socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
        $this->users[$k]['shou']=true;
        return true;
         
    }
     
    function uncode($str){
        $mask = array();  
        $data = '';  
        $msg = unpack('H*',$str);  
        $head = substr($msg[1],0,2);  
        if (hexdec($head{1}) === 8) {  
            $data = false;  
        }else if (hexdec($head{1}) === 1){  
            $mask[] = hexdec(substr($msg[1],4,2));  
            $mask[] = hexdec(substr($msg[1],6,2));  
            $mask[] = hexdec(substr($msg[1],8,2));  
            $mask[] = hexdec(substr($msg[1],10,2));  
           
            $s = 12;  
            $e = strlen($msg[1])-2;  
            $n = 0;  
            for ($i=$s; $i<= $e; $i+= 2) {  
                $data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));  
                $n++;  
            }  
        }  
        return $data;
    }
     
     
    function code($msg){
        $msg = preg_replace(array('/\r$/','/\n$/','/\r\n$/',), '', $msg);
        $frame = array();  
        $frame[0] = '81';  
        $len = strlen($msg);  
        $frame[1] = $len<16?'0'.dechex($len):dechex($len);  
        $frame[2] = $this->ord_hex($msg);  
        $data = implode('',$frame);  
        return pack("H*", $data);  
    }
     
    function ord_hex($data)  {  
        $msg = '';  
        $l = strlen($data);  
        for ($i= 0; $i<$l; $i++) {  
            $msg .= dechex(ord($data{$i}));  
        }  
        return $msg;  
    }
     
    function send($k,$msg){
        parse_str($msg,$g);
        $this->e($msg);
        $ar=array();
        if($g['type']=='add'){
            $this->users[$k]['ming']=$g['ming'];
            $ar['add']=true;
            $ar['nrong']='欢迎'.$g['ming'].'加入！';
            $ar['users']=$this->getusers();
            $key='all';
        }else if($g['type']=='ltiao'){
            $ar['nrong']=$g['nr'];
            $key=$g['key'];
        }
        $msg=json_encode($ar);
        $this->e($msg);
        $msg = $this->code($msg);
        $this->send1($k,$msg,$key);
    }
     
    function getusers(){
        $ar=array();
        foreach($this->users as $k=>$v){
            $ar[$k]=$v['ming'];
        }
        return $ar;
    }
     
    function send1($k,$str,$key='all'){
        if($key=='all'){
            foreach($this->users as $v){
                socket_write($v['socket'],$str,strlen($str));
            }
        }else{
            if($k!=$key)
            socket_write($this->users[$k]['socket'],$str,strlen($str));
            socket_write($this->users[$key]['socket'],$str,strlen($str));
        }
    }
     
    function send2($ming,$k){
        $ar['remove']=true;
        $ar['removekey']=$k;
        $ar['nrong']=$ming.'退出聊天室';
        $str = $this->code(json_encode($ar));
        $this->send1(false,$str,'all');
    }
    
    function e($str){
        $path=dirname(__FILE__).'/log.txt';
        $str=$str."\n";
        error_log($str,3,$path);
        echo iconv('utf-8','gbk//IGNORE',$str);
    }
}
 
$sk=new Sock('127.0.0.1',8000);
$sk->run();
?>