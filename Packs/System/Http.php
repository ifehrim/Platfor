<?php
/**
 * Created by IntelliJ IDEA.
 * User: pc
 * Date: 11/9/2018
 * Time: 10:38
 */

namespace Packs\System;

class Http
{

    private static $instance;

    private function _req($url, $post = '', $extra = array(), $timeout = 60)
    {
        $url_set = parse_url($url);
        if (empty($url_set['path'])) {
            $url_set['path'] = '/';
        }
        if (!empty($url_set['query'])) {
            $url_set['query'] = "?{$url_set['query']}";
        }
        if (empty($url_set['port'])) {
            $url_set['port'] = $url_set['scheme'] == 'https' ? '443' : '80';
        }
        if (strpos($url, 'https://') && !extension_loaded('openssl')) {
            if (!extension_loaded("openssl")) {
                die('请开启您PHP环境的openssl');
            }
        }
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            if (version_compare(phpversion(), '5.6.0', '>')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
            if (!empty($extra['ip'])) {
                $extra['Host'] = $url_set['host'];
                $url_set['host'] = $extra['ip'];
                unset($extra['ip']);
            }

            curl_setopt($ch, CURLOPT_URL, $url_set['scheme'] . '://' . $url_set['host'] . ($url_set['port'] == '80' ? '' : ':' . $url_set['port']) . $url_set['path'] . (isset($url_set['query']) ? $url_set['query'] : ''));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            @curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            if ($post) {
                if (is_array($post)) {
                    $filepost = false;
                    foreach ($post as $name => $value) {
                        if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof CURLFile)) {
                            $filepost = true;
                            break;
                        }
                    }
                    if (!$filepost) {
                        $post = http_build_query($post);
                    }
                }
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            if (!empty($GLOBALS['_W']['config']['setting']['proxy'])) {
                $urls = parse_url($GLOBALS['_W']['config']['setting']['proxy']['host']);
                if (!empty($urls['host'])) {
                    curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
                    $proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
                    if (!empty($urls['scheme']) && defined($proxytype)) {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
                    } else {
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                    }
                    if (!empty($GLOBALS['_W']['config']['setting']['proxy']['auth'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['_W']['config']['setting']['proxy']['auth']);
                    }
                }
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            if (defined('CURL_SSLVERSION_TLSv1')) {
                curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            }
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
            if (!empty($extra) && is_array($extra)) {
                $headers = array();
                foreach ($extra as $opt => $value) {
                    if (strpos($opt, 'CURLOPT_')) {
                        curl_setopt($ch, constant($opt), $value);
                    } elseif (is_numeric($opt)) {
                        curl_setopt($ch, $opt, $value);
                    } else {
                        $headers[] = "{$opt}: {$value}";
                    }
                }
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
            }
            $data = curl_exec($ch);
            curl_getinfo($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            if ($errno || empty($data)) {
                return error(1, $error);
            } else {
                return $this->parse($data);
            }
        }
        $method = empty($post) ? 'GET' : 'POST';
        $fdata = "{$method} {$url_set['path']}{$url_set['query']} HTTP/1.1\r\n";
        $fdata .= "Host: {$url_set['host']}\r\n";
        if (function_exists('gzdecode')) {
            $fdata .= "Accept-Encoding: gzip, deflate\r\n";
        }
        $fdata .= "Connection: close\r\n";
        if (!empty($extra) && is_array($extra)) {
            foreach ($extra as $opt => $value) {
                if (!strpos($opt, 'CURLOPT_')) {
                    $fdata .= "{$opt}: {$value}\r\n";
                }
            }
        }
        if ($post) {
            if (is_array($post)) {
                $body = http_build_query($post);
            } else {
                $body = urlencode($post);
            }
            $fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
        } else {
            $fdata .= "\r\n";
        }
        if ($url_set['scheme'] == 'https') {
            $fp = fsockopen('ssl://' . $url_set['host'], $url_set['port'], $errno, $error);
        } else {
            $fp = fsockopen($url_set['host'], $url_set['port'], $errno, $error);
        }
        stream_set_blocking($fp, true);
        stream_set_timeout($fp, $timeout);
        if (!$fp) {
            return error(1, $error);
        } else {
            fwrite($fp, $fdata);
            $content = '';
            while (!feof($fp))
                $content .= fgets($fp, 512);
            fclose($fp);
            return $this->parse($content, true);
        }
    }

    private function parse($data, $chunked = false)
    {
        $rlt = [];
        $headermeta = explode('HTTP/', $data);
        if (count($headermeta) > 2) {
            $data = 'HTTP/' . array_pop($headermeta);
        }
        $pos = strpos($data, "\r\n\r\n");
        $split1[0] = substr($data, 0, $pos);
        $split1[1] = substr($data, $pos + 4, strlen($data));

        $split2 = explode("\r\n", $split1[0], 2);
        preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
        $rlt['code'] = $matches[2];
        $rlt['status'] = $matches[3];
        $rlt['responseline'] = $split2[0];
        $header = explode("\r\n", $split2[1]);
        $isgzip = false;
        $ischunk = false;
        foreach ($header as $v) {
            $pos = strpos($v, ':');
            $key = substr($v, 0, $pos);
            $value = trim(substr($v, $pos + 1));
            if (isset($rlt['headers']) && isset($rlt['headers'][$key]) && is_array($rlt['headers'][$key])) {
                $rlt['headers'][$key][] = $value;
            } elseif (!empty($rlt['headers'][$key])) {
                $temp = $rlt['headers'][$key];
                unset($rlt['headers'][$key]);
                $rlt['headers'][$key][] = $temp;
                $rlt['headers'][$key][] = $value;
            } else {
                $rlt['headers'][$key] = $value;
            }
            if (!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
                $isgzip = true;
            }
            if (!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
                $ischunk = true;
            }
        }
        if ($chunked && $ischunk) {
            $rlt['content'] = $this->chunk($split1[1]);
        } else {
            $rlt['content'] = $split1[1];
        }
        if ($isgzip && function_exists('gzdecode')) {
            $rlt['content'] = gzdecode($rlt['content']);
        }

        $rlt['meta'] = $data;
        if ($rlt['code'] == '100') {
            return $this->parse($rlt['content']);
        }
        return $rlt;
    }

    private function chunk($str = null)
    {
        if (!is_string($str) or strlen($str) < 1) {
            return false;
        }
        $eol = "\r\n";
        $add = strlen($eol);
        $tmp = $str;
        $str = '';
        do {
            $tmp = ltrim($tmp);
            $pos = strpos($tmp, $eol);
            if ($pos === false) {
                return false;
            }
            $len = hexdec(substr($tmp, 0, $pos));
            if (!is_numeric($len) or $len < 0) {
                return false;
            }
            $str .= substr($tmp, ($pos + $add), $len);
            $tmp = substr($tmp, ($len + $pos + $add));
            $check = trim($tmp);
        } while (!empty($check));
        unset($tmp);
        return $str;
    }

    public static function get($url)
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance->_req($url);
    }

    public function post($url, $data)
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
        return self::$instance->_req($url, $data, $headers);
    }

    public static function request($url, $post = '', $extra = array(), $timeout = 60)
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance->_req($url, $post, $extra, $timeout);
    }

}