<?php
if (!class_exists('SmartTrim')) {
    /**
     * Smart Trim String Helper
     *
     */
    class SmartTrim
    {


        /**
         *
         * process string smart split
         * @param string $strin string input
         * @param int $pos start node split
         * @param int $len length of string that need to split
         * @param string $hiddenClasses show and redmore with property display: none or invisible
         * @param string $encoding type of string endcoding
         * @return string string that is smart splited
         */
        public static function mb_trim($strin, $pos = 0, $len = 10000, $hiddenClasses = '', $encoding = 'utf-8')
        {
            mb_internal_encoding($encoding);
            $strout = trim($strin);

            $pattern = '/(<[^>]*>)/';
            $arr = preg_split($pattern, $strout, -1, PREG_SPLIT_DELIM_CAPTURE);
            $left = $pos;
            $length = $len;
            $strout = '';
            for ($i = 0; $i < count($arr); $i++) {
                /*$arr [$i] = trim ( $arr [$i] );*/
                if ($arr[$i] == '')
                    continue;
                if ($i % 2 == 0) {
                    if ($left > 0) {
                        $t = $arr[$i];
                        $arr[$i] = mb_substr($t, $left);
                        $left -= (mb_strlen($t) - mb_strlen($arr[$i]));
                    }

                    if ($left <= 0) {
                        if ($length > 0) {
                            $t = $arr[$i];
                            $arr[$i] = mb_substr($t, 0, $length);
                            $length -= mb_strlen($arr[$i]);
                            if ($length <= 0) {
                                $arr[$i] .= '...';
                            }

                        } else {
                            $arr[$i] = '';
                        }
                    }
                } else {
                    if (SmartTrim::isHiddenTag($arr[$i], $hiddenClasses)) {
                        if ($endTag = SmartTrim::getCloseTag($arr, $i)) {
                            while ($i < $endTag)
                                $strout .= $arr[$i++] . "\n";
                        }
                    }
                }
                $strout .= $arr[$i] . "\n";
            }
            //echo $strout;
            return SmartTrim::toString($arr, $len);
        }


        /**
         *
         * process simple string split
         * @param string $strin string input
         * @param int $pos start node
         * @param int $len length of string that need to split
         * @param string $hiddenClasses show and redmore with property display: none or invisible
         * @return string
         */
        public static function trim($strin, $pos = 0, $len = 10000, $hiddenClasses = '')
        {
            $strout = trim($strin);

            $pattern = '/(<[^>]*>)/';
            $arr = preg_split($pattern, $strout, -1, PREG_SPLIT_DELIM_CAPTURE);
            $left = $pos;
            $length = $len;
            $strout = '';
            for ($i = 0; $i < count($arr); $i++) {
                /*$arr [$i] = trim ( $arr [$i] );*/
                if ($arr[$i] == '')
                    continue;
                if ($i % 2 == 0) {
                    if ($left > 0) {
                        $t = $arr[$i];
                        $arr[$i] = substr($t, $left);
                        $left -= (strlen($t) - strlen($arr[$i]));
                    }

                    if ($left <= 0) {
                        if ($length > 0) {
                            $t = $arr[$i];
                            $arr[$i] = substr($t, 0, $length);
                            $length -= strlen($arr[$i]);
                            if ($length <= 0) {
                                $arr[$i] .= '...';
                            }

                        } else {
                            $arr[$i] = '';
                        }
                    }
                } else {
                    if (SmartTrim::isHiddenTag($arr[$i], $hiddenClasses)) {
                        if ($endTag = SmartTrim::getCloseTag($arr, $i)) {
                            while ($i < $endTag)
                                $strout .= $arr[$i++] . "\n";
                        }
                    }
                }
                $strout .= $arr[$i] . "\n";
            }
            //echo $strout;
            return SmartTrim::toString($arr, $len);
        }


        /**
         * Check is Hidden Tag
         * @param string tag
         * @param string type of hidden
         * @return boolean
         */
        public static function isHiddenTag($tag, $hiddenClasses = '')
        {
            //By pass full tag like img
            if (substr($tag, -2) == '/>')
                return false;
            if (in_array(SmartTrim::getTag($tag), array('script', 'style')))
                return true;
            if (preg_match('/display\s*:\s*none/', $tag))
                return true;
            if ($hiddenClasses && preg_match('/class\s*=[\s"\']*(' . $hiddenClasses . ')[\s"\']*/', $tag))
                return true;
        }


        /**
         *
         * Get close tag from content array
         * @param array $arr content
         * @param int $openidx
         * @return int 0 if find not found OR key of close tag
         */
        public static function getCloseTag($arr, $openidx)
        {
            /*$tag = trim ( $arr [$openidx] );*/
            $tag = $arr[$openidx];
            if (!$openTag = SmartTrim::getTag($tag))
                return 0;

            $endTag = "<$openTag>";
            $endidx = $openidx + 1;
            $i = 1;
            while ($endidx < count($arr)) {
                if (trim($arr[$endidx]) == $endTag)
                    $i--;
                if (SmartTrim::getTag($arr[$endidx]) == $openTag)
                    $i++;
                if ($i == 0)
                    return $endidx;
                $endidx++;
            }
            return 0;
        }


        /**
         *
         * Get tag in content
         * @param string $tag
         * @return string tag
         */
        public static function getTag($tag)
        {
            if (preg_match('/\A<([^\/>]*)\/>\Z/', trim($tag), $matches))
                return ''; //full tag
            if (preg_match('/\A<([^ \/>]*)([^>]*)>\Z/', trim($tag), $matches)) {
                //echo "[".strtolower($matches[1])."]";
                return strtolower($matches[1]);
            }
            //if (preg_match ('/<([^ \/>]*)([^\/>]*)>/', trim($tag), $matches)) return strtolower($matches[1]);
            return '';
        }


        /**
         *
         * convert array to string
         * @param array $arr
         * @param int $len
         * @return string
         */
        public static function toString($arr, $len)
        {
            $i = 0;
            $stack = new JAStack();
            $length = 0;
            while ($i < count($arr)) {
                /*$tag = trim ( $arr [$i ++] );*/
                $tag = $arr[$i++];
                if ($tag == '')
                    continue;
                if (SmartTrim::isCloseTag($tag)) {
                    if ($ltag = $stack->getLast()) {
                        if ('</' . SmartTrim::getTag($ltag) . '>' == $tag)
                            $stack->pop();
                        else
                            $stack->push($tag);
                    }
                } else if (SmartTrim::isOpenTag($tag)) {
                    $stack->push($tag);
                } else if (SmartTrim::isFullTag($tag)) {
                    //echo "[TAG: $tag, $length, $len]\n";
                    if ($length < $len)
                        $stack->push($tag);
                } else {
                    $length += strlen($tag);
                    $stack->push($tag);
                }
            }

            return $stack->toString();
        }


        /**
         *
         * Check is open tag
         * @param string $tag
         * @return boolean
         */
        public static function isOpenTag($tag)
        {
            if (preg_match('/\A<([^\/>]+)\/>\Z/', trim($tag), $matches))
                return false; //full tag
            if (preg_match('/\A<([^ \/>]+)([^>]*)>\Z/', trim($tag), $matches))
                return true;
            return false;
        }


        /**
         *
         * Check is full tag
         * @param string $tag
         * @return boolean
         */
        public static function isFullTag($tag)
        {
            //echo "[Check full: $tag]\n";
            if (preg_match('/\A<([^\/>]*)\/>\Z/', trim($tag), $matches))
                return true; //full tag
            return false;
        }


        /**
         *
         * Check is close tag
         * @param string $tag
         * @return boolean
         */
        public static function isCloseTag($tag)
        {
            if (preg_match('/<\/(.*)>/', $tag))
                return true;
            return false;
        }
    }
}

if (!class_exists('JAStack')) {

    /**
     * News Pro Module JAStack Helper
     */
    class JAStack
    {
        /*
         * array
         */
        var $_arr = null;


        /**
         * Constructor
         *
         * For php4 compatability we must not use the __constructor as a constructor for plugins
         * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
         * This causes problems with cross-referencing necessary for the observer design pattern.
         *
         */
        function __construct()
        {
            $this->_arr = array();
        }


        /**
         *
         * Push item value into array
         * @param observe $item value of item that will input to stack
         * @return unknown
         */
        function push($item)
        {
            $this->_arr[count($this->_arr)] = $item;
        }


        /**
         *
         * Pop item value from array
         * @param observe $item value of item that will pop from stack
         * @return unknow value of item that is pop from array
         */
        function pop()
        {
            if (!$c = count($this->_arr))
                return null;
            $ret = $this->_arr[$c - 1];
            unset($this->_arr[$c - 1]);
            return $ret;
        }


        /**
         *
         * Get value of last element in array
         * @return unknown value of last element in array
         */
        function getLast()
        {
            if (!$c = count($this->_arr))
                return null;
            return $this->_arr[$c - 1];
        }


        /**
         *
         * Convert array to string
         * @return string
         */
        function toString()
        {
            $output = '';
            foreach ($this->_arr as $item) {
                $output .= $item;
            }
            return $output;
        }
    }
}