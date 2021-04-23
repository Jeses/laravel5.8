<?php
/**
 * Created by PhpStorm.
 * @Date  : 2021/3/15 13:01
 * @Author:青山
 * @Email :<yz_luck@163.com>
 */

namespace App\Common\helpers;


class Filter
{
    /**
     * 安全过滤类-过滤js,css,sql,php_tag,xss,path等不安全参数 过滤级别变态
     * @param $value
     * @return null|string|string[]
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static Public function filterParamsAll($value)
    {
        if (is_array($value)) {
            /** 数组下所有key值都进行过滤 */
            foreach ($value as $key => $_value) {
                if (is_array($_value)) {
                    /** 如果是多维数组递归形式过滤 */
                    $value[$key] = self::filterParamsAll($_value);
                } else {
                    $value[$key] = self::filterAll($_value);
                }
            }
        } else {
            $value = self::filterAll($value);
        }

        return $value;
    }

    /**
     * 过滤数据信息
     * @param string $value
     * @return null|string|string[]
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterAll($value = '')
    {
        $value = trim($value, '');
        $value = self::filterScript($value);
        $value = self::filterSql($value);
        $value = self::filterStr($value);
        $value = self::filterPhpTag($value);
        $value = self::filterXss($value);
        $value = self::strOut($value);
        $value = self::filterPath($value);

        return $value;
    }

    /**
     * 富文本数据过滤，让前台支持基础HTML标签
     * @param string $value
     * @return array|string|string[]|null
     * @Date: 2019/12/11 14:52
     * @Author:Rick
     * @Email:<yz_luck@163.com>
     */
    static public function filterText($value = '')
    {
        $value = trim($value, '');
        $value = self::filterScript($value);
        $value = self::filterSql($value);
        $value = self::filterStr($value);
        $value = self::filterPhpTag($value);
        $value = self::filterPath($value);

        return $value;
    }

    /**
     * 安全过滤类-过滤javascript,css,iframes,object等不安全参数 过滤级别高
     * Controller中使用方法：self::filterScript($value)
     * @param string $value 需要过滤的值
     * @return null|string|string[]
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static Public function filterScript($value)
    {
        $value = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i", "&111n\\2", $value);
        $value = preg_replace("/(.*?)<\/script>/si", "", $value);
        $value = preg_replace("/(.*?)<\/iframe>/si", "", $value);

        if (strrpos($value, "//iesU")) {
            $value = preg_replace("//iesU", "", $value);
        }

        return $value;
    }

    /**
     * 安全过滤类-过滤HTML标签
     * Controller中使用方法：self::filterHtml($value)
     * @param string $value 需要过滤的值
     * @return string
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterHtml($value)
    {
        $value = strip_tags($value);
        if (function_exists('htmlspecialchars')) return htmlspecialchars($value);
        return str_replace(["&", '"', "'", "<", ">"], ["&", "\"", "'", "<", ">"], $value);
    }

    /**
     * 安全过滤类-对进入的数据加下划线 防止SQL注入
     * Controller中使用方法：self::filterSql($value)
     * @param string $value 需要过滤的值
     * @return string
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterSql($value)
    {
        $sql    = ["select", 'insert', "update", "delete", "\'", "\/\*", "\.\.\/", "\.\/", "union", "into", "load_file", "outfile"];
        $sql_re = ["", "", "", "", "", "", "", "", "", "", "", ""];
        return str_replace($sql, $sql_re, $value);
    }

    /**
     * 安全过滤类-通用数据过滤
     *  Controller中使用方法：$this->filter_escape($value)
     * @param string|array $value 需要过滤的变量
     * @return string|array
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterEscape($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::filterStr($v);
            }
        } else {
            $value = self::filterStr($value);
        }

        return $value;
    }

    /**
     * 安全过滤类-字符串过滤 过滤特殊有危害字符
     * Controller中使用方法：self::filterStr($value)
     * @param string $value 需要过滤的值
     * @return string
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterStr($value)
    {
        $badstr = ["\0", "%00", "\r", '&', ' ', '"', "'", "<", ">", "   ", "%3C", "%3E"];
        $newstr = ['', '', '', '&', ' ', '"', "'", "<", ">", "   ", "<", ">"];
        $value  = str_replace($badstr, $newstr, $value);
        $value  = preg_replace('/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $value);
        return $value;
    }

    /**
     * 私有路劲安全转化
     * Controller中使用方法：self::filterDir($fileName)
     * @param string $fileName
     * @return string
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterDir($fileName)
    {
        $tmpname = strtolower($fileName);
        $temp    = [':/', "\0", ".."];
        if (str_replace($temp, '', $tmpname) !== $tmpname) {
            return false;
        }

        return $fileName;
    }

    /**
     * 过滤目录
     * Controller中使用方法：self::filterPath($path)
     * @param string $path
     * @return array
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterPath($path)
    {
        $path = str_replace(["'", '#', '=', '`', '$', '%', '&', ';'], '', $path);
        return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $path), '/');
    }

    /**
     * 过滤PHP标签
     * Controller中使用方法：self::filterPhpTag($string)
     * @param string $string
     * @return string
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function filterPhpTag($string)
    {
        return str_replace([''], ['<?', '?>'], $string);
    }

    /**
     * 安全过滤类-返回函数
     * Controller中使用方法：self::strOut($value)
     * @param string $value 需要过滤的值
     * @return string
     * @Author:SortLuck
     * @Email:<yz_luck@163.com>
     */
    static public function strOut($value)
    {
        $badstr = ["<", ">", "%3C", "%3E"];
        $newstr = ["<", ">", "<", ">"];
        $value  = str_replace($newstr, $badstr, $value);
        return stripslashes($value); //下划线
    }

    /**
     * 安全过滤类-返回函数
     * Controller中使用方法：self::filterXss($value)
     * @param string $value 需要过滤的值
     * @return string
     */
    static public function filterXss($value)
    {
        /**
         * 删除所有不可打印的字符 CR(0a) and LF(0b) and TAB(9) are allowed
         * 这可以防止某些字符重新间隔 比如 <java\0script>
         * 您必须稍后使用\ n，\ r和\ t来处理拆分，因为它们*在某些输入中被允许*
         *
         * remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
         * this prevents some character re-spacing such as <java\0script>
         * note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
         */
        $val = str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $value);
        $val = preg_replace('/(&#*＼w+)[＼x00-＼x20]+;/u', '$1;', $val);
        $val = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $val);
        //$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $value);

        //$ra = Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');

        /**
         * 直接替换，用户永远不需要这些，因为他们是正常的字符
         * 这可以防止像<IMG SRC = @ avascript：alert（'XSS'）>
         *
         * straight replacements, the user should never need these since they're normal characters
         * this prevents like <IMG SRC=@avascript:alert('XSS')>
         */
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++) {
            /**
             * ;？ 匹配;，这是可选的
             * 0 {0,7}匹配任何填充的零，这是可选的，最多可达8个字符
             * @ @ search for the hex values
             *
             * ;? matches the ;, which is optional
             * 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
             * @ @ search for the hex values
             */

            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;

            /**
             * @@ 0 {0,7}匹配'0'零到七次
             *
             * @ @ 0{0,7} matches '0' zero to seven times
             */
            $val = preg_replace('/(�{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }

        /**
         * 现在唯一剩下的空白攻击是\ t，\ n和\ r \ n
         *
         * now the only remaining whitespace attacks are \t, \n, and \r
         */
        $ra1 = ['javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'];
        $ra2 = ['onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'];
        $ra  = array_merge($ra1, $ra2);

        $found = true;
        /**
         * 只要前一轮替换了某些东西，就继续更换
         *
         * keep replacing as long as the previous round replaced something
         */
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(�{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                /** add in <> to nerf the tag */
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);
                /** filter out the hex tags */
                $val = preg_replace($pattern, $replacement, $val);

                /**  no replacements were made, so exit the loop */
                if ($val_before == $val) {
                    $found = false;
                }
            }
        }
        return $val;
    }
}