<?php


namespace App\Huawei;


class Constants
{
    /**
     * push_msg
     */

    const WEBPUSH_URGENCY_VERY_LOW = "very-low";
    const WEBPUSH_URGENCY_LOW = "low";
    const WEBPUSH_URGENCY_NORMAL = "normal";
    const WEBPUSH_URGENCY_HIGH = "high";

    /*
     * Notification Type
     */
    const PUSHMSG_NOTIFICATION_MSG_TYPE = 1;
    const PUSHMSG_PASS_THROUGHT_MSG_TYPE = 2;
    const PUSHMSG_TOPIC_MSG_TYPE = 3;
    const PUSHMSG_CONDITION_MSG_TYPE = 4;
    const PUSHMSG_FASTAPP_MSG_TYPE = 5;
    const APN_MSG_TYPE = 6;
    const WEB_PUSH_MSG_TYPE = 7;
    const TOPIC_SUBSCRIBE_MSG_TYPE = 8;
    const TOPIC_UNSUBSCRIBE_MSG_TYPE = 9;
    const TOPIC_SUBSCRIBE_QUERY_MSG_TYPE = 10;

    /**
     * log
     */
    const HW_PUSH_LOG_PUSH_MSG_MODULE = "PushMsg";
    const HW_PUSH_LOG_TOPIC_SUBSCRIBE_MODULE = "TopicSubsScribe";
    const HW_PUSH_LOG_TOPIC_UNSUBSCRIBE_MODULE = "TopicUnSubsScribe";

    const HW_PUSH_LOG_DEBUG_LEVEL = 4;
    const HW_PUSH_LOG_INFO_LEVEL = 3;
    const HW_PUSH_LOG_WARN_LEVEL = 2;
    const HW_PUSH_LOG_ERROR_LEVEL = 1;

    const HW_PUSH_LOG_FILE_NAME = 'push.log';

    const URL_PATTERN = "/^https.*/";
    const VIBRATE_PATTERN = "/[0-9]+|[0-9]+[sS]|[0-9]+[.][0-9]{1,9}|[0-9]+[.][0-9]{1,9}[sS]/";
    const COLOR_PATTERN = "/^#[0-9a-fA-F]{6}$/";
    const TTL_PATTERN = "/\\d+|\\d+[sS]|\\d+.\\d{1,9}|\\d+.\\d{1,9}[sS]/";

    const APN_AUTHORIZATION_PATTERN = "/^bearer*/";
    const APN_ID_PATTERN = "/[0-9a-z]{8}(-[0-9a-z]{4}){3}-[0-9a-z]{12}/";
}
