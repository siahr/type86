<?php

class NetUtils {
	private function __construct() {}

    /**
     * リードファイル
     * @param string $fileName ファイル名
     * @param string $type Content-Type
     * @param int $term ブラウザ側でキャッシュする日数（0を指定するとキャッシュしない）
     */
    public static function readFile($fileName, $type, $term=30) {
        if (!empty($term)) {
            $modified = filectime($fileName);
            if(isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])){
                $time = $_SERVER["HTTP_IF_MODIFIED_SINCE"];
                $since = strtotime($time);
                if($since == $modified){
                    header("HTTP/1.1 304 image not modified");
                    header('Pragma: cache');
                    header("Cache-Control: max-age=".(60*60*24*$term));
                    exit;
                }
            }
        }
        if (is_file($fileName)) {
            header('Content-Type: '.$type.'');
            if (!empty($term)) {
                header("Last-Modified: " . date('r', $modified));
                header('Pragma: cache');
                header("Cache-Control: max-age=".(60*60*24*$term));
            }
            readfile($fileName);
        }
    }

	public static function downloadForce($fileName, $unlink=false, $type="application/octet-stream", $downLoadName="") {
		if (is_file($fileName)) {
			header('Content-Type: '.$type.'');
			if (empty($downLoadName)) {
				$downLoadName = $fileName;
			} else {
				$downLoadName = rawurlencode($downLoadName);
			}
			header('Content-Disposition: attachment; filename=' . basename($downLoadName));
			header('Content-Length:' . filesize($fileName));
			header('Cache-Control: public');
			header('Pragma: public');
			while (ob_get_level() > 0) ob_end_clean();
			ob_start();
			if ($fOut = fopen($fileName, 'rb')) {
				while(!feof($fOut) and (connection_status() == 0)) {
					echo fread($fOut, '4096');
					ob_flush();
				}
				ob_flush();
				fclose($fOut);
			}
			ob_end_clean();

			if ($unlink) unlink($fileName);
		}
	}

	public static function safeSerialize($data){
		return base64_encode(serialize($data));
	}

	public static function safeUnserialize($data){
		return unserialize(base64_decode($data));
	}

	/**
	 * CodeIgniter
	 *
	 * An open source application development framework for PHP
	 *
	 * This content is released under the MIT License (MIT)
	 *
	 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 *
	 * @package	CodeIgniter
	 * @author	EllisLab Dev Team
	 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
	 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
	 * @license	http://opensource.org/licenses/MIT	MIT License
	 * @link	https://codeigniter.com
	 * @since	Version 1.0.0
	 * @filesource
	 */
	/**
	 * Header Redirect
	 *
	 * Header redirect in two flavors
	 * For very fine grained control over headers, you could use the Output
	 * Library's set_header() function.
	 *
	 * @param	string	$uri	URL
	 * @param	string	$method	Redirect method
	 *			'auto', 'location' or 'refresh'
	 * @param	int	$code	HTTP Response status code
	 * @return	void
	 */
	public static function redirect($uri = '', $method = 'auto', $code = NULL)
	{
		// IIS environment likely? Use 'refresh' for better compatibility
		if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE)
		{
			$method = 'refresh';
		}
		elseif ($method !== 'refresh' && (empty($code) OR ! is_numeric($code)))
		{
			if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1')
			{
				$code = ($_SERVER['REQUEST_METHOD'] !== 'GET')
					? 303	// reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
					: 307;
			}
			else
			{
				$code = 302;
			}
		}

		switch ($method)
		{
			case 'refresh':
				header('Refresh:0;url='.$uri);
				break;
			default:
				header('Location: '.$uri, TRUE, $code);
				break;
		}
		exit;
	}
    /**
     * Is AJAX request?
     *
     * Test to see if a request contains the HTTP_X_REQUESTED_WITH header.
     *
     * @return 	bool
     */
    public static function isAjaxRequest()
    {
        return ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }


}
