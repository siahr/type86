<?php

class HttpResponseCode {
	private function __construct() {}

	public static function send($code) {
		if (isset($_SERVER["SERVER_PROTOCOL"])) {
			$protocol = $_SERVER["SERVER_PROTOCOL"];
		} else {
			$protocol = "HTTP/1.0";
		}
		switch ($code) {
			case 100:
				header($protocol." 100 Continue");
				return;
			case 101:
				header($protocol." 101 Switching Protocols");
				return;
			case 102:
				header($protocol." 102 Processing");
				return;
			case 200:
				header($protocol." 200 OK");
				return;
			case 201:
				header($protocol." 201 Created");
				return;
			case 202:
				header($protocol." 202 Accepted");
				return;
			case 203:
				header($protocol." 203 Non-Authoritative Information");
				return;
			case 204:
				header($protocol." 204 No Content");
				return;
			case 205:
				header($protocol." 205 Reset Content");
				return;
			case 206:
				header($protocol." 206 Partial Content");
				return;
			case 207:
				header($protocol." 207 Multi-Status");
				return;
			case 208:
				header($protocol." 208 Already Reported");
				return;
			case 226:
				header($protocol." 226 IM Used");
				return;
			case 300:
				header($protocol." 300 Multiple Choices");
				return;
			case 301:
				header($protocol." 301 Moved Permanently");
				return;
			case 302:
				header($protocol." 302 Found");
				return;
			case 303:
				header($protocol." 303 See Other");
				return;
			case 304:
				header($protocol." 304 Not Modified");
				return;
			case 305:
				header($protocol." 305 Use Proxy");
				return;
			case 307:
				header($protocol." 307 Temporary Redirect");
				return;
			case 308:
				header($protocol." 308 Permanent Redirect");
				return;
			case 400:
				header($protocol." 400 Bad Request");
				return;
			case 401:
				header($protocol." 401 Unauthorized");
				return;
			case 402:
				header($protocol." 402 Payment Required");
				return;
			case 403:
				header($protocol." 403 Forbidden");
				return;
			case 404:
				header($protocol." 404 Not Found");
				return;
			case 405:
				header($protocol." 405 Method Not Allowed");
				return;
			case 406:
				header($protocol." 406 Not Acceptable");
				return;
			case 407:
				header($protocol." 407 Proxy Authentication Required");
				return;
			case 408:
				header($protocol." 408 Request Timeout");
				return;
			case 409:
				header($protocol." 409 Conflict");
				return;
			case 410:
				header($protocol." 410 Gone");
				return;
			case 411:
				header($protocol." 411 Length Required");
				return;
			case 412:
				header($protocol." 412 Precondition Failed");
				return;
			case 413:
				header($protocol." 413 Request Entity Too Large");
				return;
			case 414:
				header($protocol." 414 Request-URI Too Long");
				return;
			case 415:
				header($protocol." 415 Unsupported Media Type");
				return;
			case 416:
				header($protocol." 416 Requested Range Not Satisfiable");
				return;
			case 417:
				header($protocol." 417 Expectation Failed");
				return;
			case 418:
				header($protocol." 418 I'm a teapot");
				return;
			case 422:
				header($protocol." 422 Unprocessable Entity");
				return;
			case 423:
				header($protocol." 423 Locked");
				return;
			case 424:
				header($protocol." 424 Failed Dependency");
				return;
			case 426:
				header($protocol." 426 Upgrade Required");
				return;
			case 500:
				header($protocol." 500 Internal Server Error");
				return;
			case 501:
				header($protocol." 501 Not Implemented");
				return;
			case 502:
				header($protocol." 502 Bad Gateway");
				return;
			case 503:
				header($protocol." 503 Service Unavailable");
				return;
			case 504:
				header($protocol." 504 Gateway Timeout");
				return;
			case 505:
				header($protocol." 505 HTTP Version Not Supported");
				return;
			case 506:
				header($protocol." 506 Variant Also Negotiates");
				return;
			case 507:
				header($protocol." 507 Insufficient Storage");
				return;
			case 509:
				header($protocol." 509 Bandwidth Limit Exceeded");
				return;
			case 510:
				header($protocol." 510 Not Extended");
				return;
			default:
				header($protocol." 500 Internal Server Error");
				return;
		}
	}

}
