<?php
class Profiler {


    // 基准测试类的实例

    public $bench;

    // --------------------------------------------------------------------

    /**
     * Class constructor
     *
     * Initialize Profiler
     *
     * @param   array   $config Parameters
     */
    public function __construct($bench)
    {
        $this->bench = $bench;
    }

    /**
     * Auto Profiler
     *
     * This function cycles through the entire array of mark points and
     * matches any two points that are named identically (ending in "_start"
     * and "_end" respectively).  It then compiles the execution times for
     * all points and returns it as an array
     *
     * @return  array
     */
    protected function _compile_benchmarks()
    {
        $profile = array();
        $bench = $this->bench;
        // 开始基准测试，统计时间
        foreach ($bench->marker as $key => $val)
        {
            // We match the "end" marker so that the list ends
            // up in the order that it was defined
            if (preg_match('/(.+?)_end$/i', $key, $match) && isset($bench->marker[$match[1].'_end'],$bench->marker[$match[1].'_start']))
            {
                $profile[$match[1]] = $bench->elapsed_time($match[1].'_start', $key);
            }
        }


        $output = "\n\n"
            .'<fieldset id="ci_profiler_benchmarks" style="border:1px solid #900;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee;">'
            ."\n"
            .'<legend style="color:#900;">&nbsp;&nbsp; 基准测试 &nbsp;&nbsp;</legend>'
            ."\n\n\n<table style=\"width:100%;\">\n";

        foreach ($profile as $key => $val)
        {
            $key = ucwords(str_replace(array('_', '-'), ' ', $key));
            $output .= '<tr><td style="padding:5px;color:#000;background-color:#ddd;width:150px;">'
                    .$key.'</td><td style="padding:5px;color:#900;font-weight:normal;background-color:#ddd;">'
                    .$val." s</td></tr>\n";
        }
        $output .= '<tr><td style="padding:5px;color:#000;background-color:#ddd;width:150px;">内存占用</td><td style="padding:5px;color:#900;font-weight:normal;background-color:#ddd;">'
                    .$bench->memory_usage()."</td></tr>\n";
        return $output."</table>\n</fieldset>";
    }

    public function run()
    {
        $output = '<div style="clear:both;background-color:#fff;padding:10px;">';
        $fields_displayed = 0;

        $output .= $this->_compile_benchmarks();

        echo $output.'</div>';
    }

}
