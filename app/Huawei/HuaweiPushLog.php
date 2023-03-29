<?php


namespace App\Huawei;


class HuaweiPushLog
{
    private $LogFile;

    private $LOG_MODULE_FIXED_LEN = 20;

    private $default_log_level = Constants::HW_PUSH_LOG_INFO_LEVEL;

    public function __construct()
    {
        $pushConfig = new HuaweiConfig;

        $this->default_log_level = $pushConfig->HW_DEFAULT_LOG_LEVEL;
        if (empty($this->default_log_level)){
            $this->default_log_level = Constants::HW_PUSH_LOG_INFO_LEVEL;
        }
    }


    /**
     * core log process
     */
    public function LogMessage(
        $msg,
        $logLevel = Constants::HW_PUSH_LOG_INFO_LEVEL,
        $module = null,
        $timeZone = 'Asia/Dhaka',
        $timeFormat = "%Y-%m-%d %H:%M:%S"
    ){
        if (empty($logLevel)) {
            $logLevel = Constants::HW_PUSH_LOG_INFO_LEVEL;
        }

        if ($logLevel > $this->default_log_level) {
            return;
        }

        date_default_timezone_set($timeZone);

        $time = strftime($timeFormat, time());
        $msg = str_replace("\t", '', $msg);
        $msg = str_replace("\n", '', $msg);

        $strLogLevel = $this->levelToString($logLevel);

        if (isset($module)) {

            $module = '[' . str_pad(str_replace(array(
                    "\n",
                    "\t"
                ), array(
                    "",
                    ""
                ), $module), $this->LOG_MODULE_FIXED_LEN) . ']';

            $logLine = "$strLogLevel\t$module\t$msg";
        } else {
            $logLine = "$strLogLevel\t$msg";
        }

       \Log::info($logLine);
    }

    private function levelToString($logLevel)
    {
        $ret = 'LOG::UNKNOWN';
        switch ($logLevel) {
            case Constants::HW_PUSH_LOG_DEBUG_LEVEL:
                $ret = 'LOG::DEBUG';
                break;
            case Constants::HW_PUSH_LOG_INFO_LEVEL:
                $ret = 'LOG::INFO';
                break;
            case Constants::HW_PUSH_LOG_WARN_LEVEL:
                $ret = 'LOG::WARNING';
                break;
            case Constants::HW_PUSH_LOG_ERROR_LEVEL:
                $ret = 'LOG::ERROR';
                break;
        }
        return $ret;
    }
}
