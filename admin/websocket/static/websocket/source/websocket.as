import flash.external.*;
import flash.net.*;
import flash.system.*;
import com.gsolo.encryption.MD5;


function removeBufferBefore(pos:int):void {
	if (pos == 0) return;
	var nextBuffer:ByteArray = new ByteArray();
	buffer.position = pos;
	buffer.readBytes(nextBuffer);
	buffer = nextBuffer;
};
function readUTFBytes(buffer:ByteArray, start:int, numBytes:int):String {
	buffer.position = start;
	var data:String = "";
	for(var i:int = start; i < start + numBytes; ++i) {
		if (buffer[i] == 0x00) {
			data += buffer.readUTFBytes(i - buffer.position) + "\x00";
			buffer.position = i + 1;
		}
	}
	data += buffer.readUTFBytes(start + numBytes - buffer.position);
	return data;
}
function readBytes(buffer:ByteArray, start:int, numBytes:int):String {
	buffer.position = start;
	var bytes:String = "";
	for (var i:int = 0; i < numBytes; ++i) {
		// & 0xff is to make \x80-\xff positive number.
		bytes += String.fromCharCode(buffer.readByte() & 0xff);
	}
	return bytes;
}
function writeBytes(bytes:String):void {
	for (var i:int = 0; i < bytes.length; ++i) {
		socket.writeByte(bytes.charCodeAt(i));
	}
}

function randomInt(min:uint, max:uint):uint {
	return min + Math.floor(Math.random() * (Number(max) - min + 1));
};
function initNoiseChars():void {
	noiseChars = new Array();
	for (var i:int = 0x21; i <= 0x2f; ++i) {
		noiseChars.push(String.fromCharCode(i));
	}
	for (var j:int = 0x3a; j <= 0x7a; ++j) {
		noiseChars.push(String.fromCharCode(j));
	}
};
function generateKey():String {
	var spaces:uint = randomInt(1, 12);
	var max:uint = uint.MAX_VALUE / spaces;
	var number:uint = randomInt(0, max);
	var key:String = (number * spaces).toString();
	var noises:int = randomInt(1, 12);
	var pos:int;
	for (var i:int = 0; i < noises; ++i) {
		var char:String = noiseChars[randomInt(0, noiseChars.length - 1)];
		pos = randomInt(0, key.length);
		key = key.substr(0, pos) + char + key.substr(pos);
	}
	for (var j:int = 0; j < spaces; ++j) {
		pos = randomInt(1, key.length - 1);
		key = key.substr(0, pos) + " " + key.substr(pos);
	}
	return key;
};
function generateKey3():String {
	var key3:String = "";
	for (var i:int = 0; i < 8; ++i) {
		key3 += String.fromCharCode(randomInt(0, 255));
	}
	return key3;
};
function getSecurityDigest(key1:String, key2:String, key3:String):String {
	var bytes1:String = keyToBytes(key1);
	var bytes2:String = keyToBytes(key2);
	return MD5.rstr_md5(bytes1 + bytes2 + key3);
};
function keyToBytes(key:String):String {
	var keyNum:uint = parseInt(key.replace(/[^\d]/g, ""));
	var spaces:uint = 0;
	for (var i:int = 0; i < key.length; ++i) {
		if (key.charAt(i) == " ") ++spaces;
	}
	var resultNum:uint = keyNum / spaces;
	var bytes:String = "";
	for (var j:int = 3; j >= 0; --j) {
		bytes += String.fromCharCode((resultNum >> (j * 8)) & 0xff);
	}
	return bytes;
};

function connect(myhost='', myport=''){
	if(!myhost && !myport) return;
	host = myhost;
	port = myport;
	Security.loadPolicyFile("xmlsocket://"+host+":"+port);
	socket.connect(host, port);
}
function send(encData:String):int {
	var data:String = decodeURIComponent(encData);
	if (readyState == OPEN) {
		socket.writeByte(0x00);
		socket.writeUTFBytes(data);
		socket.writeByte(0xff);
		socket.flush();
		trace("sent: " + data);
		return -1;
	} else if (readyState == CLOSED) {
		var bytes:ByteArray = new ByteArray();
		bytes.writeUTFBytes(data);
		bufferedAmount += bytes.length;
		return bufferedAmount;
	} else {
		trace("INVALID_STATE_ERR: invalid state");
		return 0;
	}
}
function receive( type, data ) {
	ExternalInterface.call("ws." + type, data );
}
function ping(){
	ExternalInterface.call("window.websocket_ping && websocket_ping()");
}

function close(){
	ExternalInterface.call("window.websocket_close && websocket_close()");
}

//flash load callback
function registerJsFun():void{
	if(ExternalInterface.available){
	try{
		var containerReady:Boolean=isContainerReady();
		if(containerReady){
			//注册函数
			setupCallBacks();
		}else{
			//检测是否准备好
			var readyTimer:Timer=new Timer(100);
			readyTimer.addEventListener(TimerEvent.TIMER,timeHandler);
			readyTimer.start();
		}
		}catch(error:Error){
			trace(error)
		}
	}else{
		trace("External interface is not available for this container.");
	}
}

function timeHandler(event:TimerEvent):void{
	var isReady:Boolean=isContainerReady();
    if(isReady){
  		Timer(event.target).stop();
   		setupCallBacks();
	}
}

function isContainerReady():Boolean{
	var result:Boolean=Boolean(ExternalInterface.call("websocket_run"));
	return result;
}

function setupCallBacks(){
	ExternalInterface.addCallback("Send", send);
	ExternalInterface.addCallback("Connect", connect);
	ExternalInterface.addCallback("Ping", ping);
	ExternalInterface.addCallback("Close", close);
	ExternalInterface.call("websocket_set")
}

var CONNECTING:int = 0;
var OPEN:int = 1;
var CLOSING:int = 2;
var CLOSED:int = 3;

var host;
var port;

var dataQueue:Array;
var expectedDigest:String;
var noiseChars:Array;
var buffer:ByteArray = new ByteArray();
var bufferedAmount:int = 0;
var readyState:int = CONNECTING;
var headerState:int = 0;

var socket = new Socket();
var onConnectHandler = function(){
	//trace ("Connection succeeded!");
	dataQueue = [];
    var key1:String = generateKey();
    var key2:String = generateKey();
    var key3:String = generateKey3();
    expectedDigest = getSecurityDigest(key1, key2, key3);
    
    var req:String = 
		"GET /echo HTTP/1.1\r\n" +
		"Upgrade: WebSocket\r\n" +
		"Connection: Upgrade\r\n" +
		"Host: " +host + ":" + port + "\r\n" +
		"Origin: http://" +host + "\r\n" +
		"Sec-WebSocket-Key1: " +key1 + "\r\n" +
		"Sec-WebSocket-Key2: " +key2 + "\r\n" +
		"\r\n";
    //trace("request header:\n" + req);
    socket.writeUTFBytes(req);
   // trace("sent key3: " + key3);
    writeBytes(key3);
    socket.flush();
	readyState = OPEN;
	receive('onopen', host);	//约定
};
var onCloseHandler = function(){
	trace("Connection closed!");
	readyState = CLOSED;
	receive('onclose',true);	//约定
};
var onGetDataHandler = function(event){
	//trace("progressHandler: bytesLoaded=" + event.bytesLoaded + " bytesTotal=" + event.bytesTotal);
	var pos:int = buffer.length;
	socket.readBytes(buffer, pos);
	for (; pos < buffer.length; ++pos) {
		if (headerState < 4) {
			// try to find "\r\n\r\n"
			if ((headerState == 0 || headerState == 2) && buffer[pos] == 0x0d) {
				++headerState;
			} else if ((headerState == 1 || headerState == 3) && buffer[pos] == 0x0a) {
				++headerState;
			} else {
				headerState = 0;
			}
			if (headerState == 4) {
				//var headerStr:String = readUTFBytes(buffer, 0, pos + 1);
				//trace("response header:\n" + headerStr);
				//if (!validateHeader(headerStr)) return;
				removeBufferBefore(pos + 1);
				pos = -1;
			}
		} else if (headerState == 4) {
			if (pos == 15) {
				var replyDigest:String = readBytes(buffer, 0, 16);
				//trace("reply digest: " + replyDigest);
				if (replyDigest != expectedDigest) {
					trace("digest doesn't match: " + replyDigest + " != " + expectedDigest);
					return;
				}
				headerState = 5;
				removeBufferBefore(pos + 1);
				pos = -1;
				readyState = OPEN;
			}
		} else {
			if (buffer[pos] == 0xff && pos > 0) {
				if (buffer[0] != 0x00) {
					trace("data must start with \\x00");
					return;
				}
				var data:String = readUTFBytes(buffer, 1, pos - 1);
				trace("received: " + data);
				receive('onmessage', data);
				dataQueue.push(encodeURIComponent(data));
				removeBufferBefore(pos + 1);
				pos = -1;
			} else if (pos == 1 && buffer[0] == 0xff && buffer[1] == 0x00) { // closing
				trace("received closing packet");
				removeBufferBefore(pos + 1);
				pos = -1;
			}
		}
	}
};
socket.addEventListener(Event.CONNECT, onConnectHandler);
socket.addEventListener(IOErrorEvent.IO_ERROR, onCloseHandler);
socket.addEventListener(SecurityErrorEvent.SECURITY_ERROR, onCloseHandler);
socket.addEventListener(Event.CLOSE, onCloseHandler);
socket.addEventListener(ProgressEvent.SOCKET_DATA, onGetDataHandler);

initNoiseChars();
registerJsFun();
